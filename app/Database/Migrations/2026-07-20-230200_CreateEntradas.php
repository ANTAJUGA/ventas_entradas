<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEntradas extends Migration
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
            'detalle_venta_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'qr_token_hash' => [
                'type' => 'CHAR',
                'constraint' => 64,
            ],
            'titular_nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
            ],
            'titular_email' => [
                'type' => 'VARCHAR',
                'constraint' => 190,
                'null' => true,
            ],
            'asiento' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
            ],
            'estado' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'vigente',
            ],
            'emitida_at' => [
                'type' => 'DATETIME',
            ],
            'usada_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'anulada_at' => [
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('codigo');
        $this->forge->addUniqueKey('qr_token_hash');
        $this->forge->addKey('detalle_venta_id');
        $this->forge->addKey('estado');
        $this->forge->addForeignKey('detalle_venta_id', 'detalle_ventas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('entradas', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('entradas', true);
    }
}
