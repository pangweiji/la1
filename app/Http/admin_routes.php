<?php

/* ================== Homepage ================== */
Route::get('/', 'HomeController@index');
Route::get('/home', 'Home\HomeController@index');
Route::auth();

/* ================== Access Uploaded Files ================== */
Route::get('files/{hash}/{name}', 'LA\UploadsController@get_file');

/*
|--------------------------------------------------------------------------
| Admin Application Routes
|--------------------------------------------------------------------------
*/

$as = "";
if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
    $as = config('laraadmin.adminRoute').'.';
    
    // Routes for Laravel 5.3
    Route::get('/logout', 'Auth\LoginController@logout');
}

Route::group(['as' => $as, 'middleware' => ['auth', 'permission:ADMIN_PANEL']], function () {
    
    /* ================== Dashboard ================== */
    
    Route::get(config('laraadmin.adminRoute'), 'LA\DashboardController@index');
    Route::get(config('laraadmin.adminRoute'). '/dashboard', 'LA\DashboardController@index');
    
    /* ================== Users ================== */
    Route::resource(config('laraadmin.adminRoute') . '/users', 'LA\UsersController');
    Route::get(config('laraadmin.adminRoute') . '/user_dt_ajax', 'LA\UsersController@dtajax');
    
    /* ================== Uploads ================== */
    Route::resource(config('laraadmin.adminRoute') . '/uploads', 'LA\UploadsController');
    Route::post(config('laraadmin.adminRoute') . '/upload_files', 'LA\UploadsController@upload_files');
    Route::get(config('laraadmin.adminRoute') . '/uploaded_files', 'LA\UploadsController@uploaded_files');
    Route::post(config('laraadmin.adminRoute') . '/uploads_update_caption', 'LA\UploadsController@update_caption');
    Route::post(config('laraadmin.adminRoute') . '/uploads_update_filename', 'LA\UploadsController@update_filename');
    Route::post(config('laraadmin.adminRoute') . '/uploads_update_public', 'LA\UploadsController@update_public');
    Route::post(config('laraadmin.adminRoute') . '/uploads_delete_file', 'LA\UploadsController@delete_file');
    
    /* ================== Roles ================== */
    Route::resource(config('laraadmin.adminRoute') . '/roles', 'LA\RolesController');
    Route::get(config('laraadmin.adminRoute') . '/role_dt_ajax', 'LA\RolesController@dtajax');
    Route::post(config('laraadmin.adminRoute') . '/save_module_role_permissions/{id}', 'LA\RolesController@save_module_role_permissions');
    
    /* ================== Permissions ================== */
    Route::resource(config('laraadmin.adminRoute') . '/permissions', 'LA\PermissionsController');
    Route::get(config('laraadmin.adminRoute') . '/permission_dt_ajax', 'LA\PermissionsController@dtajax');
    Route::post(config('laraadmin.adminRoute') . '/save_permissions/{id}', 'LA\PermissionsController@save_permissions');
    
    /* ================== Departments ================== */
    Route::resource(config('laraadmin.adminRoute') . '/departments', 'LA\DepartmentsController');
    Route::get(config('laraadmin.adminRoute') . '/department_dt_ajax', 'LA\DepartmentsController@dtajax');
    
    /* ================== Employees ================== */
    Route::resource(config('laraadmin.adminRoute') . '/employees', 'LA\EmployeesController');
    Route::get(config('laraadmin.adminRoute') . '/employee_dt_ajax', 'LA\EmployeesController@dtajax');
    Route::post(config('laraadmin.adminRoute') . '/change_password/{id}', 'LA\EmployeesController@change_password');
    
    /* ================== Organizations ================== */
    Route::resource(config('laraadmin.adminRoute') . '/organizations', 'LA\OrganizationsController');
    Route::get(config('laraadmin.adminRoute') . '/organization_dt_ajax', 'LA\OrganizationsController@dtajax');

    /* ================== Backups ================== */
    Route::resource(config('laraadmin.adminRoute') . '/backups', 'LA\BackupsController');
    Route::get(config('laraadmin.adminRoute') . '/backup_dt_ajax', 'LA\BackupsController@dtajax');
    Route::post(config('laraadmin.adminRoute') . '/create_backup_ajax', 'LA\BackupsController@create_backup_ajax');
    Route::get(config('laraadmin.adminRoute') . '/downloadBackup/{id}', 'LA\BackupsController@downloadBackup');
});

Route::group(['as' => 'Home', 'middleware' => ['auth', 'permission:ADMIN_PANEL']], function () {
    /* ================== Message ================== */
    Route::resource(config('laraadmin.homeRoute') . '/message', 'Home\MessageController');
    Route::get(config('laraadmin.homeRoute') . '/message_list', 'Home\MessageController@list');
    Route::get(config('laraadmin.homeRoute') . '/message_detail/{id}', 'Home\MessageController@detail');
    Route::get(config('laraadmin.homeRoute') . '/message_accountList', 'Home\MessageController@accountList');
    Route::post(config('laraadmin.homeRoute') . '/message_addAccount_ajax', 'Home\MessageController@addAccountAjax');
    Route::get(config('laraadmin.homeRoute') . '/message_deleteAccount_ajax/{id}', 'Home\MessageController@deleteAccountAjax');
    Route::post(config('laraadmin.homeRoute') . '/message_reply', 'Home\MessageController@reply');

    /* ================== Gmail manager ================== */
    Route::any(config('laraadmin.homeRoute') . '/gmail_list', 'Home\GmailManagerController@list');
    Route::any(config('laraadmin.homeRoute') . '/gmail_auth', 'Home\GmailManagerController@auth');
    Route::any(config('laraadmin.homeRoute') . '/gmail_getCode', 'Home\GmailManagerController@getCode');

    /* ================== Test ================== */
    Route::any(config('laraadmin.homeRoute') . '/test', 'Home\TestController@test');
    Route::any(config('laraadmin.homeRoute') . '/test_show', 'Home\TestController@show');

    /* ================== Test1 ================== */
    Route::resource(config('laraadmin.homeRoute') . '/test1', 'Home\Test1Controller');

    Route::resource(config('laraadmin.homeRoute') . '/user', 'Home\UsersController');
    Route::get(config('laraadmin.homeRoute') . '/userinfo', 'Home\UsersController@getUsers');
});