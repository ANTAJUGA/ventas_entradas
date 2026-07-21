<?php

namespace App\Models;

use CodeIgniter\Model;

class RolModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'nombre',
        'slug',
        'descripcion',
        'activo',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'nombre' => 'required|max_length[60]',
        'slug' => 'required|max_length[60]|alpha_dash|is_unique[roles.slug,id,{id}]',
        'descripcion' => 'permit_empty|max_length[255]',
        'activo' => 'permit_empty|in_list[0,1]',
    ];
}
