<?php

namespace A17\Twill;

use A17\Twill\Facades\TwillPermissions;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public const SUPERADMIN = 'SUPERADMIN';

    /**
     * Map between the legacy gates and the new gates from PermissionAuthServiceProvider.
     * The new gates are being used in the code now and the old gates are kept for
     * backward compatibility.
     */
    public const ABILITY_ALIASES = [
        'list' => ['access-module-list', 'access-media-library'],
        'edit' => [
            'view-item',
            'view-module',
            'edit-item',
            'edit-module',
            'edit-settings',
            'manage-item',
            'manage-module',
            'manage-modules',
        ],
        'reorder' => [],
        'publish' => [],
        'feature' => [],
        'delete' => [],
        'duplicate' => [],
        'upload' => ['edit-media-library'],
        'manage-users' => ['edit-users', 'edit-user-roles', 'edit-user-groups', 'access-user-management'],
        'edit-user' => [],
        'publish-user' => [],
        'impersonate' => [],
    ];

    protected function define($ability, $callback)
    {
        collect($ability)
            ->concat(static::ABILITY_ALIASES[$ability] ?? [])
            ->each(function ($alias) use ($callback) {
                Gate::define($alias, $callback);
            });
    }

    protected function authorize($user, $callback)
    {
        if (! $user->isPublished()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $callback($user);
    }

    protected function userHasRole($user, $roles)
    {
        return in_array($user->role_value, $roles, true);
    }

    public function boot()
    {
        $this->define('list', function ($user, $item = null) {
            return $this->authorize($user, function ($user) {
                return $this->userHasRole($user, [
                    TwillPermissions::roleValue('VIEWONLY'),
                    TwillPermissions::roleValue('PUBLISHER'),
                    TwillPermissions::roleValue('ADMIN'),
                ]);
            });
        });

        $this->define('edit', function ($user, $item = null) {
            return $this->authorize($user, function ($user) {
                return $this->userHasRole(
                    $user,
                    [TwillPermissions::roleValue('PUBLISHER'), TwillPermissions::roleValue('ADMIN')]
                );
            });
        });

        $this->define('reorder', function ($user) {
            return $this->authorize($user, function ($user) {
                return $this->userHasRole(
                    $user,
                    [TwillPermissions::roleValue('PUBLISHER'), TwillPermissions::roleValue('ADMIN')]
                );
            });
        });

        $this->define('publish', function ($user) {
            return $this->authorize($user, function ($user) {
                return $this->userHasRole(
                    $user,
                    [TwillPermissions::roleValue('PUBLISHER'), TwillPermissions::roleValue('ADMIN')]
                );
            });
        });

        $this->define('feature', function ($user) {
            return $this->authorize($user, function ($user) {
                return $this->userHasRole(
                    $user,
                    [TwillPermissions::roleValue('PUBLISHER'), TwillPermissions::roleValue('ADMIN')]
                );
            });
        });

        $this->define('delete', function ($user) {
            return $this->authorize($user, function ($user) {
                return $this->userHasRole(
                    $user,
                    [TwillPermissions::roleValue('PUBLISHER'), TwillPermissions::roleValue('ADMIN')]
                );
            });
        });

        $this->define('duplicate', function ($user) {
            return $this->authorize($user, function ($user) {
                return $this->userHasRole(
                    $user,
                    [TwillPermissions::roleValue('PUBLISHER'), TwillPermissions::roleValue('ADMIN')]
                );
            });
        });

        $this->define('upload', function ($user) {
            return $this->authorize($user, function ($user) {
                return $this->userHasRole(
                    $user,
                    [TwillPermissions::roleValue('PUBLISHER'), TwillPermissions::roleValue('ADMIN')]
                );
            });
        });

        $this->define('manage-users', function ($user) {
            return $this->authorize($user, function ($user) {
                return $this->userHasRole($user, [TwillPermissions::roleValue('ADMIN')]);
            });
        });

        // As an admin, I can edit users, except superadmins
        // As a non-admin, I can edit myself only
        $this->define('edit-user', function ($user, $editedUser = null) {
            return $this->authorize($user, function ($user) use ($editedUser) {
                return ($this->userHasRole($user, [TwillPermissions::roleValue('ADMIN')]) || $user->id == $editedUser->id)
                    && ($editedUser ? $editedUser->role !== self::SUPERADMIN : true);
            });
        });

        $this->define('publish-user', function ($user) {
            return $this->authorize($user, function ($user) {
                $editedUserObject = twillModel('user')::find(request('id'));

                return $this->userHasRole(
                    $user,
                    [TwillPermissions::roleValue('ADMIN')]
                ) && (
                    $editedUserObject && $user->id !== $editedUserObject->id &&
                    $editedUserObject->role !== self::SUPERADMIN
                );
            });
        });

        $this->define('impersonate', function ($user) {
            return $user->role === self::SUPERADMIN;
        });
    }
}
