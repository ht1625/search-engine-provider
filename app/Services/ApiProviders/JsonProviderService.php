<?php

namespace App\Services\ApiProviders;

use Illuminate\Support\Facades\Http;

class JsonProviderService implements ProviderInterface
{
    private const BASE_URL = 'https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/v2/provider1';

    public function fetchData(?int $page = null): string
    {
        $response = Http::get(self::BASE_URL, $page ? ['page' => $page] : []);
        return $response->body();
    }

    // NOT - İlgili api'nin sayfalama yapısının olduğu varsayımıyla genel bir implementasyon
    public function fetchAllData(): string
    {
        $firstBody = $this->fetchData(null);
        $firstJson = json_decode($firstBody, true);

        $pagination   = $firstJson['pagination'] ?? ['total' => 0, 'page' => 1, 'per_page' => 0];
        $total        = (int)($pagination['total'] ?? 0);
        $perPage      = max(1, (int)($pagination['per_page'] ?? 0)); // 0 gelirse 1’e sabitle
        $pageCount    = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        $allData = [];
        if (isset($firstJson['data']) && is_array($firstJson['data'])) {
            $allData = $firstJson['data'];
        } else {
            $allData[] = $firstJson;
        }

        for ($p = 2; $p <= $pageCount; $p++) {
            $body = $this->fetchData($p);
            $json = json_decode($body, true);

            if (isset($json['data']) && is_array($json['data'])) {
                array_push($allData, ...$json['data']);
            } else {
                $allData[] = $json;
            }
        }

        $merged = [
            'contents' => $allData,
            'pagination' => [
                'total'    => $total,
                'per_page' => $perPage,
                'pages'    => $pageCount,
            ],
        ];

        return json_encode($merged, JSON_UNESCAPED_UNICODE);
    }

}
