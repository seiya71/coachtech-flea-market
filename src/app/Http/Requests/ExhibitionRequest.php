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
            'item_name' => ['required', 'max:255'],
            'description' => ['required', 'max:255'],
            'item_image' => ['required', 'string', 'regex:/\.(jpe?g|png)$/i'],
            'condition' => ['required'],
            'price' => ['required', 'integer', 'min:0'],
            'brand' => ['required', 'max:255'],
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',

        ];
    }

    public function messages()
    {
        return [
            'item_name.max' => '商品名は255文字以内で入力してください',
            'brand.max' => 'ブランド名は255文字以内で入力してください',
        ];
    }
}
