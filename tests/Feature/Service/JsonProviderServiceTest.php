<?php

namespace Tests\Feature\Service;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Services\ApiProviders\JsonProviderService;

class JsonProviderServiceTest extends TestCase
{
    public function test_fetch_data_returns_first_page()
    {
        Http::fake([
            'raw.githubusercontent.com/*' => Http::response([
                'pagination' => ['total' => 10, 'page' => 1, 'per_page' => 10],
                'data' => ['item1', 'item2'],
            ], 200),
        ]);

        $service = new JsonProviderService();
        $result = $service->fetchData();

        $this->assertJson($result);
        $json = json_decode($result, true);
        $this->assertArrayHasKey('pagination', $json);
    }

    public function test_fetch_all_data_combines_all_pages()
    {
        Http::fake([
            // Page 1
            'raw.githubusercontent.com/*page=1*' => Http::response([
                'pagination' => ['total' => 20, 'page' => 1, 'per_page' => 10],
                'contents' => ['item1', 'item2'],
            ], 200),
            // Page 2
            'raw.githubusercontent.com/*page=2*' => Http::response([
                'pagination' => ['total' => 20, 'page' => 2, 'per_page' => 10],
                'contents' => ['item3', 'item4'],
            ], 200),
        ]);

        $service = new JsonProviderService();
        $result = $service->fetchAllData();

        $this->assertJson($result);
        $json = json_decode($result, true);
        $this->assertEquals(15, count($json['contents']));
    }
}
