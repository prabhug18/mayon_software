<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $this->role,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ];
    }
}
