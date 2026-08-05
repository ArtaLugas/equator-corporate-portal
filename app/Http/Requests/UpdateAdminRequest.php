<?php

namespace App\Http\Requests;

use App\Support\Rbac;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('update', $this->route('admin')) ?? false;
    }

    public function rules(): array
    {
        $adminId = $this->route('admin')->id;

        return [
            'name' => ['required', 'string', 'max:191'],
            'email' => [
                'required', 'email', 'max:191',
                Rule::unique('admins', 'email')->ignore($adminId),
            ],
            // Optional on update — leave blank to keep current password.
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::exists('roles', 'name')->where('guard_name', Rbac::GUARD)],
            'status' => ['required', 'in:active,inactive'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
