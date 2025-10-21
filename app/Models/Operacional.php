<?php

namespace App\Models;

use CodeIgniter\Model;

class Operacional extends Model
{
    protected $table            = 'operacional';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'nome',
        'data',
        'tipo',
        'hora_inicio',
        'hora_fim',
        'hora_extra',
        'temp_inicio',
        'temp_fim',
        'qtd_fornecedor',
        'monitor_sonoro',
        'liga_g1',
        'desliga_g1',
        'tempo_g1',
        'liga_g2',
        'desliga_g2',
        'tempo_g2',
        'premontagem',
        'cache_premontagem',
        'valor_premontagem',
        'operacional',
        'valor_operacional',
        'super_op',
        'total_operacional',
        'seguranca',
        'bombeiro',
        'limpeza',
        'taxa_cozinha',
        'publico_final',
        'publico_tribuna',
        'publico_saida',
        'valor_diesel',
        'valor_inicio',
        'diesel_inicio',
        'diesel_fim',
        'diesel_litros',
        'diesel_total',
        'cedae_valor',
        'cedae_inicio',
        'cedae_fim',
        'cedae_consumo',
        'cedae_total',
        'light_valor',
        'light_inicio',
        'light_fim',
        'light_consumo',
        'light_total',
        'total_consumo',
        'descricao',
        'flag',
        'created_at',
        'updated_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
