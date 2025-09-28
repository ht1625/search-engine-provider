<?php

namespace App\Http\Controllers;

use App\Http\Requests\MediaItemIndexRequest;
use App\Models\MediaItem;
use Illuminate\Support\Facades\Cache;

class MediaItemPageController extends Controller
{
    public function index(MediaItemIndexRequest $request)
    {
        $v = $request->validated();

        $cacheKey = sprintf(
            'media_items:html:v2:q=%s|type=%s|sort=%s|order=%s|page=%d|per=%d',
            $v['q']        ?? '',
            $v['type']     ?? '',
            $v['sort'],
            $v['order'],
            (int)($v['page'] ?? 1),
            (int)$v['per_page']
        );

        $ttl = now()->addMinutes(10);

        $paginator = Cache::tags(['media_items'])->remember($cacheKey, $ttl, function () use ($v) {
            return MediaItem::query()
                ->select(['external_id','title','type','score'])
                ->search($v['q'] ?? null)
                ->type($v['type'] ?? null)
                ->sortBy($v['sort'], $v['order'])
                ->paginate($v['per_page'])
                ->appends($v);
        });

        return view('media_items.index', [
            'items' => $paginator,
            'filters' => [
                'q'      => $v['q']    ?? '',
                'type'   => $v['type'] ?? '',
                'sort'   => $v['sort'],
                'order'  => $v['order'],
                'per'    => $v['per_page'],
            ],
        ]);
    }
}
