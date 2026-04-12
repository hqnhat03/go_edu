<?php

namespace App\Http\Requests\Level;

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
            'level' => 'required|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'education_level' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'level.required' => 'Trình độ là bắt buộc',
            'level.string' => 'Trình độ phải là chuỗi',
            'level.max' => 'Trình độ phải có tối đa 255 ký tự',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
            'education_level.required' => 'Trình độ đào tạo là bắt buộc',
        ];
    }
}
