<?php
declare(strict_types=1);

/** [phương thức, route, controller, action] — thêm route cho module mới ở đây. */
return [
    ['GET',  'home/index',        HomeController::class,      'index'],

    // Xác thực
    ['GET',  'auth/login',        AuthController::class,      'showLogin'],
    ['POST', 'auth/login',        AuthController::class,      'login'],
    ['GET',  'auth/register',     AuthController::class,      'showRegister'],
    ['POST', 'auth/register',     AuthController::class,      'register'],
    ['POST', 'auth/logout',       AuthController::class,      'logout'],
    ['GET',  'auth/check-email',  AuthController::class,      'checkEmail'],   // endpoint JSON cho Fetch API

    // Khu vực theo vai trò
    ['GET',  'dashboard/index',    DashboardController::class, 'index'],
    ['GET',  'dashboard/student',  DashboardController::class, 'student'],
    ['GET',  'dashboard/lecturer', DashboardController::class, 'lecturer'],

    // Quản trị
    ['GET',  'admin/dashboard',    AdminController::class,     'dashboard'],
    ['GET',  'admin/users',        AdminController::class,     'users'],
    ['POST', 'admin/user-status',  AdminController::class,     'updateStatus'],
    ['POST', 'admin/user-role',    AdminController::class,     'updateRole'],
    ['GET',  'admin/catalog',      CatalogController::class,   'index'],
    ['POST', 'admin/catalog-save', CatalogController::class,   'save'],
    ['POST', 'admin/catalog-delete', CatalogController::class, 'delete'],
];
