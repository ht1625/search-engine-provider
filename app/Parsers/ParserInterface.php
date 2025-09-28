<?php

namespace App\Parsers;

interface ParserInterface
{
    public function parse($rawData): array;
}
