<?php

namespace Modules\UserManagementModule\Enums;

enum UserRole:string
{
    case SUPERADMIN = 'super-admin';
    case MANAGER = 'manager';

    case INSTRUCTOR = 'instructor';
    case STUDENT = 'student';
    case AUDITOR = 'auditor';
}
