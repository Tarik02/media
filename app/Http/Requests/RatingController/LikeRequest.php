<?php

namespace App\Http\Requests\RatingController;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $voter
 */
class LikeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'voter' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
        ];
    }
}
