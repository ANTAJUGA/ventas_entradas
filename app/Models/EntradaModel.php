<?php

namespace App\Models;

use CodeIgniter\Model;

class EntradaModel extends Model
{
    protected $table = 'entradas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'detalle_venta_id',
        'codigo',
        'qr_token_hash',
        'titular_nombre',
        'titular_email',
        'asiento',
        'estado',
        'emitida_at',
        'usada_at',
        'anulada_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'detalle_venta_id' => 'required|is_natural_no_zero|is_not_unique[detalle_ventas.id]',
        'codigo' => 'required|max_length[50]|alpha_numeric_punct|is_unique[entradas.codigo,id,{id}]',
        'qr_token_hash' => 'required|exact_length[64]|alpha_numeric|is_unique[entradas.qr_token_hash,id,{id}]',
        'titular_nombre' => 'permit_empty|max_length[200]',
        'titular_email' => 'permit_empty|max_length[190]|valid_email',
        'asiento' => 'permit_empty|max_length[30]',
        'estado' => 'required|in_list[vigente,usada,anulada]',
        'emitida_at' => 'required|valid_date[Y-m-d H:i:s]',
        'usada_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'anulada_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
    ];
}
