<?php

namespace App\Parsers;

use App\Enums\ContentType;
use App\Enums\LogLevel;

use App\Models\Stats\ArticleStats;
use App\Models\Stats\VideoStats;
use App\Models\JobLog;

use App\Services\Scoring\VideoScoring;
use App\Services\Scoring\ArticleScoring;
use App\Services\Scoring\ScoreCalculator;

class XmlParser implements ParserInterface
{
    
    public function parse($rawData): array
    {
        $standardized = [];

        // 🔹 Global parse: simplexml_load_string
        try {
            $xml = simplexml_load_string($rawData);
            $items = $xml->items->item ?? [];
        } catch (\Throwable $e) {
            JobLog::create([
                'job_name' => self::class,
                'message'  => "Failed to parse XML: " . $e->getMessage(),
                'level'    => LogLevel::ERROR,
                'context'  => [
                    'raw_data' => substr($rawData, 0, 500),
                    'trace'    => $e->getTraceAsString(),
                ],
            ]);
            throw $e;
        }

        // 🔹 Item-level parse
        foreach ($items as $item) {
            try {
                $categories = [];
                if (isset($item->categories->category)) {
                    foreach ($item->categories->category as $cat) {
                        $categories[] = (string)$cat;
                    }
                }

                $type = match ((string)($item->type ?? '')) {
                    'video'   => ContentType::VIDEO,
                    'article' => ContentType::ARTICLE,
                    default   => null,
                };

                $stats = match ($type) {
                    ContentType::VIDEO => new VideoStats(
                        views: (int)($item->stats->views ?? 0),
                        likes: (int)($item->stats->likes ?? 0),
                        duration: (string)($item->stats->duration ?? '')
                    ),
                    ContentType::ARTICLE => new ArticleStats(
                        readingTime: (int)($item->stats->reading_time ?? 0),
                        reactions: (int)($item->stats->reactions ?? 0),
                        comments: (int)($item->stats->comments ?? 0)
                    ),
                    default => null,
                };

                $strategy = match ($type) {
                    ContentType::VIDEO   => new VideoScoring($stats),
                    ContentType::ARTICLE => new ArticleScoring($stats),
                    default              => null,
                };

                $calculator = $strategy
                    ? new ScoreCalculator($strategy)
                    : null;

                $score = $calculator
                    ? $calculator->calculate(new \DateTime((string)($item->publication_date ?? 'now')))
                    : null;

                $standardized[] = [
                    'external_id'  => (string)($item->id ?? ''),
                    'title'        => (string)($item->headline ?? ''),
                    'description'  => null,
                    'type'         => $type,
                    'stats'        => $stats,
                    'published_at' => (string)($item->publication_date ?? null),
                    'tags'         => $categories,
                    'score'        => $score,
                ];

                // ✅ Item-level success log
                JobLog::create([
                    'job_name' => self::class,
                    'message'  => "Item parsed successfully",
                    'level'    => LogLevel::INFO,
                    'context'  => [
                        'external_id' => (string)($item->id ?? ''),
                        'type'        => $type?->value,
                    ],
                ]);

            } catch (\Throwable $e) {
                // ❌ Item-level error log
                JobLog::create([
                    'job_name' => self::class,
                    'message'  => "Failed to parse item: " . $e->getMessage(),
                    'level'    => LogLevel::ERROR,
                    'context'  => [
                        'raw_item' => json_encode($item),
                        'trace'    => $e->getTraceAsString(),
                    ],
                ]);
            }
        }

        // 🔹 General summary log
        JobLog::create([
            'job_name' => self::class,
            'message'  => "Parse completed: " . count($standardized) . " items standardized",
            'level'    => LogLevel::INFO,
        ]);

        return $standardized;
    }

}
