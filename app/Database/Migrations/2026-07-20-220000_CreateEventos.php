<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventos extends Migration
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
            'organizador_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => 170,
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'categoria' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'imagen' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'estado' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'borrador',
            ],
            'publicado_at' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('organizador_id');
        $this->forge->addKey('estado');
        $this->forge->addForeignKey('organizador_id', 'usuarios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('eventos', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('eventos', true);
    }
}
