<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Mockery;
use Throwable;
use App\Models\MediaItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MediaItemsApiTest extends TestCase
{
    use RefreshDatabase;

    private function mockCacheTagsRemember(): void
    {
        Cache::shouldReceive('tags')
            ->andReturnSelf()
            ->byDefault();

        Cache::shouldReceive('remember')
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            })
            ->byDefault();
    }

    public function test_index_returns_default_sorted_paginated_list()
    {
        $this->mockCacheTagsRemember();

        MediaItem::create(['external_id'=>'e1', 'provider_name' => 'Json', 'title'=>'Zeta','type'=>'video','score'=>10]);
        MediaItem::create(['external_id'=>'e2', 'provider_name' => 'XML', 'title'=>'Alpha','type'=>'article' ,'score'=>30]);
        MediaItem::create(['external_id'=>'e3', 'provider_name' => 'Json', 'title'=>'Beta' ,'type'=>'video','score'=>20]);

        $res = $this->getJson('/api/media-items');

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'meta' => ['page','per_page','total','last_page','sort','order','filters'],
                'data',
            ]);

        $data = $res->json('data');
        $this->assertCount(3, $data);
        $this->assertEquals('Alpha', $data[0]['title']); // score 30
        $this->assertEquals('Beta' , $data[1]['title']); // score 20
        $this->assertEquals('Zeta' , $data[2]['title']); // score 10

        $this->assertEquals(1, $res->json('meta.page'));
        $this->assertEquals(10, $res->json('meta.per_page'));
        $this->assertEquals(3, $res->json('meta.total'));
        $this->assertEquals('score', $res->json('meta.sort'));
        $this->assertEquals('desc',  $res->json('meta.order'));
    }

    public function test_index_filters_by_type_and_search_q()
    {
        $this->mockCacheTagsRemember();

        MediaItem::create(['external_id'=>'e1', 'provider_name' => 'Json', 'title'=>'AI Intro', 'type'=>'video','score'=>10]);
        MediaItem::create(['external_id'=>'e2', 'provider_name' => 'XML', 'title'=>'Cooking',  'type'=>'article' ,'score'=>99]);
        MediaItem::create(['external_id'=>'e3', 'provider_name' => 'Json', 'title'=>'AI Basics','type'=>'article' ,'score'=>50]);

        $res = $this->getJson('/api/media-items?q=AI&type=video');

        $res->assertOk()->assertJsonPath('success', true);
        $data = $res->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals('AI Intro', $data[0]['title']);
        $this->assertEquals('video',    $data[0]['type']);
    }

    public function test_index_sorts_by_title_asc()
    {
        $this->mockCacheTagsRemember();

        MediaItem::create(['external_id'=>'e1', 'provider_name' => 'Json', 'title'=>'Delta','type'=>'video','score'=>10]);
        MediaItem::create(['external_id'=>'e2', 'provider_name' => 'XML', 'title'=>'Alpha','type'=>'article' ,'score'=>99]);
        MediaItem::create(['external_id'=>'e3', 'provider_name' => 'Json', 'title'=>'Charlie','type'=>'article','score'=>50]);

        $res = $this->getJson('/api/media-items?sort=title&order=asc');

        $res->assertOk()->assertJsonPath('success', true);
        $data = $res->json('data');

        $this->assertEquals('Alpha',  $data[0]['title']);
        $this->assertEquals('Charlie',$data[1]['title']);
        $this->assertEquals('Delta',  $data[2]['title']);
    }

    public function test_index_pagination_params_are_applied()
    {
        $this->mockCacheTagsRemember();

        for ($i=1; $i<=25; $i++) {
            MediaItem::create([
                'external_id' => "e$i",
                'provider_name' => $i % 2 ? 'Json' : 'XML',
                'title'       => "Item $i",
                'type'        => $i % 2 ? 'video' : 'article',
                'score'       => $i, 
            ]);
        }

        $res = $this->getJson('/api/media-items?per_page=10&page=2');

        $res->assertOk()->assertJsonPath('success', true);

        $this->assertEquals(2,  $res->json('meta.page'));
        $this->assertEquals(10, $res->json('meta.per_page'));
        $this->assertEquals(25, $res->json('meta.total'));

        $this->assertCount(10, $res->json('data'));
    }

    public function test_index_returns_500_when_cache_layer_throws()
    {

        Cache::shouldReceive('tags')->andReturnSelf();
        Cache::shouldReceive('remember')->andThrow(new \Exception('boom'));

        MediaItem::create(['external_id'=>'e1', 'provider_name' => 'XML', 'title'=>'Alpha','type'=>'article','score'=>1]);

        $res = $this->getJson('/api/media-items');

        $res->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Media items could not be retrieved.');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
