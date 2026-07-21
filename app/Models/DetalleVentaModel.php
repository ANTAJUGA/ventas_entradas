<?php

namespace App\Models;

use CodeIgniter\Model;

class DetalleVentaModel extends Model
{
    protected $table = 'detalle_ventas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'venta_id',
        'tipo_entrada_id',
        'cantidad',
        'precio_unitario',
        'descuento_unitario',
        'subtotal',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'venta_id' => 'required|is_natural_no_zero|is_not_unique[ventas.id]',
        'tipo_entrada_id' => 'required|is_natural_no_zero|is_not_unique[tipos_entrada.id]',
        'cantidad' => 'required|is_natural_no_zero',
        'precio_unitario' => 'required|decimal|greater_than_equal_to[0]',
        'descuento_unitario' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'subtotal' => 'required|decimal|greater_than_equal_to[0]',
    ];
}
