<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            'crear_solicitud',
            'ver_solicitudes',
            'aceptar_solicitud',
            'ver_permisos_aceptados',
            'exportar_permisos',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        $secretaria = Role::firstOrCreate(['name' => 'secretaria']);
        $secretaria->syncPermissions(['crear_solicitud', 'ver_solicitudes']);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'crear_solicitud',
            'ver_solicitudes',
            'aceptar_solicitud',
            'ver_permisos_aceptados',
            'exportar_permisos',
        ]);

        $operativo = Role::firstOrCreate(['name' => 'operativo']);
        $operativo->syncPermissions(['ver_permisos_aceptados', 'exportar_permisos']);

        // Crear usuarios de ejemplo
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@asistencia.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );
        $adminUser->assignRole('admin');

        $secretariaUser = User::firstOrCreate(
            ['email' => 'secretaria@asistencia.com'],
            [
                'name' => 'Secretaria',
                'password' => Hash::make('password'),
            ]
        );
        $secretariaUser->assignRole('secretaria');

        $operativoUser = User::firstOrCreate(
            ['email' => 'operativo@asistencia.com'],
            [
                'name' => 'Operativo',
                'password' => Hash::make('password'),
            ]
        );
        $operativoUser->assignRole('operativo');
    }
}
