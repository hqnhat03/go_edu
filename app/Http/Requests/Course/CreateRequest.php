<?php

namespace App\Http\Requests\Course;

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
            'name' => 'required|string|max:255',
            'description' => 'string',
            'status' => 'required|in:draft,published,archived',
            'target_student' => 'required|in:student,employee,all',
            'price' => 'required|numeric|min:0',
            'lesson_count' => 'required|integer|min:1',
            'completion_time' => 'required|integer|min:1',
            'image_url' => 'string',
            'level_id' => 'required|exists:levels,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên khóa học không được để trống',
            'status.required' => 'Trạng thái không được để trống',
            'target_student.required' => 'Đối tượng học viên không được để trống',
            'price.required' => 'Giá khóa học không được để trống',
            'lesson_count.required' => 'Số lượng bài học không được để trống',
            'completion_time.required' => 'Thời gian hoàn thành không được để trống',
            'level_id.required' => 'Level không được để trống',
        ];
    }
}
