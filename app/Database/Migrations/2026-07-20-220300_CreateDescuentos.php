<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDescuentos extends Migration
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
                'null' => true,
            ],
            'codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'descripcion' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'valor' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned' => true,
            ],
            'compra_minima' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned' => true,
                'default' => 0,
            ],
            'fecha_inicio' => [
                'type' => 'DATETIME',
            ],
            'fecha_fin' => [
                'type' => 'DATETIME',
            ],
            'limite_usos' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'usos_actuales' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addUniqueKey('codigo');
        $this->forge->addKey('evento_id');
        $this->forge->addKey(['activo', 'fecha_inicio', 'fecha_fin']);
        $this->forge->addForeignKey('evento_id', 'eventos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('descuentos', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('descuentos', true);
    }
}
