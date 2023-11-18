<?php

namespace App\Http\Requests\Admins;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
            'name' => 'required|max:60',
            'slug' => ['required', Rule::unique('category')->ignore($this->id)],
            'parent_id' => 'nullable|integer',
            'status' => 'nullable|integer',
            'popular' => 'nullable|integer',
            'description' => 'nullable|max:160',
            'meta_title' => 'nullable|max:60',
            'meta_description' => 'nullable|max:160',
        ];
    }
}
