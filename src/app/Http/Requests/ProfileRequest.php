<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\AddressRequest;

class ProfileRequest extends AddressRequest
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
        // プロフィール画像はアップロード時にセッションに保存したパスを使うので、フォームの値は受け付けない
        return parent::rules();
    }
}
