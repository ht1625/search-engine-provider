<?php

namespace App\Services\Scoring;

use App\Models\Stats\ArticleStats;

class ArticleScoring implements ScoringStrategyInterface
{
    public function __construct(private ArticleStats $stats) {}

    public function calculateBaseScore(): float
    {
        return $this->stats->readingTime + ($this->stats->reactions / 50);
    }

    public function getTypeMultiplier(): float
    {
        return 1.0;
    }

    public function calculateEngagementScore(): float
    {
        if ($this->stats->readingTime === 0) return 0;
        return ($this->stats->reactions / $this->stats->readingTime) * 5;
    }
}