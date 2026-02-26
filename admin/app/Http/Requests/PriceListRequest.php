<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PriceListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // IMPORTANT
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = $this->input('id');

        return [
           'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('price_lists', 'name')->ignore($id),
        ],

            'conversion_rate' => [
                'required',
                'numeric',
                'min:0',
                'max:100000',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],

            'price_list_type' => 'required|integer|in:0,1',
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            // Name
            'name.required' => 'Price list name is required.',
            'name.string' => 'Price list name must be valid text.',
            'name.max' => 'Price list name cannot exceed 255 characters.',
            'name.unique' => 'This price list name already exists.',

            // Conversion Rate
            'conversion_rate.required' => 'Conversion rate is required.',
            'conversion_rate.numeric' => 'Conversion rate must be a number.',
            'conversion_rate.min' => 'Conversion rate cannot be negative.',
            'conversion_rate.max' => 'Conversion rate cannot exceed 100000.00.',
            'conversion_rate.regex' => 'Conversion rate must have maximum 2 decimal places.',

            // Price List Type
            'price_list_type.required' => 'Please select price list type.',
            'price_list_type.integer' => 'Invalid price list type.',
            'price_list_type.in' => 'Invalid price list type selected.',

        ];
    }
}
