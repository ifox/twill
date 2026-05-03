<?php

namespace A17\Twill;

use A17\Twill\Enums\PermissionLevel;
use A17\Twill\Models\Enums\UserRole;
use A17\Twill\Models\Permission;
use A17\Twill\View\Components\Navigation\NavigationLink;
use BackedEnum;
use Illuminate\Support\Facades\Auth;

class TwillPermissions
{
    /**
     * @var class-string<BackedEnum>
     */
    public string $roleEnum = UserRole::class;

    public function enabled(): bool
    {
        return config('twill.enabled.permissions-management');
    }

    public function roles(): string
    {
        return $this->roleEnum;
    }

    /**
     * The role enumeration class. Must be a backed enum.
     */
    public function setRoleEnum(string $roleEnum): void
    {
        $this->roleEnum = $roleEnum;
    }

    public function roleValues(): array
    {
        return collect(($this->roles())::cases())->mapWithKeys(
            fn (BackedEnum $role) => [$role->name => $role->value]
        )->all();
    }

    public function roleValue(string $role): ?string
    {
        return $this->roleValues()[$role] ?? null;
    }

    /**
     * Return the module name if the module has permissions, otherwise return false.
     */
    public function getPermissionModule(string $moduleName): bool|string
    {
        $submodule = Permission::permissionableModules()->filter(function ($module) use ($moduleName) {
            return strpos($module, '.') && explode('.', $module)[1] === $moduleName;
        })->first();

        if (Permission::permissionableModules()->contains($moduleName)) {
            return $moduleName;
        }

        if ($submodule) {
            return $submodule;
        }

        return false;
    }

    public function levelIs(string $level): bool
    {
        if (! PermissionLevel::isValid($level)) {
            throw new \Exception('Invalid permission level. Check TwillPermissions for available levels');
        }

        return $this->enabled() && config('twill.permissions.level') === $level;
    }

    public function levelIsOneOf(array $levels): bool
    {
        foreach ($levels as $level) {
            if (! PermissionLevel::isValid($level)) {
                throw new \Exception('Invalid permission level. Check TwillPermissions for available levels');
            }
        }

        return $this->enabled() && in_array(config('twill.permissions.level'), $levels, true);
    }

    public function showUserSecondaryNavigation(): void
    {
        Facades\TwillNavigation::addSecondaryNavigationForCurrentRequest(
            NavigationLink::make()->title(twillTrans('twill::lang.user-management.users'))
                ->forModule('users')
                ->onlyWhen(fn () => Auth::user()->can('edit-users'))
        );

        Facades\TwillNavigation::addSecondaryNavigationForCurrentRequest(
            NavigationLink::make()->title(twillTrans('twill::lang.permissions.roles.title'))
                ->forModule('roles')
                ->onlyWhen(
                    fn () => config('twill.enabled.permissions-management') && Auth::user()->can('edit-user-roles')
                )
        );

        Facades\TwillNavigation::addSecondaryNavigationForCurrentRequest(
            NavigationLink::make()->title(twillTrans('twill::lang.permissions.groups.title'))
                ->forModule('groups')
                ->onlyWhen(
                    fn () => config('twill.enabled.permissions-management') && Auth::user()->can('edit-user-groups')
                )
        );
    }
}
