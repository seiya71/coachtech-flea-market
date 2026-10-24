<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileImageRequest extends FormRequest
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
            'profile_image' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ];
    }

    public function messages()
    {
        return [
            'profile_image.required' => 'プロフィール画像を選択してください',
            'profile_image.image' => 'プロフィール画像には画像ファイルを選択してください',
            'profile_image.mimes' => 'プロフィール画像はjpegまたはpng形式で選択してください',
            'profile_image.max' => 'プロフィール画像は2MB以下のファイルを選択してください',
        ];
    }
}
