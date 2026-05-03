<?php

namespace A17\Twill\Models\Enums;

enum UserRole: string
{
    case VIEWONLY = 'View only';
    case PUBLISHER = 'Publisher';
    case ADMIN = 'Admin';

    public static function toArray(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $role) => [$role->name => $role->value])->all();
    }
}
