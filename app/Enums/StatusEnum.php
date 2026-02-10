<?php
namespace App\Enums;

class StatusEnum
{
    const ACTIVE = 'active';
    const INACTIVE = 'inactive';
    const DELETED = 'deleted';
    const SUSPENDED = 'suspended';

    public static function values()
    {
        return [
            self::ACTIVE,
            self::INACTIVE,
            self::DELETED,
            self::SUSPENDED,
        ];
    }
}
