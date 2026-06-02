<?= $this->extend('main')?>;

<?php $this->section('content');?>
<?php helper('format'); ?>

<?php
$eventos = $eventos ?? [];
$responsaveis = $responsaveis ?? [];
$taxas = $taxas ?? [];
$tableReady = (bool)($tableReady ?? false);
$statusBadge = static function (string $status): string {
    return match ($status) {
        'pago' => 'success',
        'parcial' => 'warning',
        default => 'danger',
    };
};
$moneyValue = static function (mixed $value): string {
    return number_format((float)($value ?? 0), 2, ',', '.');
};
$checked = static function (mixed $actual, mixed $expected): string {
    return (string)$actual === (string)$expected ? 'checked' : '';
};
$selected = static function (mixed $actual, mixed $expected): string {
    return (string)$actual === (string)$expected ? 'selected' : '';
};
$formatDateInput = static function (mixed $date): string {
    return !empty($date) ? esc((string)$date) : '';
};
$eventoSituacao = static function (mixed $flag): string {
    return (int)$flag === 0 ? 'Planejado' : 'Realizado';
};
$eventoSituacaoBadge = static function (mixed $flag): string {
    return (int)$flag === 0 ? 'primary' : 'success';
};
$eventoOptionLabel = static function (object $evento) use ($eventoSituacao): string {
    return (string)$evento->nome . ' - ' . br_date((string)$evento->data) . ' [' . $eventoSituacao($evento->flag ?? 1) . ']';
};
$alertsFor = static function (object $taxa): array {
    $alertas = [];
    $status = (string)($taxa->status_financeiro ?? 'pendente');
    $cobrancaRealizada = (int)($taxa->cobranca_realizada ?? 0) === 1;
    $dataCobranca = trim((string)($taxa->data_cobranca ?? ''));

    if ($status === 'parcial') {
        $alertas[] = 'Pagamento parcial';
    } elseif ($status === 'pendente' && !$cobrancaRealizada) {
        $alertas[] = 'Cobrança não realizada';
    } elseif ($status === 'pendente' && $cobrancaRealizada) {
        $alertas[] = $dataCobranca !== ''
            ? 'Cobrado em ' . br_date($dataCobranca) . ' | pagamento pendente'
            : 'Cobrança realizada | pagamento pendente';
    }

    if ((int)($taxa->houve_negociacao ?? 0) === 1) {
        $alertas[] = 'Houve negociação';
    }

    return $alertas;
};
?>

<div class="page-shell mt-3 mb-5 taxa-cozinha-page">
    <?php if (session()->has('error')) : ?>
        <div class="flash text-center alert alert-danger text-danger"><?= session()->get('error') ?? ''; ?></div>
    <?php endif; ?>
    <?php if (session()->has('success')) : ?>
        <div class="flash text-center alert alert-success text-success"><?= session()->get('success') ?? ''; ?></div>
    <?php endif; ?>
    <?php if (!$tableReady) : ?>
        <div class="app-banner app-banner-warning mb-3">
            <div>
                <strong>Tabela não encontrada</strong>
                <p>A tabela da taxa de cozinha ainda não existe. Execute as migrations para liberar o salvamento.</p>
            </div>
        </div>
    <?php endif; ?>

    <style>
        .taxa-cozinha-page .section-box {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 8px;
            background: #fff;
            padding: 1rem;
            height: 100%;
        }
        .taxa-cozinha-page .section-box h5 {
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        .taxa-cozinha-page textarea {
            min-height: 86px;
        }
        .taxa-cozinha-page .form-check-inline {
            margin-right: 1.2rem;
        }
        .taxa-cozinha-page .actions-cell {
            min-width: 156px;
        }
    </style>

    <div class="page-hero">
        <div>
            <h2>Taxa de cozinha</h2>
            <p>Controle cobranças de eventos realizados e planejados, valores recebidos, pendências e comprovantes.</p>
        </div>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#taxaCreateModal">
            <i class="bi bi-plus-circle"></i> Cadastrar taxa
        </button>
    </div>

    <div class="app-banner app-banner-info mb-3">
        <div>
            <strong>Cobrança antecipada</strong>
            <p>Você pode lançar cobrança para evento planejado antes da realização. Use o status financeiro e a data da cobrança para acompanhar pendências.</p>
        </div>
    </div>

    <div class="content-card p-3">
        <div class="content-card-header">
            <div>
                <h5>Eventos com taxa cadastrada</h5>
                <small><?= count($taxas); ?> registro(s) encontrados</small>
            </div>
        </div>
        <?php if ($taxas): ?>
            <div class="table-responsive">
                <table id="taxaCozinhaTable" class="display table table-striped table-hover dt-stack align-middle app-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Evento</th>
                            <th>Situação</th>
                            <th>Cliente</th>
                            <th>Valor</th>
                            <th>Pago</th>
                            <th>Status</th>
                            <th>Responsável</th>
                            <th>Alertas</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($taxas as $taxa): ?>
                            <?php $alertas = $alertsFor($taxa); ?>
                            <tr>
                                <td data-label="Data"><?= !empty($taxa->evento_data) ? br_date((string)$taxa->evento_data) : '-'; ?></td>
                                <td data-label="Evento"><?= esc((string)($taxa->evento_nome ?? '-')); ?></td>
                                <td data-label="Situação">
                                    <span class="badge text-bg-<?= $eventoSituacaoBadge($taxa->evento_flag ?? 1); ?>">
                                        <?= esc($eventoSituacao($taxa->evento_flag ?? 1)); ?>
                                    </span>
                                </td>
                                <td data-label="Cliente"><?= esc((string)($taxa->cliente ?? '-')); ?></td>
                                <td data-label="Valor"><?= br_money($taxa->valor_taxa ?? 0); ?></td>
                                <td data-label="Pago"><?= br_money($taxa->valor_pago_efetivo ?? ($taxa->valor_pago ?? 0)); ?></td>
                                <td data-label="Status">
                                    <span class="badge text-bg-<?= $statusBadge((string)($taxa->status_financeiro ?? 'pendente')); ?>">
                                        <?= esc(ucfirst((string)($taxa->status_financeiro ?? 'pendente'))); ?>
                                    </span>
                                </td>
                                <td data-label="Responsável"><?= esc((string)($taxa->responsavel_nome ?? '-')); ?></td>
                                <td data-label="Alertas">
                                    <?= $alertas ? esc(implode(' | ', $alertas)) : '<span class="text-muted">Sem alertas</span>'; ?>
                                </td>
                                <td data-label="Ação" class="actions-cell action-cell">
                                    <button type="button" class="btn btn-info text-white btn-icon" data-bs-toggle="modal" data-bs-target="#taxaView<?= (int)$taxa->id; ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning text-white btn-icon" data-bs-toggle="modal" data-bs-target="#taxaEdit<?= (int)$taxa->id; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-icon" data-bs-toggle="modal" data-bs-target="#taxaDelete<?= (int)$taxa->id; ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="d-flex align-items-center gap-3">
                    <span class="empty-state-icon"><i class="bi bi-cash-coin"></i></span>
                    <div>
                        <h5>Nenhuma cobrança cadastrada</h5>
                        <p>Cadastre a primeira taxa de cozinha para acompanhar pagamentos e pendências.</p>
                    </div>
                </div>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#taxaCreateModal">
                    <i class="bi bi-plus-circle"></i> Cadastrar taxa
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="taxaCreateModal" tabindex="-1" aria-labelledby="taxaCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="taxaCreateModalLabel">Cadastrar taxa de cozinha</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-tabs">
                <span class="nav-link active"><i class="bi bi-calendar-event"></i> Evento</span>
                <span class="nav-link"><i class="bi bi-cash-coin"></i> Taxa</span>
                <span class="nav-link"><i class="bi bi-credit-card"></i> Pagamento</span>
                <span class="nav-link"><i class="bi bi-clipboard-check"></i> Controle</span>
            </div>
            <div class="modal-body">
                <form id="taxaCreateForm" method="post" action="<?= url_to('taxa.cozinha.store'); ?>" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <label class="form-label">Evento</label>
                        <select class="form-select" name="operacional_id" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($eventos as $evento): ?>
                                <option value="<?= (int)$evento->id; ?>"><?= esc($eventoOptionLabel($evento)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label">Cliente</label>
                        <input type="text" class="form-control" name="cliente" placeholder="Marina Silva">
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label">Responsável</label>
                        <select class="form-select" name="responsavel_id">
                            <option value="">Selecione...</option>
                            <?php foreach ($responsaveis as $responsavel): ?>
                                <option value="<?= (int)$responsavel->id; ?>"><?= esc((string)$responsavel->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?= view('taxaCozinhaFormFields', ['taxa' => null, 'prefix' => 'create']); ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="taxaCreateForm" class="btn btn-success"><i class="bi bi-save"></i> Cadastrar</button>
            </div>
        </div>
    </div>
</div>

<?php foreach ($taxas as $taxa): ?>
    <?php
        $modalId = (int)$taxa->id;
        $alertas = $alertsFor($taxa);
    ?>
    <div class="modal fade" id="taxaView<?= $modalId; ?>" tabindex="-1" aria-labelledby="taxaViewLabel<?= $modalId; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="taxaViewLabel<?= $modalId; ?>"><?= esc((string)($taxa->evento_nome ?? 'Taxa de cozinha')); ?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Cliente:</strong> <?= esc((string)($taxa->cliente ?? '-')); ?></div>
                        <div class="col-md-6"><strong>Data do evento:</strong> <?= !empty($taxa->evento_data) ? br_date((string)$taxa->evento_data) : '-'; ?></div>
                        <div class="col-md-6"><strong>Situação do evento:</strong> <?= esc($eventoSituacao($taxa->evento_flag ?? 1)); ?></div>
                        <div class="col-md-6"><strong>Valor da taxa:</strong> <?= br_money($taxa->valor_taxa ?? 0); ?></div>
                        <div class="col-md-6"><strong>Valor pago:</strong> <?= br_money($taxa->valor_pago ?? 0); ?></div>
                        <div class="col-md-6"><strong>Status:</strong> <?= esc(ucfirst((string)($taxa->status_financeiro ?? 'pendente'))); ?></div>
                        <div class="col-md-6"><strong>Forma de pagamento:</strong> <?= esc((string)($taxa->forma_pagamento ?? '-')); ?></div>
                        <div class="col-md-6"><strong>Responsável:</strong> <?= esc((string)($taxa->responsavel_nome ?? '-')); ?></div>
                        <div class="col-md-6"><strong>Responsável pelo pagamento:</strong> <?= esc((string)($taxa->responsavel_pagamento ?? '-')); ?></div>
                        <div class="col-md-6"><strong>Taxa embutida:</strong> <?= (int)$taxa->embutida_contrato === 1 ? 'Sim' : 'Não'; ?></div>
                        <div class="col-md-6"><strong>Prevista em contrato:</strong> <?= (int)$taxa->prevista_contrato === 1 ? 'Sim' : 'Não'; ?></div>
                        <div class="col-md-6"><strong>Houve negociação:</strong> <?= (int)$taxa->houve_negociacao === 1 ? 'Sim' : 'Não'; ?></div>
                        <div class="col-md-6"><strong>Cobrança realizada:</strong> <?= (int)$taxa->cobranca_realizada === 1 ? 'Sim' : 'Não'; ?></div>
                        <div class="col-12"><strong>Alertas:</strong> <?= $alertas ? esc(implode(' | ', $alertas)) : 'Sem alertas'; ?></div>
                        <div class="col-12"><strong>Detalhes da negociação:</strong><br><?= nl2br(esc((string)($taxa->detalhes_negociacao ?? '-'))); ?></div>
                        <div class="col-12"><strong>Observações:</strong><br><?= nl2br(esc((string)($taxa->observacoes ?? '-'))); ?></div>
                        <div class="col-12">
                            <strong>Comprovante:</strong>
                            <?php if (!empty($taxa->comprovante)): ?>
                                <a href="<?= url_to('taxa.cozinha.comprovante', $taxa->comprovante); ?>" class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="bi bi-paperclip"></i> Abrir arquivo
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="taxaEdit<?= $modalId; ?>" tabindex="-1" aria-labelledby="taxaEditLabel<?= $modalId; ?>" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="taxaEditLabel<?= $modalId; ?>">Alterar taxa de cozinha</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-tabs">
                    <span class="nav-link active"><i class="bi bi-calendar-event"></i> Evento</span>
                    <span class="nav-link"><i class="bi bi-cash-coin"></i> Taxa</span>
                    <span class="nav-link"><i class="bi bi-credit-card"></i> Pagamento</span>
                    <span class="nav-link"><i class="bi bi-clipboard-check"></i> Controle</span>
                </div>
                <div class="modal-body">
                    <form id="taxaEditForm<?= $modalId; ?>" method="post" action="<?= url_to('taxa.cozinha.store'); ?>" enctype="multipart/form-data">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="id" value="<?= $modalId; ?>">
                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <label class="form-label">Evento</label>
                                <select class="form-select" name="operacional_id" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($eventos as $evento): ?>
                                        <option value="<?= (int)$evento->id; ?>" <?= $selected($taxa->operacional_id, $evento->id); ?>>
                                            <?= esc($eventoOptionLabel($evento)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label">Cliente</label>
                                <input type="text" class="form-control" name="cliente" value="<?= esc((string)($taxa->cliente ?? '')); ?>">
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label">Responsável</label>
                                <select class="form-select" name="responsavel_id">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($responsaveis as $responsavel): ?>
                                        <option value="<?= (int)$responsavel->id; ?>" <?= $selected($taxa->responsavel_id, $responsavel->id); ?>>
                                            <?= esc((string)$responsavel->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="section-box">
                                    <h5><i class="bi bi-cash-coin"></i> Dados da taxa</h5>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Valor da taxa</label>
                                            <input type="text" class="form-control money" name="valor_taxa" value="<?= $moneyValue($taxa->valor_taxa ?? 0); ?>">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label d-block">Taxa embutida no contrato?</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="embutida_contrato" id="embutidaSim<?= $modalId; ?>" value="1" <?= $checked($taxa->embutida_contrato, 1); ?>>
                                                <label class="form-check-label" for="embutidaSim<?= $modalId; ?>">Sim</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="embutida_contrato" id="embutidaNao<?= $modalId; ?>" value="0" <?= $checked($taxa->embutida_contrato, 0); ?>>
                                                <label class="form-check-label" for="embutidaNao<?= $modalId; ?>">Não</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label d-block">Houve negociação?</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="houve_negociacao" id="negociacaoNao<?= $modalId; ?>" value="0" <?= $checked($taxa->houve_negociacao, 0); ?>>
                                                <label class="form-check-label" for="negociacaoNao<?= $modalId; ?>">Não</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="houve_negociacao" id="negociacaoSim<?= $modalId; ?>" value="1" <?= $checked($taxa->houve_negociacao, 1); ?>>
                                                <label class="form-check-label" for="negociacaoSim<?= $modalId; ?>">Sim</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Detalhes da negociação</label>
                                            <textarea class="form-control" name="detalhes_negociacao"><?= esc((string)($taxa->detalhes_negociacao ?? '')); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="section-box">
                                    <h5><i class="bi bi-exclamation-triangle"></i> Status financeiro</h5>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label d-block">Status</label>
                                            <?php foreach (['pendente' => 'Pendente', 'pago' => 'Pago', 'parcial' => 'Parcial'] as $value => $label): ?>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="status_financeiro" id="status<?= ucfirst($value) . $modalId; ?>" value="<?= $value; ?>" <?= $checked($taxa->status_financeiro, $value); ?>>
                                                    <label class="form-check-label" for="status<?= ucfirst($value) . $modalId; ?>"><?= $label; ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Forma de pagamento</label>
                                            <select class="form-select" name="forma_pagamento">
                                                <?php foreach (['PIX', 'Transferência', 'Boleto', 'Dinheiro', 'Cartão'] as $forma): ?>
                                                    <option value="<?= esc($forma); ?>" <?= $selected($taxa->forma_pagamento, $forma); ?>><?= esc($forma); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Valor pago</label>
                                            <input type="text" class="form-control money" name="valor_pago" value="<?= $moneyValue($taxa->valor_pago ?? 0); ?>">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Data pagamento</label>
                                            <input type="date" class="form-control" name="data_pagamento" value="<?= $formatDateInput($taxa->data_pagamento ?? null); ?>">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Comprovante</label>
                                            <input type="file" class="form-control" name="comprovante" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="section-box">
                                    <h5><i class="bi bi-clipboard-check"></i> Controle operacional</h5>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label d-block">Cobrança realizada?</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="cobranca_realizada" id="cobrancaSim<?= $modalId; ?>" value="1" <?= $checked($taxa->cobranca_realizada, 1); ?>>
                                                <label class="form-check-label" for="cobrancaSim<?= $modalId; ?>">Sim</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="cobranca_realizada" id="cobrancaNao<?= $modalId; ?>" value="0" <?= $checked($taxa->cobranca_realizada, 0); ?>>
                                                <label class="form-check-label" for="cobrancaNao<?= $modalId; ?>">Não</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <label class="form-label">Data da cobrança</label>
                                            <input type="date" class="form-control" name="data_cobranca" value="<?= $formatDateInput($taxa->data_cobranca ?? null); ?>">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Observações</label>
                                            <textarea class="form-control" name="observacoes"><?= esc((string)($taxa->observacoes ?? '')); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="section-box">
                                    <h5><i class="bi bi-sliders"></i> Controle avançado</h5>
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Responsável pelo pagamento</label>
                                            <select class="form-select" name="responsavel_pagamento">
                                                <?php foreach (['Cliente', 'Contratante', 'Produção', 'Fornecedor'] as $responsavelPagamento): ?>
                                                    <option value="<?= esc($responsavelPagamento); ?>" <?= $selected($taxa->responsavel_pagamento, $responsavelPagamento); ?>><?= esc($responsavelPagamento); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label d-block">Taxa prevista em contrato?</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="prevista_contrato" id="previstaSim<?= $modalId; ?>" value="1" <?= $checked($taxa->prevista_contrato, 1); ?>>
                                                <label class="form-check-label" for="previstaSim<?= $modalId; ?>">Sim</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="prevista_contrato" id="previstaNao<?= $modalId; ?>" value="0" <?= $checked($taxa->prevista_contrato, 0); ?>>
                                                <label class="form-check-label" for="previstaNao<?= $modalId; ?>">Não</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
	                        </div>
	                    </form>
	                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="taxaEditForm<?= $modalId; ?>" class="btn btn-success"><i class="bi bi-save"></i> Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="taxaDelete<?= $modalId; ?>" tabindex="-1" aria-labelledby="taxaDeleteLabel<?= $modalId; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="taxaDeleteLabel<?= $modalId; ?>">Excluir taxa de cozinha</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p>Quer mesmo excluir essa taxa?</p>
                    <strong><?= esc((string)($taxa->evento_nome ?? '-')); ?></strong>
                </div>
                <div class="modal-footer">
                    <form method="post" action="<?= url_to('taxa.cozinha.delete'); ?>">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= $modalId; ?>">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    $(document).ready(function () {
        $('input.money').mask('000.000.000.000.000,00', {reverse: true});

        jQuery.extend(jQuery.fn.dataTable.ext.type.order, {
            "date-dd-mm-yyyy-pre": function(d) {
                if (d === null || d === "" || d === "-") {
                    return 0;
                }
                var parts = d.split("/");
                return new Date(parts[2], parts[1] - 1, parts[0]).getTime();
            }
        });

        if ($.fn.DataTable && $('#taxaCozinhaTable').length) {
            $('#taxaCozinhaTable').DataTable({
                columnDefs: [
                    {type: "date-dd-mm-yyyy", targets: 0},
                    {orderable: false, targets: 9}
                ],
                order: [[0, "desc"]],
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                pageLength: 25,
                language: {
                    sEmptyTable: "Nenhum dado disponível na tabela",
                    sInfo: "Mostrando _START_ até _END_ de _TOTAL_ registros",
                    sInfoEmpty: "Mostrando 0 até 0 de 0 registros",
                    sInfoFiltered: "(filtrado de _MAX_ registros no total)",
                    sInfoPostFix: "",
                    sInfoThousands: ".",
                    sLengthMenu: "Mostrar _MENU_ registros",
                    sLoadingRecords: "Carregando...",
                    sProcessing: "Processando...",
                    sSearch: "Pesquisar:",
                    sZeroRecords: "Nenhum registro encontrado",
                    oPaginate: {
                        sFirst: "Primeiro",
                        sLast: "Último",
                        sNext: "Próximo",
                        sPrevious: "Anterior"
                    },
                    oAria: {
                        sSortAscending: ": Ordenar colunas de forma ascendente",
                        sSortDescending: ": Ordenar colunas de forma descendente"
                    }
                }
            });
        }
    });
</script>

<?php $this->endSection(); ?>
