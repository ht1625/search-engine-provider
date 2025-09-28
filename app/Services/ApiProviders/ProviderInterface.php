<?php

namespace App\Services\ApiProviders;

interface ProviderInterface
{
    public function fetchData(?int $page = null): string;
    public function fetchAllData(): string;
}
