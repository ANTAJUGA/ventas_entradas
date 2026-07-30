<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateArtistas extends Migration
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
            'nombre_artistico' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'nombre_real' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
                'null' => true,
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'genero' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'pais' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'descripcion' => [
                'type' => 'TEXT',
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
                'default' => 'activo',
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
        $this->forge->addKey('nombre_artistico');
        $this->forge->addKey('tipo');
        $this->forge->addKey('estado');
        $this->forge->createTable('artistas', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('artistas', true);
    }
}
