<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StudentController extends Controller
{
    /**
     * Listado de todos los alumnos.
     */
    public function index()
    {
        $students = Student::latest()->get();
        return view('students.index', compact('students'));
    }

    /**
     * Formulario para crear un alumno.
     */
    public function create()
    {
        return view('students.create');
    }

    /**
     * Guardar nuevo alumno con QR generado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'codigo_personal'  => 'required|string|max:100',
            'grado'            => 'required|string|max:100',
        ]);

        $student = Student::create([
            'name'             => $request->name,
            'codigo_personal'  => $request->codigo_personal,
            'grado'            => $request->grado,
            'qr_code'          => (string) Str::uuid(),
        ]);

        return redirect()->route('students.show', $student)
            ->with('success', 'Alumno registrado exitosamente.');
    }

    /**
     * Ver detalle de un alumno con su QR.
     */
    public function show(Student $student)
    {
        $qrCode = QrCode::size(250)->generate($student->qr_code);
        return view('students.show', compact('student', 'qrCode'));
    }

    /**
     * Formulario para editar un alumno.
     */
    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    /**
     * Actualizar alumno.
     */
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'codigo_personal'  => 'required|string|max:100',
            'grado'            => 'required|string|max:100',
        ]);

        $student->update([
            'name'             => $request->name,
            'codigo_personal'  => $request->codigo_personal,
            'grado'            => $request->grado,
        ]);

        return redirect()->route('students.show', $student)
            ->with('success', 'Alumno actualizado exitosamente.');
    }

    /**
     * Eliminar alumno.
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Alumno eliminado exitosamente.');
    }
}
