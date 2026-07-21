<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTiposEntrada extends Migration
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
            'funcion_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'descripcion' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'precio' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned' => true,
            ],
            'cupo' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'limite_por_compra' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 10,
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
        $this->forge->addKey('funcion_id');
        $this->forge->addUniqueKey(['funcion_id', 'nombre']);
        $this->forge->addForeignKey('funcion_id', 'funciones', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tipos_entrada', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tipos_entrada', true);
    }
}
