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
     * 商品画像はフォームの値ではなく、アップロード時にセッションに保存したパスを使う
     * (フォームの値をそのまま使うと、他人の画像など任意のファイルを指定できてしまうため)
     */
    protected function prepareForValidation()
    {
        $this->merge(['item_image' => session('item_image')]);
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
            'item_image' => ['required', 'string'],
            'condition' => ['required'],
            'price' => ['required', 'integer', 'min:50', 'max:9999999'],
            'brand' => ['required', 'max:255'],
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',

        ];
    }

    public function messages()
    {
        return [
            'item_image.required' => '商品画像をアップロードしてください',
            'item_name.max' => '商品名は255文字以内で入力してください',
            'brand.max' => 'ブランド名は255文字以内で入力してください',
            'price.min' => '販売価格は50円以上で入力してください',
            'price.max' => '販売価格は9,999,999円以下で入力してください',
        ];
    }
}
