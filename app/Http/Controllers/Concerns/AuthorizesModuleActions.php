<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Routing\Controllers\Middleware;

/**
 * Maps standard resource actions to "{module}.{ability}" permission middleware
 * for controllers that implement HasMiddleware. Spatie registers every
 * permission as a gate, so `can:news.update` resolves against the acting admin's
 * permissions with no extra wiring, and super admins pass via the super_admin
 * role holding every permission.
 *
 * Actions absent from a controller are simply never matched, so the standard
 * grouping is safe to apply verbatim; pass $extra to attach module-specific
 * methods (e.g. a Message controller's reply/archive) to an ability.
 */
trait AuthorizesModuleActions
{
    protected static function moduleMiddleware(string $module, array $extra = []): array
    {
        // ability => controller methods that require "{module}.{ability}".
        $groups = [
            'view' => ['index', 'show', 'trash'],
            'create' => ['create', 'store'],
            'update' => ['edit', 'update'],
            'delete' => ['destroy', 'bulkDestroy', 'restore', 'forceDelete'],
        ];

        foreach ($extra as $ability => $methods) {
            $groups[$ability] = array_values(array_unique(
                array_merge($groups[$ability] ?? [], (array) $methods)
            ));
        }

        $middleware = [];

        foreach ($groups as $ability => $methods) {
            if ($methods !== []) {
                $middleware[] = new Middleware("can:{$module}.{$ability}", only: $methods);
            }
        }

        return $middleware;
    }
}
