<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Listado de asistencia del día (todos los alumnos con su estado).
     */
    public function index(Request $request)
    {
        $date = $request->query('date', now()->format('Y-m-d'));

        $students = Student::with(['attendances' => function ($query) use ($date) {
            $query->whereDate('date', $date);
        }])->orderBy('name')->get();

        // Mapear cada alumno con su estado del día
        $studentList = $students->map(function ($student) use ($date) {
            $attendance = $student->attendances->first();
            return (object) [
                'student'       => $student,
                'attendance'    => $attendance,
                'status'        => $attendance ? $attendance->status : 'sin_registro',
                'check_in_time' => $attendance ? $attendance->check_in_time : null,
            ];
        });

        $totalStudents = $students->count();
        $presentes     = $studentList->where('status', 'presente')->count();
        $ausentes      = $studentList->where('status', 'ausente')->count();
        $sinRegistro   = $studentList->where('status', 'sin_registro')->count();

        return view('attendance.index', compact(
            'studentList', 'date', 'totalStudents', 'presentes', 'ausentes', 'sinRegistro'
        ));
    }

    /**
     * Vista del escáner QR.
     */
    public function scan()
    {
        return view('attendance.scan');
    }

    /**
     * Marcar asistencia vía AJAX (escaneo QR) — 1 sola vez por día.
     */
    public function mark(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $student = Student::where('qr_code', $request->qr_code)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado.',
            ], 404);
        }

        // Verificar si ya tiene cualquier registro hoy
        $existing = Attendance::where('student_id', $student->id)
            ->whereDate('date', now()->format('Y-m-d'))
            ->first();

        if ($existing) {
            if ($existing->status === 'presente') {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ ' . $student->name . ' ya registró asistencia hoy.',
                ]);
            }

            // Si estaba marcado como ausente, actualizar a presente
            $existing->update([
                'status'        => 'presente',
                'check_in_time' => now()->format('H:i:s'),
            ]);

            return response()->json([
                'success' => true,
                'message' => '✅ Asistencia marcada para ' . $student->name . ' (se actualizó de ausente a presente)',
                'student' => [
                    'name'  => $student->name,
                    'email' => $student->email,
                    'time'  => now()->format('H:i:s'),
                ],
            ]);
        }

        // Crear nuevo registro de asistencia
        Attendance::create([
            'student_id'    => $student->id,
            'date'          => now()->format('Y-m-d'),
            'check_in_time' => now()->format('H:i:s'),
            'status'        => 'presente',
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Asistencia marcada para ' . $student->name,
            'student' => [
                'name'  => $student->name,
                'email' => $student->email,
                'time'  => now()->format('H:i:s'),
            ],
        ]);
    }

    /**
     * Marcar inasistencia manualmente.
     */
    public function markAbsent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'date'       => 'required|date',
        ]);

        $student = Student::findOrFail($request->student_id);

        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $student->id,
                'date'       => $request->date,
            ],
            [
                'status'        => 'ausente',
                'check_in_time' => now()->format('H:i:s'),
            ]
        );

        return redirect()->route('attendance.index', ['date' => $request->date])
            ->with('success', $student->name . ' marcado como ausente.');
    }

    /**
     * Marcar a todos los alumnos sin registro como ausentes.
     */
    public function markAllAbsent(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->date;

        // Obtener IDs de alumnos que YA tienen registro ese día
        $registeredIds = Attendance::whereDate('date', $date)->pluck('student_id');

        // Alumnos sin registro
        $unregistered = Student::whereNotIn('id', $registeredIds)->get();

        foreach ($unregistered as $student) {
            Attendance::create([
                'student_id'    => $student->id,
                'date'          => $date,
                'check_in_time' => now()->format('H:i:s'),
                'status'        => 'ausente',
            ]);
        }

        return redirect()->route('attendance.index', ['date' => $date])
            ->with('success', $unregistered->count() . ' alumno(s) marcados como ausentes.');
    }
}
