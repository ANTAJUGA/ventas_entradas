<?php

namespace App\Models;

use CodeIgniter\Model;

class ControlAccesoModel extends Model
{
    protected $table = 'control_accesos';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'entrada_id',
        'usuario_id',
        'resultado',
        'motivo',
        'punto_acceso',
        'direccion_ip',
        'registrado_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = '';

    protected $validationRules = [
        'entrada_id' => 'required|is_natural_no_zero|is_not_unique[entradas.id]',
        'usuario_id' => 'permit_empty|is_natural_no_zero|is_not_unique[usuarios.id]',
        'resultado' => 'required|in_list[aceptado,rechazado]',
        'motivo' => 'permit_empty|max_length[255]',
        'punto_acceso' => 'permit_empty|max_length[100]',
        'direccion_ip' => 'permit_empty|valid_ip|max_length[45]',
        'registrado_at' => 'required|valid_date[Y-m-d H:i:s]',
    ];
}
