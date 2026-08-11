<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorReelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_name' => ['required', 'string', 'max:100'],
            'speciality' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'mobile' => ['nullable', 'regex:/^[0-9+() -]{7,20}$/'],
            'hospital_name' => ['nullable', 'string', 'max:150'],
            'consent' => ['accepted'],
        ];
    }
}
