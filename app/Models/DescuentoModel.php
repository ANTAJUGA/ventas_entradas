<?php

namespace App\Models;

use CodeIgniter\Model;

class DescuentoModel extends Model
{
    protected $table = 'descuentos';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'evento_id',
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'valor',
        'compra_minima',
        'fecha_inicio',
        'fecha_fin',
        'limite_usos',
        'usos_actuales',
        'activo',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'evento_id' => 'permit_empty|is_natural_no_zero|is_not_unique[eventos.id]',
        'codigo' => 'required|max_length[50]|alpha_numeric_punct|is_unique[descuentos.codigo,id,{id}]',
        'nombre' => 'required|max_length[100]',
        'descripcion' => 'permit_empty|max_length[255]',
        'tipo' => 'required|in_list[porcentaje,fijo]',
        'valor' => 'required|decimal|greater_than[0]',
        'compra_minima' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'fecha_inicio' => 'required|valid_date[Y-m-d H:i:s]',
        'fecha_fin' => 'required|valid_date[Y-m-d H:i:s]',
        'limite_usos' => 'permit_empty|is_natural_no_zero',
        'usos_actuales' => 'permit_empty|is_natural',
        'activo' => 'permit_empty|in_list[0,1]',
    ];
}
