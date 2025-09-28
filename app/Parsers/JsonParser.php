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

class JsonParser implements ParserInterface
{
    
    public function parse($rawData): array
    {
        $standardized = [];

        // 🔹 Global parse (rawData -> array)
        try {
            $data = json_decode($rawData, true);
            $items = $data['contents'] ?? [$data];
        } catch (\Throwable $e) {
            JobLog::create([
                'job_name' => static::class,
                'message'  => "Failed to decode JSON: " . $e->getMessage(),
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
                $type = match ((string)($item['type'] ?? '')) {
                    'video'   => ContentType::VIDEO,
                    'article' => ContentType::ARTICLE,
                    default   => null,
                };

                $stats = match ($type) {
                    ContentType::VIDEO => new VideoStats(
                        views: (int)($item['metrics']['views'] ?? 0),
                        likes: (int)($item['metrics']['likes'] ?? 0),
                        duration: (string)($item['metrics']['duration'] ?? '')
                    ),
                    ContentType::ARTICLE => new ArticleStats(
                        readingTime: (int)($item['metrics']['reading_time'] ?? 0),
                        reactions: (int)($item['metrics']['reactions'] ?? 0),
                        comments: (int)($item['metrics']['comments'] ?? 0)
                    ),
                    default => null,
                };

                $strategy = match ($type) {
                    ContentType::VIDEO   => new VideoScoring($stats),
                    ContentType::ARTICLE => new ArticleScoring($stats),
                    default              => null,
                };

                $calculator = $strategy ? new ScoreCalculator($strategy) : null;
                $score = $calculator ? $calculator->calculate(new \DateTime((string)($item['published_at'] ?? 'now'))) : null;

                $standardized[] = [
                    'external_id'  => $item['id'] ?? '',
                    'title'        => $item['title'] ?? '',
                    'description'  => $item['desc'] ?? null,
                    'type'         => $type,
                    'stats'        => $stats,
                    'published_at' => $item['published_at'] ?? null,
                    'tags'         => $item['tags'] ?? [],
                    'score'        => $score,
                ];

                // ✅ Successful item log
                JobLog::create([
                    'job_name' => static::class,
                    'message'  => "Item parsed successfully",
                    'level'    => LogLevel::INFO,
                    'context'  => [
                        'external_id' => $item['id'] ?? null,
                        'type'        => $type?->value,
                    ],
                ]);

            } catch (\Throwable $e) {
                // ❌ Item-level error log
                JobLog::create([
                    'job_name' => static::class,
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
            'job_name' => static::class,
            'message'  => "JSON parse completed: " . count($standardized) . " items standardized",
            'level'    => LogLevel::INFO,
        ]);

        return $standardized;
    }

}
