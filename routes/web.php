<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffAttendanceController;
use App\Http\Controllers\PermissionRequestController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────────────
// Autenticación
// ──────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Página principal
Route::get('/', function () {
    return redirect()->route('students.index');
});

// ──────────────────────────────────────────────────────
// Rutas protegidas por autenticación
// ──────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // CRUD de Alumnos (acceso general autenticado)
    Route::resource('students', StudentController::class);

    // Asistencia (acceso general autenticado)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');
    Route::post('/attendance/mark', [AttendanceController::class, 'mark'])->name('attendance.mark');
    Route::post('/attendance/absent', [AttendanceController::class, 'markAbsent'])->name('attendance.absent');
    Route::post('/attendance/absent-all', [AttendanceController::class, 'markAllAbsent'])->name('attendance.absent.all');

    // ──────────────────────────────────────────────────
    // Personal (Staff) CRUD + Asistencia
    // ──────────────────────────────────────────────────
    Route::resource('staff', StaffController::class);
    Route::get('/staff-attendance', [StaffAttendanceController::class, 'index'])->name('staff-attendance.index');
    Route::get('/staff-attendance/scan', [StaffAttendanceController::class, 'scan'])->name('staff-attendance.scan');
    Route::post('/staff-attendance/mark', [StaffAttendanceController::class, 'mark'])->name('staff-attendance.mark');
    Route::post('/staff-attendance/absent', [StaffAttendanceController::class, 'markAbsent'])->name('staff-attendance.absent');
    Route::post('/staff-attendance/absent-all', [StaffAttendanceController::class, 'markAllAbsent'])->name('staff-attendance.absent.all');

    // ──────────────────────────────────────────────────
    // Permisos de Estudiantes
    // ──────────────────────────────────────────────────

    // Secretaria + Admin: crear solicitudes y ver listado
    Route::middleware(['role:secretaria|admin'])->group(function () {
        Route::get('/permisos/crear', [PermissionRequestController::class, 'create'])->name('permissions.create');
        Route::post('/permisos', [PermissionRequestController::class, 'store'])->name('permissions.store');
        Route::get('/permisos/solicitudes', [PermissionRequestController::class, 'solicitudes'])->name('permissions.solicitudes');
    });

    // Admin: recepción de pendientes y aceptar
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/permisos/pendientes', [PermissionRequestController::class, 'pendientes'])->name('permissions.pendientes');
        Route::patch('/permisos/{permissionRequest}/aceptar', [PermissionRequestController::class, 'aceptar'])->name('permissions.aceptar');
    });

    // Admin + Operativo: ver aceptados y exportar
    Route::middleware(['role:admin|operativo'])->group(function () {
        Route::get('/permisos/aceptados', [PermissionRequestController::class, 'aceptados'])->name('permissions.aceptados');
        Route::get('/permisos/exportar', [PermissionRequestController::class, 'exportar'])->name('permissions.exportar');
    });
});
