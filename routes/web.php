<?php

// Authentication routes (harus tetap public/publik)
Route::get('/login', 'AuthController@showLoginForm')->name('login');
Route::post('/login', 'AuthController@login');
Route::get('/register', 'AuthController@showRegistrationForm')->name('register');
Route::post('/register', 'AuthController@register');
Route::post('/logout', 'AuthController@logout')->name('logout');

// Kiosk route (public/publik)
Route::get('/', 'KioskController@index')->name('kiosk');  // Homepage jadi kiosk
Route::get('/kiosk', 'KioskController@index')->name('kiosk.index');
Route::post('/kiosk/process', 'KioskController@processAttendance')->name('kiosk.process');

// Protected routes (perlu login)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
    
    // Attendance history tetap ada untuk pengguna biasa
    Route::get('/attendance/history', 'AttendanceController@history')->name('attendance.history');
    
    // Admin routes - pastikan nama route sudah benar
    Route::group(['middleware' => 'admin', 'prefix' => 'admin'], function () {
        // User management
        Route::get('/users', 'UserController@index')->name('admin.users');
        Route::get('/users/create', 'UserController@create')->name('admin.users.create');
        Route::post('/users', 'UserController@store')->name('admin.users.store');
        Route::get('/users/{id}/edit', 'UserController@edit')->name('admin.users.edit');
        Route::put('/users/{id}', 'UserController@update')->name('admin.users.update');
        Route::delete('/users/{id}', 'UserController@destroy')->name('admin.users.destroy');
        Route::delete('/users/{id}/reset-face', 'UserController@resetFace')->name('admin.users.reset-face');
        
        // Face registration for user by admin
        Route::get('/users/{id}/register-face', 'UserController@showRegisterFace')->name('admin.users.register-face');
        Route::post('/users/{id}/register-face', 'UserController@registerFace')->name('admin.users.save-face');
        
        // Attendance management
        Route::get('/attendance', 'AttendanceController@adminIndex')->name('admin.attendance');
    });
});