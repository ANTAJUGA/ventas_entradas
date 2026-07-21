<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVentas extends Migration
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
            'codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
            ],
            'cliente_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'vendedor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'descuento_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'cliente_nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
            ],
            'cliente_email' => [
                'type' => 'VARCHAR',
                'constraint' => 190,
            ],
            'subtotal' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned' => true,
            ],
            'descuento_total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned' => true,
                'default' => 0,
            ],
            'total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned' => true,
            ],
            'estado' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'pendiente',
            ],
            'metodo_pago' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
            ],
            'referencia_pago' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'pagado_at' => [
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
        $this->forge->addUniqueKey('codigo');
        $this->forge->addKey('cliente_id');
        $this->forge->addKey('vendedor_id');
        $this->forge->addKey('descuento_id');
        $this->forge->addKey('estado');
        $this->forge->addForeignKey('cliente_id', 'usuarios', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('vendedor_id', 'usuarios', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('descuento_id', 'descuentos', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('ventas', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ventas', true);
    }
}
