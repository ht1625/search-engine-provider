<?php

namespace Tests\Feature\Service;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Services\ApiProviders\XmlProviderService;

class XmlProviderServiceTest extends TestCase
{
    public function test_fetch_data_returns_first_page_when_no_page_is_given()
    {
        $page1 = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <feed>
        <meta>
            <total_count>20</total_count>
            <current_page>1</current_page>
            <items_per_page>10</items_per_page>
        </meta>
        <items>
            <item><id>1</id></item>
            <item><id>2</id></item>
        </items>
        </feed>
        XML;

        Http::fake([
            'raw.githubusercontent.com/*' => Http::response($page1, 200, ['Content-Type' => 'application/xml']),
        ]);

        $svc = new XmlProviderService();
        $xml = $svc->fetchData();

        $this->assertStringContainsString('<current_page>1</current_page>', $xml);
        $this->assertStringContainsString('<item><id>1</id></item>', $xml);
    }

    public function test_fetch_data_returns_requested_page()
    {
        $page2 = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <feed>
        <meta>
            <total_count>20</total_count>
            <current_page>2</current_page>
            <items_per_page>10</items_per_page>
        </meta>
        <items>
            <item><id>11</id></item>
            <item><id>12</id></item>
        </items>
        </feed>
        XML;

        Http::fake([
            'raw.githubusercontent.com/*page=2*' => Http::response($page2, 200, ['Content-Type' => 'application/xml']),
        ]);

        $svc = new XmlProviderService();
        $xml = $svc->fetchData(2);

        $this->assertStringContainsString('<current_page>2</current_page>', $xml);
        $this->assertStringContainsString('<id>11</id>', $xml);
    }

    public function test_fetch_all_data_merges_all_pages()
    {
        $page1 = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <feed>
        <meta>
            <total_count>20</total_count>
            <current_page>1</current_page>
            <items_per_page>10</items_per_page>
        </meta>
        <items>
            <item><id>1</id></item>
            <item><id>2</id></item>
        </items>
        </feed>
        XML;

        $page2 = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <feed>
        <meta>
            <total_count>20</total_count>
            <current_page>2</current_page>
            <items_per_page>10</items_per_page>
        </meta>
        <items>
            <item><id>11</id></item>
            <item><id>12</id></item>
        </items>
        </feed>
        XML;

        Http::fake([
            'raw.githubusercontent.com/*' => Http::response($page1, 200, ['Content-Type' => 'application/xml']),
            'raw.githubusercontent.com/*page=2*' => Http::response($page2, 200, ['Content-Type' => 'application/xml']),
        ]);

        $svc  = new XmlProviderService();
        $json = $svc->fetchAllData();

        $this->assertJson($json);

        $merged = json_decode($json, true);

        // pagination controls
        $this->assertArrayHasKey('meta', $merged);
        $this->assertEquals(20, $merged['meta']['total_count']);
        $this->assertEquals(10, $merged['meta']['items_per_page']);
        $this->assertEquals(2, $merged['meta']['current_page']);

        $this->assertCount(2, $merged['items']);
        $this->assertEquals('10', $merged['meta']['items_per_page']);
        $this->assertEquals('2', $merged['meta']['current_page']);
    }
}
