<?php

namespace App\Services\ApiProviders;

use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class XmlProviderService implements ProviderInterface
{
    private const BASE_URL = 'https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/v2/provider2';

    public function fetchData(?int $page = null): string
    {
        $response = Http::get(self::BASE_URL, $page ? ['page' => $page] : []);
        return $response->body();
    }

    // NOT - İlgili api'nin sayfalama yapısının olduğu varsayımıyla genel bir implementasyon
    public function fetchAllData(): string
    {

        $firstBody = $this->fetchData(null);
        $firstXml  = new SimpleXMLElement($firstBody);

        $total     = (int) $firstXml->meta->total_count ?? 0;
        $perPage   = (int) $firstXml->meta->items_per_page ?? 0;
        $pageCount = $perPage > 0 ? (int) ceil($total / $perPage) : 1;

        $allData = [$this->xmlToArray($firstXml)];

        for ($p = 2; $p <= $pageCount; $p++) {
            $body = $this->fetchData($p);
            $xml  = new SimpleXMLElement($body);
            $allData[] = $this->xmlToArray($xml);
        }

        $merged = [
            'items' => $allData,
            'meta' => [
                'total_count'    => $total,
                'items_per_page' => $perPage,
                'current_page'    => $pageCount,
            ],
        ];

        return json_encode($merged, JSON_UNESCAPED_UNICODE);
    }

    private function xmlToArray(SimpleXMLElement $xml): array
    {
        return json_decode(json_encode($xml), true);
    }
}
