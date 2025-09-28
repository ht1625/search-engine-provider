<?php

namespace App\Services\Scoring;

interface ScoringStrategyInterface
{
    public function calculateBaseScore(): float;
    public function getTypeMultiplier(): float;
    public function calculateEngagementScore(): float;
}
