<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFunciones extends Migration
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
            'evento_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'fecha_inicio' => [
                'type' => 'DATETIME',
            ],
            'fecha_fin' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'venta_inicio' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'venta_fin' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'recinto' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'direccion' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'ciudad' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'aforo_total' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'estado' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'programada',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('evento_id');
        $this->forge->addKey('fecha_inicio');
        $this->forge->addKey('estado');
        $this->forge->addForeignKey('evento_id', 'eventos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('funciones', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('funciones', true);
    }
}
