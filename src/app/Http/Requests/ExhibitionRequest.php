<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'item_name' => ['required'],
            'description' => ['required', 'max:255'],
            'item_image' => ['required', 'string', 'regex:/\.(jpeg|png)$/i'],
            'condition' => ['required'],
            'price' => ['required', 'integer', 'min:0'],
            'brand' => ['required'],
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',

        ];
    }
}
