<?php

namespace App\Http\Requests\Level;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject_id' => 'required|exists:subjects,id',
            'level' => 'required|string|max:255',
            'status' => 'required|in:draft,published,archived',
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.required' => 'Subject ID is required',
            'subject_id.exists' => 'Subject ID does not exist',
            'level.required' => 'Level is required',
            'level.string' => 'Level must be a string',
            'level.max' => 'Level must be at most 255 characters',
            'status.required' => 'Status is required',
            'status.in' => 'Status must be draft, published, or archived',
        ];
    }
}
