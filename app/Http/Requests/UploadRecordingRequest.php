<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadRecordingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recording' => ['required', 'file', 'mimes:webm,mp4,mov', 'max:51200'],
            'video_zoom' => ['required', 'numeric', 'between:1.00,1.50'],
        ];
    }
}
