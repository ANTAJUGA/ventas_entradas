<?php

namespace App\Models;

use CodeIgniter\Model;

class TipoEntradaModel extends Model
{
    protected $table = 'tipos_entrada';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'funcion_id',
        'nombre',
        'descripcion',
        'precio',
        'cupo',
        'limite_por_compra',
        'activo',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'funcion_id' => 'required|is_natural_no_zero|is_not_unique[funciones.id]',
        'nombre' => 'required|max_length[80]',
        'descripcion' => 'permit_empty|max_length[255]',
        'precio' => 'required|decimal|greater_than_equal_to[0]',
        'cupo' => 'required|is_natural',
        'limite_por_compra' => 'required|is_natural_no_zero',
        'activo' => 'permit_empty|in_list[0,1]',
    ];
}
