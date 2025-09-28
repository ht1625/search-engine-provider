<?php

namespace App\Jobs;

use App\Services\DataProcessingService;
use App\Services\ApiProviders\ProviderInterface;

use App\Parsers\ParserInterface;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\MediaItem;
use App\Models\JobLog;

use App\Enums\LogLevel;

class ProcessProviderDataJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected ProviderInterface $provider;
    protected ParserInterface $parser;
    protected string $providerName;

    public function __construct(ProviderInterface $provider, ParserInterface $parser, string $providerName)
    {
        $this->provider = $provider;
        $this->parser = $parser;
        $this->providerName = $providerName;
    }

    public function handle(DataProcessingService $processor)
    {
        
        // 🔹 Global fetch + process
        try {
            $rawData = $this->provider->fetchData();
            $dataModels = $processor->processData($rawData, $this->parser, $this->providerName);
        } catch (\Throwable $e) {
            JobLog::create([
                'job_name' => self::class,
                'message'  => "Job failed during fetch/process: " . $e->getMessage(),
                'level'    => LogLevel::ERROR,
                'context'  => [
                    'provider' => $this->providerName,
                    'trace'    => $e->getTraceAsString(),
                ],
            ]);
            throw $e;
        }

        // 🔹 Item-level processing
        foreach ($dataModels as $model) {
            try {
                MediaItem::updateOrCreate(
                    [
                        'external_id'   => $model->externalId,
                        'provider_name' => $model->providerName,
                    ],
                    [
                        'title'        => $model->title,
                        'description'  => $model->description,
                        'type'         => $model->type->value,
                        'stats'        => method_exists($model->stats, 'toArray') 
                                            ? $model->stats->toArray() 
                                            : (array) $model->stats,
                        'tags'         => $model->tags,
                        'score'        => $model->score,
                        'published_at' => $model->publishedAt,
                    ]
                );

                // ✅ Successful item log
                JobLog::create([
                    'job_name' => self::class,
                    'message'  => "MediaItem synced",
                    'level'    => LogLevel::INFO,
                    'context'  => [
                        'external_id' => $model->externalId,
                        'provider'    => $model->providerName,
                    ],
                ]);

            } catch (\Exception $e) {
                // ❌ Error item log
                JobLog::create([
                    'job_name' => self::class,
                    'message'  => $e->getMessage(),
                    'level'    => LogLevel::ERROR,
                    'context'  => [
                        'external_id' => $model->externalId ?? null,
                        'provider'    => $model->providerName ?? $this->providerName,
                        'trace'       => $e->getTraceAsString(),
                    ],
                ]);
            }
        }
    }
}
