<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateControlAccesos extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'entrada_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'usuario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'resultado' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'motivo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'punto_acceso' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'direccion_ip' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'registrado_at' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('entrada_id');
        $this->forge->addKey('usuario_id');
        $this->forge->addKey(['resultado', 'registrado_at']);
        $this->forge->addForeignKey('entrada_id', 'entradas', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('control_accesos', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('control_accesos', true);
    }
}
