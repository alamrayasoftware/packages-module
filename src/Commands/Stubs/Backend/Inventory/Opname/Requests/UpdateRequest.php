<?php

namespace __defaultNamespace__\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => $errors->first(),
                'data' => null,
                'errors' => $errors
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'list_item_id' => 'required|array',
            'list_item_id.*' => 'exists:__defaultNamespace__\Models\Related\MItem,id',
            'list_expired_date' => 'nullable|array',
            'list_expired_date.*' => 'date_format:Y-m-d',
            'list_old_qty' => 'required|array',
            'list_new_qty' => 'required|array',
            'list_unit_price' => 'required|array',
            'list_note' => 'nullable|array',
        ];
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            // 'email.required' => 'Email is required!',
        ];
    }
}
