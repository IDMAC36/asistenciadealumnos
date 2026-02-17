<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Página principal redirige al listado de alumnos
Route::get('/', function () {
    return redirect()->route('students.index');
});

// CRUD de Alumnos
Route::resource('students', StudentController::class);

// Asistencia
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::get('/attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');
Route::post('/attendance/mark', [AttendanceController::class, 'mark'])->name('attendance.mark');
