<?php

namespace App\Models;

use App\Enums\ContentType;
use App\Models\Stats\StatsInterface;

class UnifiedData
{
    public string $externalId;                          // id of the item from provider
    public string $title;                               // title of the item
    public ?string $description;                        // description of the item
    public ContentType $type;                           // video, article, podcast, etc.
    public StatsInterface $stats;                       // statistics object
    public ?string $publishedAt;                        // publication date
    public array $tags;                                 // tags or categories
    public ?float $score;                               // calculated score
    public string $providerName;                        // name of the data provider

    public function __construct(array $data)
    {
        $this->externalId = $data['external_id'] ?? '';
        $this->title = $data['title'] ?? '';
        $this->description = $data['description'] ?? null;
        $this->type = $data['type'];
        $this->stats = $data['stats'];
        $this->publishedAt = $data['published_at'] ?? null;
        $this->tags = $data['tags'] ?? [];
        $this->score = $data['score'] ?? 0;
        $this->providerName = $data['provider_name'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'stats' => $this->stats,
            'published_at' => $this->publishedAt,
            'tags' => $this->tags,
            'score' => $this->score,
            'provider_name' => $this->providerName,
        ];
    }
}
