<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shop_uuid' => 'required|string|exists:shops,uuid',
            'name' => 'required|string|max:255',
            'image' => 'nullable|sometimes',
            'email' => 'required|string|max:255',
            'contact_number' => 'required|string|max:255',
        ];
    }
}
