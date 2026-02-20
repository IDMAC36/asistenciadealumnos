<?php

namespace App\Http\Controllers;

use App\Exports\DailyStaffAttendanceExport;
use App\Exports\MonthlyStaffAttendanceReportExport;
use App\Exports\StaffAttendanceHistoryExport;
use App\Models\Staff;
use App\Models\StaffAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
     * Exportar asistencia del día del personal a Excel.
     */
    public function exportDaily(Request $request)
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        $filename = 'Asistencia_Personal_' . $date . '.xlsx';

        return Excel::download(new DailyStaffAttendanceExport($date), $filename);
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

    /**
     * Historial de asistencia mensual por personal.
     */
    public function history(Request $request)
    {
        $staffMembers = Staff::orderBy('name')->get();
        $staffId = $request->query('staff_id');
        $month = $request->query('month', now()->format('Y-m'));

        $staff = $staffId ? Staff::find($staffId) : null;
        $calendar = [];
        $stats = ['presente' => 0, 'ausente' => 0, 'sin_registro' => 0];

        if ($staff) {
            $startOfMonth = Carbon::parse($month . '-01');
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
            $today = now()->startOfDay();

            $attendances = StaffAttendance::where('staff_id', $staff->id)
                ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                ->get()
                ->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d'));

            for ($day = $startOfMonth->copy(); $day->lte($endOfMonth); $day->addDay()) {
                $dateStr = $day->format('Y-m-d');
                $dayOfWeek = $day->dayOfWeek;
                $isWeekend = in_array($dayOfWeek, [0, 6]);
                $isFuture = $day->gt($today);

                if ($isWeekend || $isFuture) {
                    $status = null;
                } elseif (isset($attendances[$dateStr])) {
                    $status = $attendances[$dateStr]->status;
                    $stats[$status] = ($stats[$status] ?? 0) + 1;
                } else {
                    $status = 'sin_registro';
                    $stats['sin_registro']++;
                }

                $calendar[] = (object) [
                    'date' => $dateStr,
                    'day' => $day->day,
                    'dayOfWeek' => $dayOfWeek,
                    'status' => $status,
                    'isWeekend' => $isWeekend,
                    'isFuture' => $isFuture,
                    'checkInTime' => isset($attendances[$dateStr]) ? $attendances[$dateStr]->check_in_time : null,
                ];
            }
        }

        $prevMonth = Carbon::parse($month . '-01')->subMonth()->format('Y-m');
        $nextMonth = Carbon::parse($month . '-01')->addMonth()->format('Y-m');
        $monthLabel = Carbon::parse($month . '-01')->locale('es')->isoFormat('MMMM YYYY');

        return view('staff-attendance.history', compact(
            'staffMembers', 'staff', 'staffId', 'month', 'calendar', 'stats',
            'prevMonth', 'nextMonth', 'monthLabel'
        ));
    }

    /**
     * Exportar historial de asistencia de personal a Excel.
     */
    public function exportHistory(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'month' => 'required|date_format:Y-m',
        ]);

        $staff = Staff::findOrFail($request->staff_id);
        $month = $request->month;
        $monthLabel = Carbon::parse($month . '-01')->locale('es')->isoFormat('MMMM_YYYY');
        $filename = 'Historial_' . str_replace(' ', '_', $staff->name) . '_' . $monthLabel . '.xlsx';

        return Excel::download(
            new StaffAttendanceHistoryExport($staff, $month),
            $filename
        );
    }

    /**
     * Reporte mensual general de asistencia del personal.
     */
    public function monthlyReport(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($month . '-01');
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = now()->startOfDay();

        $weekdays = [];
        for ($day = $startOfMonth->copy(); $day->lte($endOfMonth); $day->addDay()) {
            if (!in_array($day->dayOfWeek, [0, 6])) {
                $weekdays[] = $day->copy();
            }
        }

        $staffMembers = Staff::orderBy('role')->orderBy('name')->get();
        $grouped = $staffMembers->groupBy('role');

        $attendances = StaffAttendance::whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()
            ->groupBy('staff_id')
            ->map(fn($items) => $items->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d')));

        $reportData = [];
        foreach ($grouped as $role => $membersInRole) {
            $reportData[$role] = [];
            foreach ($membersInRole as $member) {
                $memberDays = [];
                $memberAttendances = $attendances->get($member->id, collect());

                foreach ($weekdays as $day) {
                    $dateStr = $day->format('Y-m-d');
                    $isFuture = $day->gt($today);

                    if ($isFuture) {
                        $status = null;
                    } elseif ($memberAttendances && $memberAttendances->has($dateStr)) {
                        $status = $memberAttendances->get($dateStr)->status;
                    } else {
                        $status = 'sin_registro';
                    }

                    $memberDays[$dateStr] = $status;
                }

                $reportData[$role][] = (object) [
                    'staff' => $member,
                    'days' => $memberDays,
                ];
            }
        }

        $prevMonth = $startOfMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $startOfMonth->copy()->addMonth()->format('Y-m');
        $monthLabel = $startOfMonth->locale('es')->isoFormat('MMMM YYYY');

        return view('staff-attendance.monthly-report', compact(
            'month', 'weekdays', 'reportData', 'prevMonth', 'nextMonth', 'monthLabel'
        ));
    }

    /**
     * Exportar reporte mensual del personal a Excel.
     */
    public function exportMonthlyReport(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $monthLabel = Carbon::parse($month . '-01')->locale('es')->isoFormat('MMMM_YYYY');
        $filename = 'Reporte_Mensual_Personal_' . $monthLabel . '.xlsx';

        return Excel::download(new MonthlyStaffAttendanceReportExport($month), $filename);
    }
}
