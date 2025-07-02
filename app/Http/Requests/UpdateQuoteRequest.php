<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateQuoteRequest extends FormRequest
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
            'id' => 'required|integer',
            'firstName' => 'string|max:255',
            'lastName' => 'string|max:255',
            'email' => 'email',
            'phone' => 'string|max:255',
            'address' => 'string|max:255',
            'servicesNeeded' => 'array|max:255',
            'additionalInfo' => 'string|max:255',
            'status' => 'string|max:255',
            'agreedToTerms' => 'boolean',
        ];
    }
}
