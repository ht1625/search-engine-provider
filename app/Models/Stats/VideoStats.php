<?php

namespace App\Models\Stats;

class VideoStats implements StatsInterface
{
    public function __construct(
        public int $views,
        public int $likes,
        public string $duration
    ) {}
}