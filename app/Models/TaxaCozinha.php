<?php

namespace App\Models;

use CodeIgniter\Model;

class TaxaCozinha extends Model
{
    protected $table = 'taxas_cozinha';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $protectFields = true;
    protected $allowedFields = [
        'operacional_id',
        'cliente',
        'responsavel_id',
        'valor_taxa',
        'embutida_contrato',
        'houve_negociacao',
        'detalhes_negociacao',
        'status_financeiro',
        'forma_pagamento',
        'valor_pago',
        'data_pagamento',
        'comprovante',
        'cobranca_realizada',
        'data_cobranca',
        'observacoes',
        'responsavel_pagamento',
        'prevista_contrato',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
