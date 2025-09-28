<?php

namespace App\Http\Controllers;

use App\Models\MediaItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\MediaItemIndexRequest;
use App\Http\Resources\MediaItemResource;
use Throwable;

class MediaItemController extends Controller
{
    public function index(MediaItemIndexRequest $request): JsonResponse
    {
        $v = $request->validated();

        // Cache key
        $cacheKey = sprintf(
            'media_items:v2:q=%s|type=%s|sort=%s|order=%s|page=%d|per=%d',
            $v['q']        ?? '',
            $v['type']     ?? '',
            $v['sort'],
            $v['order'],
            (int)($v['page'] ?? 1),
            (int)$v['per_page']
        );

        try {
            // Tag-based cache: facilitates invalidation
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

            return response()->json([
                'success' => true,
                'meta' => [
                    'page'       => $paginator->currentPage(),
                    'per_page'   => $paginator->perPage(),
                    'total'      => $paginator->total(),
                    'last_page'  => $paginator->lastPage(),
                    'sort'       => $v['sort'],
                    'order'      => $v['order'],
                    'filters'    => [
                        'q'    => $v['q']   ?? null,
                        'type' => $v['type']?? null,
                    ],
                ],
                'data' => MediaItemResource::collection($paginator->items()),
            ]);
        } catch (Throwable $e) {
            report($e); // Log the error for debugging
            return response()->json([
                'success' => false,
                'message' => 'Media items could not be retrieved.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
