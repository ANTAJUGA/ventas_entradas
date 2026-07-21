<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin@ventas-entradas.local';
        $usuarios = $this->db->table('usuarios');

        if ($usuarios->where('email', $email)->get()->getRowArray() !== null) {
            echo "El administrador local ya existe: {$email}" . PHP_EOL;

            return;
        }

        $rol = $this->db->table('roles')
            ->where('slug', 'administrador')
            ->get()
            ->getRowArray();

        if ($rol === null) {
            throw new RuntimeException('No existe el rol administrador. Ejecuta primero RolesSeeder.');
        }

        $password = 'Adm-' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');

        $usuarios->insert([
            'rol_id' => $rol['id'],
            'nombres' => 'Administrador',
            'apellidos' => 'Local',
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'estado' => 'activo',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        echo "Administrador local creado." . PHP_EOL;
        echo "Correo: {$email}" . PHP_EOL;
        echo "Contraseña temporal: {$password}" . PHP_EOL;
        echo "Guarda esta contraseña: no volverá a mostrarse." . PHP_EOL;
    }
}
