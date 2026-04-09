<?php

namespace App\Http\Requests\Teacher;

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
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required',
            'nationality' => 'required',
            'gender' => 'required',
            'expertise' => 'required',
            'experience' => 'required|numeric',
            'target_student_type' => 'required',
            'status' => 'required',
            'day_of_birth' => 'required',
            'avatar' => 'nullable',
            'bio' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Họ tên không được để trống',
            'email.required' => 'Email không được để trống',
            'email.email' => 'Email không hợp lệ',
            'phone.required' => 'Số điện thoại không được để trống',
            'address.required' => 'Địa chỉ không được để trống',
            'nationality.required' => 'Quốc tịch không được để trống',
            'gender.required' => 'Giới tính không được để trống',
            'expertise.required' => 'Chuyên môn không được để trống',
            'experience.required' => 'Kinh nghiệm không được để trống',
            'target_student_type.required' => 'Đối tượng học viên không được để trống',
            'status.required' => 'Trạng thái không được để trống',
            'day_of_birth.required' => 'Ngày sinh không được để trống',
        ];
    }
}
