<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AuthorizesModuleActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Support\Rbac;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * Manage RBAC roles and their permission matrix. Gated by the `role.*`
 * permissions, which only the super_admin role holds — so role management stays
 * a super-admin capability while remaining a normal, matrix-driven CRUD screen.
 *
 * super_admin is the omnipotent role and is protected: it cannot be renamed,
 * deleted, or have its permissions trimmed. The default `admin` role is
 * protected from deletion but its permissions may be tuned.
 */
class RoleController extends Controller implements HasMiddleware
{
    use AuthorizesModuleActions;

    /** Roles the UI must never delete — the system depends on them. */
    private const PROTECTED = ['super_admin', 'admin'];

    public static function middleware(): array
    {
        return static::moduleMiddleware('role');
    }

    public function index()
    {
        $roles = Role::where('guard_name', Rbac::GUARD)
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        // admin-per-role counts come from the role column (the source of truth).
        $adminCounts = DB::table('admins')->selectRaw('role, COUNT(*) total')
            ->groupBy('role')->pluck('total', 'role');

        return view('admin.roles.index', [
            'roles' => $roles,
            'adminCounts' => $adminCounts,
            'protected' => self::PROTECTED,
        ]);
    }

    public function create()
    {
        return view('admin.roles.create', [
            'modules' => Rbac::modules(),
            'granted' => [],
        ]);
    }

    public function store(RoleRequest $request)
    {
        $role = Role::create([
            'name' => $request->validated('name'),
            'guard_name' => Rbac::GUARD,
        ]);

        $role->syncPermissions($this->cleanPermissions($request->validated('permissions', [])));

        activity_log('Roles', 'Created role: '.$role->name);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        abort_if($role->guard_name !== Rbac::GUARD, 404);

        return view('admin.roles.edit', [
            'role' => $role,
            'modules' => Rbac::modules(),
            'granted' => $role->permissions->pluck('name')->all(),
            'locked' => $role->name === 'super_admin',
        ]);
    }

    public function update(RoleRequest $request, Role $role)
    {
        abort_if($role->guard_name !== Rbac::GUARD, 404);

        // super_admin is omnipotent by definition — its name and full permission
        // set are never editable, so a tampered form cannot weaken it.
        if ($role->name === 'super_admin') {
            $role->syncPermissions(Rbac::permissions());

            return redirect()->route('admin.roles.index')
                ->with('success', 'The super admin role always holds every permission.');
        }

        $role->update(['name' => $request->validated('name')]);
        $role->syncPermissions($this->cleanPermissions($request->validated('permissions', [])));

        activity_log('Roles', 'Updated role: '.$role->name);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        abort_if($role->guard_name !== Rbac::GUARD, 404);

        if (in_array($role->name, self::PROTECTED, true)) {
            return back()->with('error', 'The '.$role->name.' role is protected and cannot be deleted.');
        }

        if (DB::table('admins')->where('role', $role->name)->exists()) {
            return back()->with('error', 'Reassign the admins using this role before deleting it.');
        }

        $name = $role->name;
        $role->delete();

        activity_log('Roles', 'Deleted role: '.$name);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /** Keep only permission names that exist in the catalog. */
    private function cleanPermissions(array $permissions): array
    {
        return array_values(array_intersect($permissions, Rbac::permissions()));
    }
}
