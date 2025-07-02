<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateClientJobRequest extends FormRequest
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
            'uuid' => 'required|string|max:255',
            'title' => 'string|max:255|nullable',
            'client' => 'array|nullable',
            'date' => 'date|nullable',
            'duration' => 'integer|nullable',
            'status' => 'string|in:Scheduled,Pending,Confirmed,Completed,Cancelled',
            'price' => 'numeric|nullable',
            'notes' => 'string|nullable',
            'services' => 'array|nullable',
            'team' => 'array|nullable',
        ];
    }
}
