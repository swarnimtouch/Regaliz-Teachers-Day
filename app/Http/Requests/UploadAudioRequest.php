<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadAudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['audio' => ['required', 'file', 'mimes:webm,mp3,wav,m4a,ogg,mp4', 'max:25600']];
    }
}
