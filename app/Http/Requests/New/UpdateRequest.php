<?php

namespace App\Http\Requests\New;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'title' => 'required|string|max:255|unique:news,title,' . $this->route('id'),
            'content' => 'required|string',
            'image' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
        ];
    }
}
