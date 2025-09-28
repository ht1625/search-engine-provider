<?php

namespace App\Models\Stats;

class ArticleStats implements StatsInterface
{
    public function __construct(
        public int $readingTime,
        public int $reactions,
        public int $comments
    ) {}
}