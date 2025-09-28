<?php

namespace App\Services;

use App\Models\UnifiedData;
use App\Models\JobLog;

use App\Enums\LogLevel;

use App\Parsers\ParserInterface;

class DataProcessingService
{
    /**
     * @param string $rawData
     * @param ParserInterface $parser
     * @param string $providerName
     * @return UnifiedData[]  Array of unified data models
     */
    public function processData(string $rawData, ParserInterface $parser, string $providerName): array
    {
        try {
            $parsedItems = $parser->parse($rawData);

            $unifiedDataList = [];
            foreach ($parsedItems as $item) {
                $item['provider_name'] = $providerName;
                $unifiedDataList[] = new UnifiedData($item);
            }

            // Success log
            JobLog::create([
                'job_name' => static::class,
                'level' => LogLevel::INFO,
                'message' => "Provider '{$providerName}' data processed successfully.",
                'context' => [
                    'item_count' => count($parsedItems),
                    'provider' => $providerName,
                ],
            ]);

            return $unifiedDataList;

        } catch (\Throwable $e) {
            // Error log
            JobLog::create([
                'job_name' => static::class,
                'level' => LogLevel::ERROR,
                'message' => "An error occurred while processing data: " . $e->getMessage(),
                'context' => [
                    'provider' => $providerName,
                    'trace' => $e->getTraceAsString(),
                ],
            ]);

            throw $e;
        }
    }

}
