<?php

namespace App\Http\Requests\ClassRoom;

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
            'class_code' => 'required|string',
            'start_day' => 'required|date',
            'end_day' => 'required|date|after:start_day',
            'max_student' => 'required|integer',
            'meeting_url' => 'required|string',
            'status' => 'required|in:draft,published,archived',
            'course_id' => 'required',
            'class_teachers' => 'array',
        ];
    }

    public function messages()
    {
        return [
            'class_code.required' => 'Mã lớp học không được để trống',
            'class_code.string' => 'Mã lớp học phải là chuỗi',
            'start_day.required' => 'Ngày bắt đầu không được để trống',
            'start_day.date' => 'Ngày bắt đầu phải là ngày',
            'end_day.required' => 'Ngày kết thúc không được để trống',
            'end_day.date' => 'Ngày kết thúc phải là ngày',
            'end_day.after' => 'Ngày kết thúc phải sau ngày bắt đầu',
            'max_student.required' => 'Số lượng học viên tối đa không được để trống',
            'max_student.integer' => 'Số lượng học viên tối đa phải là số',
            'meeting_url.required' => 'URL họp không được để trống',
            'meeting_url.string' => 'URL họp phải là chuỗi',
            'status.required' => 'Trạng thái không được để trống',
            'status.in' => 'Trạng thái không hợp lệ',
            'course_id.required' => 'Khóa học không được để trống',
        ];
    }
}
