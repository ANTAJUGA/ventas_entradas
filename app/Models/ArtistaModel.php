<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtistaModel extends Model
{
    protected $table = 'artistas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'nombre_artistico',
        'nombre_real',
        'tipo',
        'genero',
        'pais',
        'descripcion',
        'imagen',
        'estado',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'nombre_artistico' => 'required|max_length[150]',
        'nombre_real' => 'permit_empty|max_length[180]',
        'tipo' => 'required|in_list[solista,banda,duo,orquesta,otro]',
        'genero' => 'permit_empty|max_length[80]',
        'pais' => 'permit_empty|max_length[100]',
        'descripcion' => 'permit_empty',
        'imagen' => 'permit_empty|max_length[255]',
        'estado' => 'required|in_list[activo,inactivo]',
    ];

    protected $validationMessages = [
        'nombre_artistico' => [
            'required' => 'El nombre artístico es obligatorio.',
            'max_length' => 'El nombre artístico no puede superar los 150 caracteres.',
        ],
        'tipo' => [
            'required' => 'Selecciona el tipo de artista.',
            'in_list' => 'El tipo de artista seleccionado no es válido.',
        ],
        'estado' => [
            'required' => 'Selecciona el estado del artista.',
            'in_list' => 'El estado seleccionado no es válido.',
        ],
    ];
}
