<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AiKnowledge extends Model
{
    protected $table = 'ai_knowledge';
    
    protected $fillable = [
        'category',
        'key',
        'value',
        'embedding',
        'description',
        'is_active',
        'priority'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public static function searchSimilar(array $queryEmbedding, int $limit = 3): array
    {
        $knowledge = self::where('is_active', true)
            ->whereNotNull('embedding')
            ->get();

        if ($knowledge->isEmpty()) {
            return [];
        }

        $results = [];
        
        foreach ($knowledge as $item) {
            $itemEmbedding = json_decode($item->embedding, true);
            
            if (!is_array($itemEmbedding)) {
                continue;
            }
            
            $similarity = self::cosineSimilarity($queryEmbedding, $itemEmbedding);
            
            $results[] = [
                'item' => $item,
                'similarity' => $similarity
            ];
        }

        usort($results, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return array_slice($results, 0, $limit);
    }

    protected static function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            return 0.0;
        }

        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $magnitudeA += $a[$i] * $a[$i];
            $magnitudeB += $b[$i] * $b[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    public static function getContext(string $userMessage = ''): string
    {
        $knowledge = self::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        if ($knowledge->isEmpty()) {
            return '';
        }

        $context = "# JobCare Platformasi Haqida Ma'lumotlar:\n\n";

        foreach ($knowledge->groupBy('category') as $category => $items) {
            $categoryTitle = self::getCategoryTitle($category);
            $context .= "## {$categoryTitle}:\n";

            foreach ($items as $item) {
                $context .= "- {$item->key}: {$item->value}\n";
            }
            $context .= "\n";
        }

        return $context;
    }

    protected static function getCategoryTitle($category): string
    {
        $titles = [
            'contact' => 'Kontakt Ma\'lumotlari',
            'faq' => 'Tez-tez so\'raladigan savollar',
            'service' => 'Xizmatlar',
            'about' => 'Platforma haqida',
            'pricing' => 'Narxlar',
        ];

        return $titles[$category] ?? ucfirst($category);
    }
}
