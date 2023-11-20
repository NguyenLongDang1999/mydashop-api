<?php

namespace App\Http\Requests\Admins;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
        return [
            'sku' => 'required|max:60',
            'name' => 'required|max:60',
            'slug' => ['required', Rule::unique('product')->ignore($this->id)],
            'category_id' => 'required|integer',
            'technical_specifications' => 'nullable|string',
            'attributes' => 'nullable|string',
            'brand_id' => 'nullable|integer',
            'status' => 'nullable|integer',
            'popular' => 'nullable|integer',
            'price' => 'required',
            'special_price_type' => 'nullable',
            'special_price' => 'nullable',
            'selling_price' => 'nullable',
            'quantity' => 'nullable',
            'short_description' => 'nullable|max:160',
            'description' => 'nullable',
            'meta_title' => 'nullable|max:60',
            'meta_description' => 'nullable|max:160',
            'product_related' => 'string',
            'product_upsell' => 'string',
            'product_cross_sell' => 'string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'selling_price' => $this->sellingPrice()
        ]);
    }

    private function sellingPrice(): int
    {
            $discount = 0;

            if (intval($this->special_price_type) === config('constants.special_type.percent')) {
                $discount = ($this->price / 100) * $this->special_price;
            }

            if (intval($this->special_price_type) === config('constants.special_type.fixed')) {
                $discount = $this->special_price;
            }

            return $this->price - intval($discount);
    }
}
