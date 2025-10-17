<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
require_once public_path('vendor/fpdf/fpdf.php');

class CVController extends Controller
{
    public function cv()
    {
        return view('pages/cv');
    }

    public function generate(Request $request)
    {
        $dir = public_path('uploads/');
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $photo = '';
        if ($request->hasFile('profile_picture') && $request->file('profile_picture')->isValid()) {
            $name = time().'_'.preg_replace('/[^\w.\-]/u', '_', $request->file('profile_picture')->getClientOriginalName());
            $request->file('profile_picture')->move($dir, $name);
            $photo = 'uploads/'.$name;
        }

        $certs = [];
        if ($request->hasFile('certificates')) {
            foreach ($request->file('certificates') as $file) {
                if ($file->isValid()) {
                    $name = time().'_'.preg_replace('/[^\w.\-]/u', '_', $file->getClientOriginalName());
                    $file->move($dir, $name);
                    $certs[] = 'uploads/'.$name;
                }
            }
        }

        $d = [
            'name'             => (string) $request->input('name',''),
            'birthday'         => (string) $request->input('birthday',''),
            'address'          => (string) $request->input('address',''),
            'email'            => (string) $request->input('email',''),
            'phone'            => (string) $request->input('phone',''),
            'photo'            => $photo,
            'position'         => (string) $request->input('position',''),
            'responsibilities' => (string) $request->input('responsibilities',''),
            'education'        => (string) $request->input('education',''),
            'career'           => (string) $request->input('career',''),
            'languages'        => (string) $request->input('languages',''),
            'report_writing'   => (string) $request->input('report_writing',''),
            'computing_skills' => (string) $request->input('computing_skills',''),
            'memberships'      => (string) $request->input('memberships',''),
            'certificates'     => $certs,
        ];

        $pdf = new CVGenerator($d);
        return response($pdf->Output('S', 'cv.pdf'))
               ->header('Content-Type', 'application/pdf');
    }
}

/*─────────────────────────────────────────────\
|  CVGenerator  – FPDF subclass                |
\──────────────────────────────────────────────*/

class CVGenerator extends \FPDF
{
    private $d;
    private $B = 0, $I = 0, $U = 0;
    private $ALIGN = '', $HREF = '';
    private $inList = false, $liOpen = false;
    private $bulletW = 0;

    public function __construct(array $data)
    {
        parent::__construct();
        $this->AddFont('DVS','',  'DejaVuSansCondensed.php');
        $this->AddFont('DVS','B', 'DejaVuSansCondensed-Bold.php');
        $this->AddFont('DVS','I', 'DejaVuSansCondensed-Oblique.php');
        $this->AddFont('DVS','BI','DejaVuSansCondensed-BoldOblique.php');
        $this->SetFont('DVS','',12);

        $this->bulletW = $this->GetStringWidth(chr(149)) + 2;
        $this->d = $data;
        $this->AddPage();
        $this->render();
    }

    private function sectionTitle(string $text): void
    {
        $this->SetFont('DVS','B',14);
        $this->SetFillColor(220,220,220);
        $this->Cell(0,10,$text,0,1,'L',true);
        $this->Ln(2);
        $this->SetFont('DVS','',12);
    }

    private function infoRow(string $label, ?string $val): void
    {
        $clean = trim(strip_tags(html_entity_decode($val ?: '-', ENT_QUOTES|ENT_HTML5,'UTF-8')));
        $clean = preg_replace('/\s*\n\s*/', ' ', $clean);
        $this->SetFont('DVS','B',12);
        $this->Cell(40,8,$label,0,0);
        $this->SetFont('DVS','',12);
        $this->MultiCell(0,8,$clean,0,1);
        $clean = trim(strip_tags(html_entity_decode($val ?: '-', ENT_QUOTES|ENT_HTML5,'UTF-8')));
        $clean = str_replace(['–','—','−'], '-', $clean); // normalize dash types to ASCII hyphen

    }

    private function richInline(string $label, ?string $html = null): void
    {
        if ($html === null || trim(strip_tags($html)) === '') {
            $html = '-';
        }

        $cleaned = str_replace(["\r", "\n"], ' ', $html);
        $cleaned = preg_replace('/<\/?(p|div|h\d)[^>]*>/i', '', $cleaned);
        $cleaned = str_replace('&nbsp;', ' ', $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        $this->SetFont('DVS', 'B', 12);
        $this->Write(6, rtrim($label, ':') . ': ');

        $this->SetFont('DVS', '', 12);
        $this->WriteHTML($cleaned, true);

        $this->Ln(6);
    }

    private function render(): void
    {
        $d = $this->d;
        $this->SetFont('DVS','B',20);
        $this->Cell(0,15,'CURRICULUM VITAE',0,1,'C');
        $this->Ln(5);

        $yStart = $this->GetY();

        if ($d['photo'] && file_exists(public_path($d['photo']))) {
            try {
                $this->Image(public_path($d['photo']),155,$yStart+5,45);
            } catch (\Exception $e) {}
        }

        $this->SetXY(10,$yStart+5);
        $this->infoRow('Name:',     $d['name']);
        $this->infoRow('Birthday:', $d['birthday']);
        $this->infoRow('Address:',  $d['address']);
        $this->infoRow('Email:',    $d['email']);
        $this->infoRow('Phone:',    $d['phone']);

        $this->SetY(max($this->GetY(), $yStart + 55));

        foreach ([
            'position'         => 'Position and Area',
            'responsibilities' => 'Designated Responsibilities',
            'career'           => 'Career Experience',
            'education'        => 'Education',
        ] as $key => $title) {
            if (trim(strip_tags($d[$key])) !== '') {
                $this->Ln(7);
                $this->sectionTitle($title);
                $this->WriteHTML($d[$key]);
            }
        }

        $this->Ln(7);
        $this->sectionTitle('Skills and Interests');
        $this->richInline('Languages:',        $d['languages']);
        $this->richInline('Report Writing:',   $d['report_writing']);
        $this->richInline('Computing Skills:', $d['computing_skills']);

        $this->SetFont('DVS','B',12);
        $this->MultiCell(0,8,'Memberships / Interests:');
        $this->SetFont('DVS','',12);
        $this->WriteHTML($d['memberships'] ?: '-');
        $this->Ln(7);

        if ($d['certificates']) {
            $this->Ln(7);
            $this->sectionTitle('Certificates Uploaded');
            foreach ($d['certificates'] as $c) {
                $this->SetTextColor(0,0,255);
                $this->Write(8,'- '.basename($c), url($c));
                $this->Ln(7);
            }
            $this->SetTextColor(0,0,0);
        }

        $this->Ln(10);
        $this->SetFont('DVS','',10);
        $this->Cell(0,8,'Generated on '.date('j F Y'),0,1,'L');
    }

    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('DVS','',10);
        $this->SetTextColor(100,100,100);
        $this->Cell(0,10,
            'Powered by BrightBridge.uz - Inspiring innovation, enabling careers.',
            0,0,'C');
    }

    public function WriteHTML(string $html, bool $inline = false): void
    {
        $html = str_replace(["\r","\n"],' ',$html);
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\/(p|div|h\d)>/i', "\n", $html);
        $html = preg_replace('/<p[^>]*>|<div[^>]*>/i', '', $html);
        $html = str_replace('&nbsp;',' ',$html);
        $html = preg_replace('/\n{2,}/', "\n", $html);

        $parts = preg_split('/(<[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($parts as $chunk) {
            if ($chunk[0] === '<') {
                $closing = $chunk[1] === '/';
                $tag = strtoupper(trim(strtok(trim($chunk,'<>/')," \t")));

                if ($closing) {
                    $this->CloseTag($tag);
                    if ($tag==='LI') { $this->liOpen=false; $this->Ln(1); }
                    if ($tag==='UL') { $this->inList=false; $this->Ln(2); }
                    continue;
                }

                $attrs=[];
                if (preg_match_all('/(\w+)\s*=\s*"([^"]*)"/',$chunk,$m,PREG_SET_ORDER)) {
                    foreach($m as $a) $attrs[strtoupper($a[1])]=$a[2];
                }
                $this->OpenTag($tag,$attrs);

                if ($tag==='UL') { $this->inList=true; $this->Ln(2); }
                if ($tag==='LI') { $this->liOpen=true; }
                if ($tag==='BR') { $this->Ln(4); }
                continue;
            }

            $text = html_entity_decode($chunk, ENT_QUOTES|ENT_HTML5, 'UTF-8');
$text = str_replace(['–','—','−'], '-', $text); // Normalize dashes to ASCII hyphen

            
            if ($text==='') continue;
            foreach (explode("\n",$text) as $i=>$line) {
                if ($i>0) $this->Ln(4);
                $line = trim($line);
                if ($line==='') continue;
                if ($this->liOpen) $this->Cell($this->bulletW,6,chr(149),0,0);
                if ($this->HREF) {
                    $this->SetTextColor(0,0,255); $this->SetStyle('U',true);
                    $this->MultiCell(0,6,$line,0,'L');
                    $this->SetStyle('U',false);   $this->SetTextColor(0,0,0);
                } else {
                    if ($inline) {
                        $this->Write(6, $line);
                    } else {
                        $this->MultiCell(0,6,$line,0,$this->ALIGN ?: 'L');
                    }
                }
            }
        }
    }

    private function OpenTag(string $tag,array $attr): void
    {
        if ($tag=='B'||$tag=='STRONG') $this->SetStyle('B',true);
        if ($tag=='I'||$tag=='EM')     $this->SetStyle('I',true);
        if ($tag=='U')                 $this->SetStyle('U',true);
        if ($tag=='H1') $this->SetFont('DVS','B',18);
        if ($tag=='H2') $this->SetFont('DVS','B',16);
        if ($tag=='CENTER') $this->ALIGN='C';
        if ($tag=='RIGHT')  $this->ALIGN='R';
        if ($tag=='A'&&isset($attr['HREF'])) $this->HREF=$attr['HREF'];
    }

    private function CloseTag(string $tag): void
    {
        if ($tag=='B'||$tag=='STRONG') $this->SetStyle('B',false);
        if ($tag=='I'||$tag=='EM')     $this->SetStyle('I',false);
        if ($tag=='U')                 $this->SetStyle('U',false);
        if ($tag=='H1'||$tag=='H2')    $this->SetFont('DVS','',12);
        if ($tag=='CENTER'||$tag=='RIGHT') $this->ALIGN='';
        if ($tag=='A') $this->HREF='';
    }

    private function SetStyle(string $tag,bool $on): void
    {
        $this->$tag += $on ? 1 : -1;
        $s=''; foreach(['B','I','U'] as $t) if($this->$t>0) $s.=$t;
        $this->SetFont('DVS',$s,12);
    }
}
