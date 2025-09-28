<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'title' => $this->title,
            'type'  => $this->type,
            'score' => (float) $this->score,
        ];
    }
}
