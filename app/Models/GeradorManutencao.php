<?php

namespace App\Models;

use CodeIgniter\Model;

class GeradorManutencao extends Model
{
    protected $table = 'gerador_manutencoes';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $protectFields = true;
    protected $allowedFields = [
        // `gerador` recebe apenas G1/G2 (validado no controller).
        'gerador',
        'data_manutencao',
        'observacao',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
