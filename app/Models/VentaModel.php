<?php

namespace App\Models;

use CodeIgniter\Model;

class VentaModel extends Model
{
    protected $table = 'ventas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'codigo',
        'cliente_id',
        'vendedor_id',
        'descuento_id',
        'cliente_nombre',
        'cliente_email',
        'subtotal',
        'descuento_total',
        'total',
        'estado',
        'metodo_pago',
        'referencia_pago',
        'pagado_at',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'codigo' => 'required|max_length[40]|alpha_numeric_punct|is_unique[ventas.codigo,id,{id}]',
        'cliente_id' => 'permit_empty|is_natural_no_zero|is_not_unique[usuarios.id]',
        'vendedor_id' => 'permit_empty|is_natural_no_zero|is_not_unique[usuarios.id]',
        'descuento_id' => 'permit_empty|is_natural_no_zero|is_not_unique[descuentos.id]',
        'cliente_nombre' => 'required|max_length[200]',
        'cliente_email' => 'required|max_length[190]|valid_email',
        'subtotal' => 'required|decimal|greater_than_equal_to[0]',
        'descuento_total' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'total' => 'required|decimal|greater_than_equal_to[0]',
        'estado' => 'required|in_list[pendiente,pagada,cancelada,reembolsada]',
        'metodo_pago' => 'permit_empty|max_length[30]',
        'referencia_pago' => 'permit_empty|max_length[100]',
        'pagado_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
    ];
}
