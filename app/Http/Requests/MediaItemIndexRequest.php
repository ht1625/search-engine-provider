<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaItemIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q'         => ['sometimes','string','max:120'],    
            'type'      => ['sometimes', Rule::in(['video','text'])],
            'sort'      => ['sometimes', Rule::in(['title','type','score'])],
            'order'     => ['sometimes', Rule::in(['asc','desc'])],
            'page'      => ['sometimes','integer','min:1'],
            'per_page'  => ['sometimes','integer','min:1','max:100'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        // default
        $data['sort']     = $data['sort']     ?? 'score';
        $data['order']    = $data['order']    ?? 'desc';
        $data['per_page'] = $data['per_page'] ?? 10;

        return $data;
    }
}
