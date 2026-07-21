<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDetalleVentas extends Migration
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
            'venta_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'tipo_entrada_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'cantidad' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'precio_unitario' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned' => true,
            ],
            'descuento_unitario' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned' => true,
                'default' => 0,
            ],
            'subtotal' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'unsigned' => true,
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
        $this->forge->addUniqueKey(['venta_id', 'tipo_entrada_id']);
        $this->forge->addKey('venta_id');
        $this->forge->addKey('tipo_entrada_id');
        $this->forge->addForeignKey('venta_id', 'ventas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tipo_entrada_id', 'tipos_entrada', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('detalle_ventas', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('detalle_ventas', true);
    }
}
