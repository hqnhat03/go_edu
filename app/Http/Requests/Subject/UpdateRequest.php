<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            "name" => [
                "required",
                "string",
                "max:255",
            ],
            "training_level" => "required|string|max:255",
            "category" => "required|string|max:255",
            "student_type" => "required|in:student,employee",
            "status" => "required|in:draft,published,archived",
        ];
    }

    public function messages(): array
    {
        return [
            "name.required" => "Tên môn học không được để trống",
            "training_level.required" => "Trình độ đào tạo không được để trống",
            "category.required" => "Danh mục không được để trống",
            "student_type.required" => "Đối tượng học viên không được để trống",
            "status.required" => "Trạng thái không được để trống",
            "status.in" => "Trạng thái không hợp lệ",
            "student_type.in" => "Đối tượng học viên không hợp lệ",
        ];
    }
}
