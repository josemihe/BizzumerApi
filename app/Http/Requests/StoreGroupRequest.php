<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            //
            'name' => 'required',
            'comment' => 'max:255'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The :attribute cannot be empty.',
            'comment.max' => 'The :attribute cannot be longer than 255 characters',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'name of the group',
            'comment' => 'put a comment in here',
        ];
    }
}
