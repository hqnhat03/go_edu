<?php

namespace App\Http\Requests\Exam;

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
     */
    public function rules(): array
    {
        return [
            'class_id' => 'required|exists:class_rooms,id',
            'name' => 'required|string|max:255',
            'duration_minutes' => 'nullable|integer|min:1',
            'open_at' => 'nullable|date',
            'close_at' => 'nullable|date|after_or_equal:open_at',
            'status' => 'nullable|in:draft,published,archived',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'class_id.required' => 'Mã lớp học không được để trống',
            'class_id.exists' => 'Lớp học không tồn tại',
            'name.required' => 'Tên bài kiểm tra không được để trống',
            'name.string' => 'Tên bài kiểm tra phải là chuỗi ký tự',
            'name.max' => 'Tên bài kiểm tra không được quá 255 ký tự',
            'duration_minutes.integer' => 'Thời gian làm bài phải là số nguyên',
            'duration_minutes.min' => 'Thời gian làm bài phải ít nhất 1 phút',
            'open_at.date' => 'Ngày mở bài không hợp lệ',
            'close_at.date' => 'Ngày đóng bài không hợp lệ',
            'close_at.after_or_equal' => 'Ngày đóng bài phải sau hoặc bằng ngày mở bài/hiện tại',
            'status.in' => 'Trạng thái không hợp lệ',
        ];
    }
}
