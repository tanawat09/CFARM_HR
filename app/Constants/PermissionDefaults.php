<?php

namespace App\Constants;

class PermissionDefaults
{
    /**
     * Default permissions for each role.
     * These are used when no database override exists.
     * Format: 'permission_key' => ['role1', 'role2', ...]
     */
    public const DEFAULTS = [
        // การจัดการระบบ
        'system.settings'     => ['admin', 'hr'],
        'system.leave_policy' => ['admin', 'hr'],
        'system.holidays'     => ['admin', 'hr'],
        'system.line'         => ['admin', 'hr'],
        'system.roles'        => ['admin'],

        // การจัดการบุคลากร
        'hr.employees'   => ['admin', 'hr'],
        'hr.departments' => ['admin', 'hr'],
        'hr.positions'   => ['admin', 'hr'],
        'hr.shifts'      => ['admin', 'hr'],
        'hr.worksites'   => ['admin', 'hr'],

        // การอนุมัติ
        'approval.leave' => ['admin', 'hr', 'managing_director', 'deputy_md', 'manager', 'supervisor'],
        'approval.line'  => ['admin', 'hr', 'managing_director', 'deputy_md', 'manager', 'supervisor'],
        'approval.view'  => ['admin', 'hr', 'managing_director', 'deputy_md', 'manager', 'supervisor'],

        // การลาหยุด
        'leave.create'   => ['admin', 'hr', 'managing_director', 'deputy_md', 'manager', 'supervisor', 'employee'],
        'leave.view_own' => ['admin', 'hr', 'managing_director', 'deputy_md', 'manager', 'supervisor', 'employee'],

        // การลงเวลา
        'attendance.checkin'  => ['admin', 'hr', 'managing_director', 'deputy_md', 'manager', 'supervisor', 'employee'],
        'attendance.history'  => ['admin', 'hr', 'managing_director', 'deputy_md', 'manager', 'supervisor', 'employee'],

        // รายงาน
        'reports.view'   => ['admin', 'hr'],
        'reports.export' => ['admin', 'hr'],
    ];

    /**
     * Check if a role has a given permission by default.
     */
    public static function hasDefault(string $role, string $permission): bool
    {
        $allowedRoles = self::DEFAULTS[$permission] ?? [];
        return in_array($role, $allowedRoles);
    }

    /**
     * Get all permission keys.
     */
    public static function allKeys(): array
    {
        return array_keys(self::DEFAULTS);
    }
}
