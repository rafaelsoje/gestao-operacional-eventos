<?php

namespace App\Models;

use CodeIgniter\Model;

class Visita extends Model
{
    // Registro operacional de visitas técnicas/comerciais no espaço de eventos.
    protected $table = 'visitas';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $protectFields = true;
    protected $allowedFields = [
        // Dados principais da visita e contexto do evento associado.
        'data_visita',
        'tipo_visita',
        'responsavel_exc',
        'cliente',
        'evento',
        'data_evento',
        'horario_inicio',
        'horario_termino',
        'publico_estimado',
        'objetivo_visita',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
