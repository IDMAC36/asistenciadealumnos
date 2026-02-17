<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Listado de asistencia del día actual.
     */
    public function index(Request $request)
    {
        $date = $request->query('date', now()->format('Y-m-d'));

        $attendances = Attendance::whereDate('date', $date)
            ->with('student')
            ->latest('check_in_time')
            ->get();

        $totalStudents = Student::count();

        return view('attendance.index', compact('attendances', 'date', 'totalStudents'));
    }

    /**
     * Vista del escáner QR.
     */
    public function scan()
    {
        return view('attendance.scan');
    }

    /**
     * Marcar asistencia vía AJAX (escaneo QR).
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

        $attendance = Attendance::firstOrCreate(
            [
                'student_id' => $student->id,
                'date'       => now()->format('Y-m-d'),
            ],
            [
                'check_in_time' => now()->format('H:i:s'),
            ]
        );

        if ($attendance->wasRecentlyCreated) {
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

        return response()->json([
            'success' => false,
            'message' => '⚠️ ' . $student->name . ' ya registró asistencia hoy.',
        ]);
    }
}
