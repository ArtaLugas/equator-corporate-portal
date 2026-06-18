<?php

namespace App\Http\Requests;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('create', Admin::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', 'unique:admins,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', 'in:super_admin,admin'],
            'status' => ['required', 'in:active,inactive'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
