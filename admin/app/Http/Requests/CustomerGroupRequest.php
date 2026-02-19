<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use \Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class CustomerGroupRequest extends FormRequest
{
    public function authorize()
    {
        // Allow all users who can access this route
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'restrict_categories' => 'required|boolean',

            // At least one of these required if restriction = 1
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',

            'brands' => 'nullable|array',
            'brands.*' => 'integer|exists:brands,id',
        ];
    }

    public function withValidator($validator)
    {
        // $validator->after(function ($validator) {
        //     if ($this->restrict_categories == 1) {

        //         $categories = $this->input('categories', []);
        //         $brands = $this->input('brands', []);

        //         if (empty($categories) && empty($brands)) {
        //             $validator->errors()->add(
        //                 'categories',
        //                 'Please select at least one category or brand'
        //             );
        //         }
        //     }
        // });
    }
    // Optional: customize validation messages
     public function messages()
    {
        return [
            'name.required' => 'Please enter customer group name',
            'name.max' => 'Customer group name must not exceed 255 characters',
            'restrict_categories.required' => 'Please select if categories should be restricted',
            'categories.*.exists' => 'Invalid category selected',
            'brands.*.exists' => 'Invalid brand selected',
        ];
    }

    // Optional: handle failed validation for Toastr
    protected function failedValidation(Validator $validator)
    {
        $firstError = $validator->errors()->first();

        Toastr::error($firstError, 'Validation Error');

        throw new ValidationException(
            $validator,
            redirect()->back()->withInput()
        );
    }
}
