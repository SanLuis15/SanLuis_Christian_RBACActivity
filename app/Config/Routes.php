<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Auth::index');
$routes->get('login', 'Auth::index');
$routes->post('login', 'Auth::index');
$routes->get('logout', 'Auth::logout');
$routes->get('blocked', 'Auth::forbiddenPage');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::registration');

$routes->get('unauthorized', 'Auth::unauthorized');

$routes->group('', ['filter' => ['auth', 'student']], static function ($routes) {
    // Assuming StudentController is created to handle student dashboard, or we use Home
    $routes->get('student/dashboard', 'StudentController::dashboard');
});

$routes->group('', ['filter' => ['auth']], static function ($routes) {
    $routes->get('records', 'Records::index');
    $routes->get('records/show/(:num)', 'Records::show/$1');

    // Shared Profile routes for ALL roles
    $routes->get('profile', 'ProfileController::show');
    $routes->get('profile/edit', 'ProfileController::edit');
    $routes->post('profile/update', 'ProfileController::update');

    // Shared View Settings route for ALL roles
    $routes->get('settings', 'ProfileController::settings');
});

$routes->group('', ['filter' => ['auth', 'teacher']], static function ($routes) {
    $routes->get('dashboard', 'Home::index');
    $routes->get('dashboard-v2', 'Home::dashboardV2');
    $routes->get('dashboard-v3', 'Home::dashboardV3');

    $routes->get('students', 'Student::index');
    $routes->get('students/show/(:num)', 'Student::show/$1');
    $routes->get('students/edit/(:num)', 'Student::edit/$1');
    $routes->post('students/store', 'Student::store');
    $routes->post('students/update/(:num)', 'Student::update/$1');
    $routes->post('students/delete/(:num)', 'Student::delete/$1');
});

$routes->group('', ['filter' => ['auth', 'records_editor']], static function ($routes) {
    $routes->get('records/create', 'Records::create');
    $routes->get('records/edit/(:num)', 'Records::edit/$1');
    $routes->post('records/store', 'Records::store');
    $routes->post('records/update/(:num)', 'Records::update/$1');
    $routes->post('records/delete/(:num)', 'Records::delete/$1');
});

$routes->group('', ['filter' => ['auth', 'coordinator']], static function ($routes) {
    // Shared functionality accessed
});

$routes->group('admin', ['filter' => ['auth', 'admin']], static function ($routes) {
    $routes->get('roles', 'Admin\RoleController::index');
    $routes->get('roles/create', 'Admin\RoleController::create');
    $routes->post('roles/store', 'Admin\RoleController::store');
    $routes->get('roles/edit/(:num)', 'Admin\RoleController::edit/$1');
    $routes->post('roles/update/(:num)', 'Admin\RoleController::update/$1');
    $routes->get('roles/delete/(:num)', 'Admin\RoleController::delete/$1');

    $routes->get('users', 'Admin\UserAdminController::index');
    $routes->post('users/assign-role/(:num)', 'Admin\UserAdminController::assignRole/$1');
    $routes->post('users/store', 'Admin\UserAdminController::storeUser');
    $routes->post('users/delete/(:num)', 'Admin\UserAdminController::deleteUser/$1');
});

// ════════════════════════════════════════════════════════════
//  API v1 — token-authenticated JSON endpoints
// ════════════════════════════════════════════════════════════

// Issue token
$routes->post('api/v1/auth/token', 'Api\AuthController::issueToken');

// Protected API routes
$routes->group('api/v1', ['filter' => 'api_auth'], static function ($routes) {
    // Auth
    $routes->delete('auth/token', 'Api\AuthController::revokeToken');

    // Students resource
    $routes->get('students',       'Api\StudentsController::index');
    $routes->get('students/(:num)', 'Api\StudentsController::show/$1');
});
