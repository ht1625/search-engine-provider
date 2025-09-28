<?php

namespace App\Services\Scoring;

use App\Models\Stats\VideoStats;

class VideoScoring implements ScoringStrategyInterface
{
    public function __construct(private VideoStats $stats) {}

    public function calculateBaseScore(): float
    {
        return ($this->stats->views / 1000) + ($this->stats->likes / 100);
    }

    public function getTypeMultiplier(): float
    {
        return 1.5;
    }

    public function calculateEngagementScore(): float
    {
        if ($this->stats->views === 0) return 0;
        return ($this->stats->likes / $this->stats->views) * 10;
    }
}