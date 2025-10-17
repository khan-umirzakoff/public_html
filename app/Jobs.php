<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Jobs extends Model
{
    protected $fillable = [
        'title', 'location', 'type', 'info', 'responses', 'quals', 'benefits', 'salary', 'status', 'promotion', 'img', 'company', 'cat_id', 'comp_id', 'embedding'
    ];

    public static function searchSimilar(array $queryEmbedding, int $limit = 5): array
    {
        $jobs = self::where('status', 1)
            ->whereNotNull('embedding')
            ->get();

        if ($jobs->isEmpty()) {
            return [];
        }

        $results = [];
        
        foreach ($jobs as $job) {
            $jobEmbedding = json_decode($job->embedding, true);
            
            if (!is_array($jobEmbedding)) continue;
            
            $similarity = self::cosineSimilarity($queryEmbedding, $jobEmbedding);
            
            $results[] = [
                'job' => $job,
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
        if (count($a) !== count($b)) return 0.0;

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

        return ($magnitudeA && $magnitudeB) ? $dotProduct / ($magnitudeA * $magnitudeB) : 0.0;
    }
}

