<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'username'=>'required|string|min:4',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:6',
            'phone_number'=>'required|min:10|max:10',
            'wilaya'=>'required|string',
            'profile_img'=>'nullable|image|mimes:png,jpg',
        ];
    }
}
