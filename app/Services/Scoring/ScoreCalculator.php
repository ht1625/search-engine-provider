<?php

namespace App\Services\Scoring;

class ScoreCalculator
{
    public function __construct(private ScoringStrategyInterface $strategy) {}

    public function calculate(\DateTime $publishedAt): float
    {
        $base = $this->strategy->calculateBaseScore();
        $multiplier = $this->strategy->getTypeMultiplier();
        $freshness = FreshnessCalculator::calculate($publishedAt);
        $engagement = $this->strategy->calculateEngagementScore();

        return ($base * $multiplier) + $freshness + $engagement;
    }
}
