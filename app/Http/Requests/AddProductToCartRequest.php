<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddProductToCartRequest extends FormRequest
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
        $productId = $this->input('product_id');

        return [
            //
            'customer_email' => 'required|email',
            'product_id' => [
                'required',
                'numeric',
                Rule::exists('products', 'id')->where(function ($query) use ($productId) {
                    $query->where('id', '=', $productId);
                }),
            ],
            'quantity' => 'sometimes|numeric|min:1',
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'product_id.exists' => 'The selected product item does not exist.',
        ];
    }
}
