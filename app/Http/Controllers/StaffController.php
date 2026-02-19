<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StaffController extends Controller
{
    public function index()
    {
        $staff = Staff::latest()->get();
        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        return view('staff.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
        ]);

        $staff = Staff::create([
            'name'    => $request->name,
            'role'    => $request->role,
            'qr_code' => 'STAFF-' . (string) Str::uuid(),
        ]);

        return redirect()->route('staff.show', $staff)
            ->with('success', 'Personal registrado exitosamente.');
    }

    public function show(Staff $staff)
    {
        $qrCode = QrCode::size(250)->generate($staff->qr_code);
        return view('staff.show', compact('staff', 'qrCode'));
    }

    public function edit(Staff $staff)
    {
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, Staff $staff)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
        ]);

        $staff->update([
            'name' => $request->name,
            'role' => $request->role,
        ]);

        return redirect()->route('staff.show', $staff)
            ->with('success', 'Personal actualizado exitosamente.');
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();
        return redirect()->route('staff.index')
            ->with('success', 'Personal eliminado exitosamente.');
    }
}
