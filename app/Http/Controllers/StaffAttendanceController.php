<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffAttendance;
use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    /**
     * Listado de asistencia de personal del día.
     */
    public function index(Request $request)
    {
        $date = $request->query('date', now()->format('Y-m-d'));

        $staffMembers = Staff::with(['attendances' => function ($query) use ($date) {
            $query->whereDate('date', $date);
        }])->orderBy('name')->get();

        $staffList = $staffMembers->map(function ($member) {
            $attendance = $member->attendances->first();
            return (object) [
                'staff'         => $member,
                'attendance'    => $attendance,
                'status'        => $attendance ? $attendance->status : 'sin_registro',
                'check_in_time' => $attendance ? $attendance->check_in_time : null,
            ];
        });

        $totalStaff  = $staffMembers->count();
        $presentes   = $staffList->where('status', 'presente')->count();
        $ausentes    = $staffList->where('status', 'ausente')->count();
        $sinRegistro = $staffList->where('status', 'sin_registro')->count();

        return view('staff-attendance.index', compact(
            'staffList', 'date', 'totalStaff', 'presentes', 'ausentes', 'sinRegistro'
        ));
    }

    /**
     * Vista del escáner QR para personal.
     */
    public function scan()
    {
        return view('staff-attendance.scan');
    }

    /**
     * Marcar asistencia vía AJAX (escaneo QR).
     */
    public function mark(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $staff = Staff::where('qr_code', $request->qr_code)->first();

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Personal no encontrado.',
            ], 404);
        }

        $existing = StaffAttendance::where('staff_id', $staff->id)
            ->whereDate('date', now()->format('Y-m-d'))
            ->first();

        if ($existing) {
            if ($existing->status === 'presente') {
                return response()->json([
                    'success' => false,
                    'already_registered' => true,
                    'message' => '⚠️ ' . $staff->name . ' ya ha sido registrado.',
                    'staff' => [
                        'name' => $staff->name,
                        'role' => $staff->role,
                        'time' => $existing->check_in_time,
                    ],
                ]);
            }

            $existing->update([
                'status'        => 'presente',
                'check_in_time' => now()->format('H:i:s'),
            ]);

            return response()->json([
                'success' => true,
                'message' => '✅ Asistencia marcada para ' . $staff->name . ' (actualizado de ausente a presente)',
                'staff' => [
                    'name' => $staff->name,
                    'role' => $staff->role,
                    'time' => now()->format('H:i:s'),
                ],
            ]);
        }

        StaffAttendance::create([
            'staff_id'      => $staff->id,
            'date'          => now()->format('Y-m-d'),
            'check_in_time' => now()->format('H:i:s'),
            'status'        => 'presente',
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Asistencia marcada para ' . $staff->name,
            'staff' => [
                'name' => $staff->name,
                'role' => $staff->role,
                'time' => now()->format('H:i:s'),
            ],
        ]);
    }

    /**
     * Marcar inasistencia manualmente.
     */
    public function markAbsent(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'date'     => 'required|date',
        ]);

        $staff = Staff::findOrFail($request->staff_id);

        StaffAttendance::updateOrCreate(
            [
                'staff_id' => $staff->id,
                'date'     => $request->date,
            ],
            [
                'status'        => 'ausente',
                'check_in_time' => now()->format('H:i:s'),
            ]
        );

        return redirect()->route('staff-attendance.index', ['date' => $request->date])
            ->with('success', $staff->name . ' marcado como ausente.');
    }

    /**
     * Marcar a todos sin registro como ausentes.
     */
    public function markAllAbsent(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $date = $request->date;

        $registeredIds = StaffAttendance::whereDate('date', $date)->pluck('staff_id');
        $unregistered = Staff::whereNotIn('id', $registeredIds)->get();

        foreach ($unregistered as $member) {
            StaffAttendance::create([
                'staff_id'      => $member->id,
                'date'          => $date,
                'check_in_time' => now()->format('H:i:s'),
                'status'        => 'ausente',
            ]);
        }

        return redirect()->route('staff-attendance.index', ['date' => $date])
            ->with('success', $unregistered->count() . ' personal marcado(s) como ausente(s).');
    }
}
