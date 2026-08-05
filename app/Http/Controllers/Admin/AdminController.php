<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Models\Admin;
use App\Support\Rbac;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    private const PAGINATION = 15;

    /** Role names assignable to an admin, for the create/edit dropdowns. */
    private function assignableRoles(): array
    {
        return Role::where('guard_name', Rbac::GUARD)->orderBy('name')->pluck('name')->all();
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $this->authorize('viewAny', Admin::class);

        $query = Admin::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($request->role, ['super_admin', 'admin'], true)) {
            $query->where('role', $request->role);
        }

        if (in_array($request->status, ['active', 'inactive'], true)) {
            $query->where('status', $request->status);
        }

        $admins = $query->latest()->paginate(self::PAGINATION)->withQueryString();

        return view('admin.admins.index', compact('admins'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE / STORE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $this->authorize('create', Admin::class);

        return view('admin.admins.create', ['roles' => $this->assignableRoles()]);
    }

    public function store(StoreAdminRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $this->uploadAvatar($request->file('avatar'));
        }

        $admin = Admin::create($validated);

        activity_log('Admins', 'Created admin: '.$admin->email.' ('.$admin->role.')');

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Admin account created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT / UPDATE
    |--------------------------------------------------------------------------
    */

    public function edit(Admin $admin)
    {
        $this->authorize('update', $admin);

        return view('admin.admins.edit', ['admin' => $admin, 'roles' => $this->assignableRoles()]);
    }

    public function update(UpdateAdminRequest $request, Admin $admin)
    {
        $validated = $request->validated();

        $isSelf = $admin->id === auth('admin')->id();

        // Guard: prevent locking yourself out (cannot demote/deactivate self).
        if ($isSelf) {
            unset($validated['role'], $validated['status']);
        }

        // Guard: do not allow demoting/deactivating the last super admin.
        if ($admin->role === 'super_admin' && $this->isLastSuperAdmin($admin)) {
            if (($validated['role'] ?? 'super_admin') !== 'super_admin'
                || ($validated['status'] ?? 'active') !== 'active') {
                return back()->withInput()->with(
                    'error',
                    'This is the last active super admin — role and status cannot be changed.'
                );
            }
        }

        // Password: keep existing if left blank.
        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        // Avatar upload (replace + cleanup) or remove.
        if ($request->hasFile('avatar')) {
            $new = $this->uploadAvatar($request->file('avatar'));
            if ($admin->avatar) {
                Storage::disk('public')->delete($admin->avatar);
            }
            $validated['avatar'] = $new;
        } elseif ($request->boolean('remove_image')) {
            if ($admin->avatar) {
                Storage::disk('public')->delete($admin->avatar);
            }
            $validated['avatar'] = null;
        } else {
            unset($validated['avatar']);
        }

        $admin->update($validated);

        activity_log('Admins', 'Updated admin: '.$admin->email);

        return redirect()
            ->to(guarded_list_url($request->input('return_url'), route('admin.admins.index')))
            ->with('success', 'Admin account updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(Admin $admin)
    {
        $this->authorize('delete', $admin);

        // Guard: never delete the last super admin.
        if ($admin->role === 'super_admin' && $this->isLastSuperAdmin($admin)) {
            return back()->with('error', __('flash.last_super_admin'));
        }

        $email = $admin->email;

        if ($admin->avatar) {
            Storage::disk('public')->delete($admin->avatar);
        }

        $admin->delete();

        activity_log('Admins', 'Deleted admin: '.$email);

        return back()->with('success', 'Admin account deleted.');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function isLastSuperAdmin(Admin $admin): bool
    {
        return $admin->role === 'super_admin'
            && Admin::where('role', 'super_admin')->where('status', 'active')->count() <= 1;
    }

    private function uploadAvatar(UploadedFile $image): string
    {
        $filename = time().'-'.Str::random(6).'.'.$image->getClientOriginalExtension();

        return $image->storeAs('avatars', $filename, 'public');
    }
}
