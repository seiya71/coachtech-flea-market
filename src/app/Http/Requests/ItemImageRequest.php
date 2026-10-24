<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemImageRequest extends FormRequest
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
            'item_image' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ];
    }

    public function messages()
    {
        return [
            'item_image.required' => '商品画像を選択してください',
            'item_image.image' => '商品画像には画像ファイルを選択してください',
            'item_image.mimes' => '商品画像はjpegまたはpng形式で選択してください',
            'item_image.max' => '商品画像は2MB以下のファイルを選択してください',
        ];
    }
}
