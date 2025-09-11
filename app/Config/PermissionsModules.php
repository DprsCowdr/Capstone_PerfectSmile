<?php
namespace Config;

/**
 * Central list of application modules to use for permissions matrix.
 * Update this file to add/remove modules shown in Role create/edit pages.
 */
class PermissionsModules
{
    public static array $modules = [
        'Patients',
        'Appointments',
        'Procedures',
        'Procedure Services',
        'Prescriptions',
        'Invoices',
        'Payments',
        'Branches',
        'Users',
        'Role & Permissions',
        'Checkin',
        'Queue',
        'Checkup',
        'Dental Records',
        'Dental Charts',
        'Services',
        'Calendar',
        'Settings',
        'Reports',
        'Notifications',
        'Messages',
        'Forms',
        'Billing'
    ];
}
