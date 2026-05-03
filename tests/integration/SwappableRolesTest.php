<?php

namespace A17\Twill\Tests\Integration;

use A17\Twill\Facades\TwillPermissions;

enum CustomUserRole: string
{
    case CUSTOMONLY = 'Custom only';

    public static function toArray(): array
    {
        return ['CUSTOMONLY' => self::CUSTOMONLY->value];
    }
}

class SwappableRolesTest extends PermissionsTestBase
{
    public function testDefaultRoles(): void
    {
        $this->assertEquals(
            ['VIEWONLY' => 'View only', 'PUBLISHER' => 'Publisher', 'ADMIN' => 'Admin'],
            TwillPermissions::roleValues()
        );
    }

    public function testCustomRoles(): void
    {
        TwillPermissions::setRoleEnum(CustomUserRole::class);

        $this->assertEquals(
            ['CUSTOMONLY' => 'Custom only'],
            TwillPermissions::roleValues()
        );
    }
}
