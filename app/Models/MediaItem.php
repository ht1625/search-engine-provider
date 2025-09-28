<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MediaItem extends Model
{
    protected $table = 'media_items';

    protected $fillable = [
        'external_id','title','description','type','stats','tags','score','provider_name','published_at',
    ];

    protected $casts = [
        'stats' => 'array',
        'tags' => 'array',
        'score' => 'float',
        'published_at' => 'datetime',
    ];

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;

        // FULLTEXT search:
        if ($this->hasFulltext()) {
            return $q->whereRaw('MATCH(title) AGAINST(? IN NATURAL LANGUAGE MODE)', [$term]);
        }

        // Fallback LIKE
        return $q->where('title', 'like', '%'.$term.'%');
    }

    public function scopeType(Builder $q, ?string $type): Builder
    {
        return $type ? $q->where('type', $type) : $q;
    }

    public function scopeSortBy(Builder $q, string $sort, string $order): Builder
    {
        $allowed = [
            'title'      => 'title',
            'type'       => 'type',
            'score'      => 'score',
        ];

        $column = $allowed[$sort] ?? 'score';
        $dir = strtolower($order) === 'asc' ? 'asc' : 'desc';

        return $q->orderBy($column, $dir)->orderBy('id', 'asc');
    }

    protected function hasFulltext(): bool
    {
        $driver = DB::connection($this->getConnectionName() ?: null)->getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'])) {
            return false;
        }

        return true;
    }
}
