<?php 

namespace App\Services\Scoring;

class FreshnessCalculator
{
    public static function calculate(\DateTime $publishedAt): int
    {
        $diff = (new \DateTime())->diff($publishedAt)->days;

        return match (true) {
            $diff <= 7 => 5,
            $diff <= 30 => 3,
            $diff <= 90 => 1,
            default => 0
        };
    }
}
