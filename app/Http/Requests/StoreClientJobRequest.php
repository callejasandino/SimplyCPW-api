<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreClientJobRequest extends FormRequest
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
            'client' => 'required',
            'date' => 'required|date',
            'duration' => 'required|integer',
            'status' => 'required|string|in:Scheduled,Pending,Confirmed,Completed,Cancelled',
            'price' => 'required|numeric',
            'notes' => 'string|nullable',
            'services' => 'array|nullable',
            'team' => 'array|nullable',
            'equipment' => 'array|nullable',
        ];
    }
}
