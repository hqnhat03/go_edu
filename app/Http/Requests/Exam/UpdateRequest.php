<?php

namespace App\Http\Requests\Exam;

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
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'duration_minutes' => 'sometimes|integer|min:1',
            'open_at' => 'nullable|date',
            'close_at' => 'nullable|date|after_or_equal:open_at',
            'status' => 'sometimes|in:draft,published,archived',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
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
