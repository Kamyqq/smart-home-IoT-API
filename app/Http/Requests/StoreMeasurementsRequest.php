<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMeasurementsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "temperature" => ["nullable", "numeric"],
            "humidity" => ["nullable", "numeric"],
            "light" => ["nullable", "numeric"],
        ];
    }
}
