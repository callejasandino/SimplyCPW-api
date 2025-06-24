<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreBusinessEventRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_type' => 'required|in:launching,promotional,announcement',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'start_date' => 'nullable|date_format:Y-m-d H:i:s',
            'end_date' => 'nullable|date_format:Y-m-d H:i:s',
            'status' => 'required|in:draft,published,archived,scheduled',
            'cta_label' => 'required|string|max:255',
            'discounted_services' => 'nullable|json',
            'visible' => 'required|boolean',
        ];
    }
}
