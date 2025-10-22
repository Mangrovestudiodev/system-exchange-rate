<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'referenceId' => 'nullable|string',
            'exchangeRates' => 'required|array|min:1',
            'exchangeRates.*' => 'required|array',
            'exchangeRates.*.*' => 'required|array',
            'exchangeRates.*.*.buyRate' => 'required|numeric',
            'exchangeRates.*.*.sellRate' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'exchangeRates.*.*.buyRate.required' => 'The :attribute is required.',
            'exchangeRates.*.*.buyRate.numeric' => 'The :attribute must be a valid number.',
            'exchangeRates.*.*.sellRate.required' => 'The :attribute is required.',
            'exchangeRates.*.*.sellRate.numeric' => 'The :attribute must be a valid number.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
