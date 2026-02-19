<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_requests', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');           // Nombre del estudiante
            $table->string('grado');
            $table->string('nivel');
            $table->text('motivo');
            $table->string('quien_solicita');
            $table->string('por_via');
            $table->enum('estado', ['pendiente', 'aceptado'])->default('pendiente');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_requests');
    }
};
