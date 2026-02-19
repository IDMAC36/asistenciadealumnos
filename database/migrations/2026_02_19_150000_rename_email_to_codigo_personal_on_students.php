<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('codigo_personal')->nullable()->after('name');
        });

        // Copy email data to codigo_personal
        \DB::table('students')->whereNotNull('email')->update([
            'codigo_personal' => \DB::raw('email'),
        ]);

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
        });

        \DB::table('students')->whereNotNull('codigo_personal')->update([
            'email' => \DB::raw('codigo_personal'),
        ]);

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('codigo_personal');
        });
    }
};
