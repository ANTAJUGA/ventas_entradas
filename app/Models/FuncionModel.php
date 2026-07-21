<?php

namespace App\Models;

use CodeIgniter\Model;

class FuncionModel extends Model
{
    protected $table = 'funciones';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'evento_id',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'venta_inicio',
        'venta_fin',
        'recinto',
        'direccion',
        'ciudad',
        'aforo_total',
        'estado',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'evento_id' => 'required|is_natural_no_zero|is_not_unique[eventos.id]',
        'nombre' => 'permit_empty|max_length[120]',
        'fecha_inicio' => 'required|valid_date[Y-m-d H:i:s]',
        'fecha_fin' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'venta_inicio' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'venta_fin' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'recinto' => 'required|max_length[150]',
        'direccion' => 'permit_empty|max_length[255]',
        'ciudad' => 'required|max_length[100]',
        'aforo_total' => 'required|is_natural_no_zero',
        'estado' => 'required|in_list[programada,en_curso,finalizada,cancelada]',
    ];
}
