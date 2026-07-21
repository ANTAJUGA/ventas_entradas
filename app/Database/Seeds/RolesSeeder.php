<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Administrador',
                'slug' => 'administrador',
                'descripcion' => 'Acceso completo a la administración del sistema.',
                'activo' => true,
            ],
            [
                'nombre' => 'Organizador',
                'slug' => 'organizador',
                'descripcion' => 'Gestiona eventos, funciones, tipos de entrada y aforo.',
                'activo' => true,
            ],
            [
                'nombre' => 'Vendedor',
                'slug' => 'vendedor',
                'descripcion' => 'Registra ventas y consulta entradas.',
                'activo' => true,
            ],
            [
                'nombre' => 'Control de acceso',
                'slug' => 'control-acceso',
                'descripcion' => 'Valida entradas en el acceso a las funciones.',
                'activo' => true,
            ],
            [
                'nombre' => 'Cliente',
                'slug' => 'cliente',
                'descripcion' => 'Consulta eventos y adquiere entradas.',
                'activo' => true,
            ],
        ];

        $this->db->table('roles')->upsertBatch($roles);
    }
}
