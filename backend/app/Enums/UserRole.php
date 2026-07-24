<?php

namespace App\Enums;

enum UserRole: string
{
    case PlatformAdmin = 'platform_admin';
    case FirmOwner = 'firm_owner';
    case Lawyer = 'lawyer';
    case Paralegal = 'paralegal';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::PlatformAdmin => 'Platform Admin',
            self::FirmOwner => 'Firm Owner / Partner',
            self::Lawyer => 'Lawyer',
            self::Paralegal => 'Paralegal / Assistant',
            self::Client => 'Client',
        };
    }

    /** Roles that belong to a firm's internal staff (not the client-facing portal). */
    public static function staffRoles(): array
    {
        return [self::FirmOwner, self::Lawyer, self::Paralegal];
    }

    /** Roles that a Firm Owner is allowed to invite into their firm. */
    public static function invitableRoles(): array
    {
        return [self::FirmOwner, self::Lawyer, self::Paralegal, self::Client];
    }

    public function isStaff(): bool
    {
        return in_array($this, self::staffRoles(), true);
    }
}
