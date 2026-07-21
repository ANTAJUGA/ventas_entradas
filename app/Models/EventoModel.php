<?php

namespace App\Models;

use CodeIgniter\Model;

class EventoModel extends Model
{
    protected $table = 'eventos';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'organizador_id',
        'nombre',
        'slug',
        'descripcion',
        'categoria',
        'imagen',
        'estado',
        'publicado_at',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'organizador_id' => 'required|is_natural_no_zero|is_not_unique[usuarios.id]',
        'nombre' => 'required|max_length[150]',
        'slug' => 'required|max_length[170]|alpha_dash|is_unique[eventos.slug,id,{id}]',
        'descripcion' => 'permit_empty',
        'categoria' => 'permit_empty|max_length[80]',
        'imagen' => 'permit_empty|max_length[255]',
        'estado' => 'required|in_list[borrador,publicado,cancelado,finalizado]',
        'publicado_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
    ];
}
