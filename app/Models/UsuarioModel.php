<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'rol_id',
        'nombres',
        'apellidos',
        'email',
        'password_hash',
        'telefono',
        'estado',
        'ultimo_acceso',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'rol_id' => 'required|is_natural_no_zero|is_not_unique[roles.id]',
        'nombres' => 'required|max_length[100]',
        'apellidos' => 'required|max_length[100]',
        'email' => 'required|max_length[190]|valid_email|is_unique[usuarios.email,id,{id}]',
        'password_hash' => 'required|max_length[255]',
        'telefono' => 'permit_empty|max_length[25]',
        'estado' => 'required|in_list[activo,inactivo,bloqueado]',
        'ultimo_acceso' => 'permit_empty|valid_date[Y-m-d H:i:s]',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'El correo electrónico ya se encuentra registrado.',
        ],
    ];
}
