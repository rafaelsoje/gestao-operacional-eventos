<?= $this->extend('main')?>;

<?php

 $this->section('content');?>
<?php helper('format'); ?>
<?php
$chartsJsV = is_file(FCPATH . 'assets/build/charts.js') ? filemtime(FCPATH . 'assets/build/charts.js') : time();
$rows = $data ?? [];
$hasRows = !empty($rows);
$topEventsRows = json_decode($topEvents ?? '[]', true);
$hasTopEvents = is_array($topEventsRows) && count($topEventsRows) > 1;
$generatorUsage = $generator_usage ?? [
    'g1' => ['total_hhmm' => '00:00', 'progress_percent' => 0, 'is_limit_reached' => false, 'is_warning' => false, 'last_maintenance_date' => null, 'remaining_hhmm' => '200:00'],
    'g2' => ['total_hhmm' => '00:00', 'progress_percent' => 0, 'is_limit_reached' => false, 'is_warning' => false, 'last_maintenance_date' => null, 'remaining_hhmm' => '200:00'],
    'has_alert' => false,
    'has_warning' => false,
];
$trendCards = $trend_cards ?? [];
$trendFor = static function (string $key) use ($trendCards): array {
    return $trendCards[$key] ?? ['text' => '-', 'class' => 'text-muted'];
};
$cedaeAlert = $cedae_alert ?? [
    'limit' => 6.0,
    'events' => [],
    'count' => 0,
    'is_alert' => false,
];
$pocoAlert = $poco_alert ?? [
    'limit' => 6.0,
    'events' => [],
    'count' => 0,
    'is_alert' => false,
];
$taxaCozinhaSummary = $taxa_cozinha_summary ?? [
    'previsto' => 0,
    'recebido' => 0,
    'pendente' => 0,
    'alertas' => 0,
];
$selectedTipos = $tipo ?? ['social', 'corporativo', 'entretenimento'];
$isTipoChecked = static function (string $value) use ($selectedTipos): bool {
    return in_array($value, $selectedTipos, true);
};
// Exibe somente horas nas telas de uso de gerador (ex.: 61:50 -> 61).
$hourOnly = static function (?string $hhmm): string {
    $value = trim((string)$hhmm);
    if ($value === '') {
        return '0';
    }
    if (strpos($value, ':') !== false) {
        [$hours] = explode(':', $value, 2);
        $hours = preg_replace('/\D+/', '', (string)$hours);
        return $hours !== '' ? (string)((int)$hours) : '0';
    }
    $digits = preg_replace('/\D+/', '', $value);
    return $digits !== '' ? (string)((int)$digits) : '0';
};
?>

<script src="<?= base_url('assets/build/charts.js?v=' . $chartsJsV) ?>"></script>

<style>
    .main {
        background: var(--app-bg);
    }

    .dashboard-shell {
        padding: 0 1rem 1.5rem;
    }

    .dashboard-hero {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.1rem 1.25rem;
        margin: 0 0 1rem;
        border: 1px solid var(--app-border);
        border-radius: 8px;
        background: linear-gradient(135deg, var(--app-surface) 0%, var(--app-surface-soft) 100%);
        box-shadow: var(--app-shadow);
    }

    .dashboard-hero h2 {
        margin: 0;
        color: var(--app-heading);
        font-size: 1.45rem;
        font-weight: 800;
        letter-spacing: 0;
    }

    .dashboard-hero p {
        margin: 0.25rem 0 0;
        color: var(--app-muted);
    }

    .dashboard-period {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.6rem;
        border-radius: 999px;
        background: var(--app-accent-soft);
        color: var(--app-accent);
        font-size: 0.9rem;
        font-weight: 700;
    }

    .dashboard-filter-wrap {
        padding: 0.85rem 1rem !important;
        margin: 0 0 1rem !important;
        border: 1px solid var(--app-border);
        border-radius: 8px;
        background: var(--app-surface);
        box-shadow: var(--app-shadow);
    }

    .dashboard-filter-form .form-control {
        min-height: 42px;
        border-color: var(--app-input-border);
        border-radius: 7px;
    }

    .dashboard-filter-form .form-check {
        margin-bottom: 0;
    }

    .dashboard-filter-form .form-check-label {
        color: var(--app-table-head);
        font-weight: 600;
    }

    .dashboard-filter-form .btn {
        min-height: 42px;
        border-radius: 7px;
        font-weight: 700;
    }

    .dashboard-modern-alerts {
        padding: 0 !important;
        text-align: left !important;
    }

    .dashboard-modern-alerts .alert {
        border: 0;
        border-left: 4px solid currentColor;
        border-radius: 8px;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
    }

    .dashboard-modern-alerts .alert-danger {
        background: color-mix(in srgb, var(--app-danger) 12%, var(--app-surface));
        color: #8a1f2d;
    }

    .dashboard-modern-alerts .alert-warning {
        background: color-mix(in srgb, var(--app-warning) 14%, var(--app-surface));
        color: #875a08;
    }

    .stat-card {
        position: relative;
        overflow: hidden;
        border: 1px solid var(--app-border);
        border-radius: 8px !important;
        box-shadow: var(--app-shadow) !important;
        transition: transform 160ms ease, box-shadow 160ms ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--app-shadow-hover) !important;
    }

    .kpi-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: #3197da;
    }

    .kpi-col:nth-child(3n + 2) .kpi-card::before {
        background: #1f9d67;
    }

    .kpi-col:nth-child(3n) .kpi-card::before {
        background: #f59f00;
    }

    .kpi-card .text-muted:first-child {
        color: var(--app-muted) !important;
        font-weight: 700;
    }

    .dashboard-chart-grid {
        padding: 0 1rem 1rem;
    }

    .dashboard-chart-grid .chart {
        margin-left: 0;
        margin-right: 0;
    }

    .dashboard-table-card {
        margin: 1rem;
        border: 1px solid var(--app-border);
        border-radius: 8px;
        box-shadow: var(--app-shadow) !important;
    }

    .dashboard-table-card .table {
        margin-bottom: 0;
    }

    .dashboard-table-card thead th {
        border-bottom: 1px solid var(--app-input-border);
        color: var(--app-table-head);
        font-size: 0.88rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .dashboard-table-card tbody td {
        color: var(--app-table-text);
        vertical-align: middle;
    }

    .dashboard-table-card tbody tr:hover {
        background: var(--app-surface-soft);
    }

    .dashboard-actions-cell .btn {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 7px;
    }

    @media (max-width: 768px) {
        .dashboard-shell {
            padding: 0 0.75rem 1rem;
        }

        .dashboard-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .dashboard-chart-grid {
            padding: 0 0.75rem 0.75rem;
        }

        .dashboard-table-card {
            margin: 0.75rem;
        }
    }
</style>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger mt-3 mb-0"><?= esc((string)session()->get('error')); ?></div>
<?php endif; ?>
<?php if (session()->has('success')): ?>
    <div class="alert alert-success mt-3 mb-0"><?= esc((string)session()->get('success')); ?></div>
<?php endif; ?>

<?php if($hasRows || (float)($sum->total_consumo ?? 0) > 0):?>

<?php if($hasRows):?>
<div class="dashboard-shell">
<div class="dashboard-hero">
    <div>
        <h2>Dashboard Operacional</h2>
        <?php if(isset($search)):?>
            <?php
                $inicio = br_date($search['data_inicial']);
                $fim = br_date($search['data_final']);
            ?>
            <p>Resultados do período de <?= esc($inicio . ' a ' . $fim); ?></p>
        <?php elseif(isset($get)):?>
            <p>Planejamento operacional do mês selecionado.</p>
        <?php else:?>
            <p>Resumo dos lançamentos operacionais mais recentes.</p>
        <?php endif;?>
    </div>
    <div class="dashboard-period">
        <i class="bi bi-calendar3"></i>
        <?php if(isset($search)):?>
            <?= esc($inicio . ' a ' . $fim); ?>
        <?php else:?>
            <?= esc((string)$mes); ?>
        <?php endif;?>
    </div>
</div>

<div class="d-flex align-items-center justify-content-end dashboard-filter-wrap">

<!-- <nav class="" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url();?>">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Library</li>
        </ol>
    </nav> -->
    <form class="row g-2 dashboard-filter-form w-100 justify-content-end align-items-center" method="get" action="<?= url_to('search.operacional.get');?>">
        <div class="col-12 col-md-3 col-lg-2">
            <input type="date" id="data_inicial" name="data_inicial" class="form-control"  required>
        </div>
        <div class="col-12 col-md-3 col-lg-2">
            <input type="date" id="data_final" name="data_final" class="form-control" required>
        </div>
        <div class="col-12 col-md d-flex align-items-center flex-wrap gap-2">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="inlineCheckbox1" name="tipos[]" value="social" <?= $isTipoChecked('social') ? 'checked' : ''; ?>>
                <label class="form-check-label" for="inlineCheckbox1">Social</label>
                </div>
                <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="inlineCheckbox2" name="tipos[]" value="corporativo" <?= $isTipoChecked('corporativo') ? 'checked' : ''; ?>>
                <label class="form-check-label" for="inlineCheckbox2">Corporativo</label>
                </div>
                <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="inlineCheckbox3" name="tipos[]" value="entretenimento" <?= $isTipoChecked('entretenimento') ? 'checked' : ''; ?>>
                <label class="form-check-label" for="inlineCheckbox3">Entretenimento</label>
            </div>
        </div>
        <div class="col-12 col-md-auto d-grid">
            <button type="submit" class="btn btn-success" onclick="return validarDatas(this.form)">Pesquisar</button>
        </div>
    </form>
</div>

<?php if(isset($search)):?>
    <div class="mb-3">
        <h6 class="mb-0 text-muted">Exibindo resultados do período de <span class="text-primary"><?= $inicio . " a " . $fim;?></span>
        <?php foreach($tipo as $item){
            echo " " . $item . " ";
        }
        ?></h6>
    </div>
<?php elseif(isset($get)):?>
<h6 class="mb-3 text-muted">Exibindo planejamento para o mês de <span class="text-primary"><?= $mes; ?></span></h6>
<?php else:?>
<h6 class="mb-3 text-muted">Exibindo lançamentos de <span class="text-primary"><?= $mes; ?></span></h6>
<?php endif;?>



<div class="dashboard-modern-alerts">
    <?php if (!empty($generatorUsage['has_alert'])): ?>
        <div class="app-banner app-banner-danger alert-dismissible fade show mb-3" role="alert">
            <div>
                <strong>Manutenção vencida</strong>
                <p>Um ou mais geradores já atingiram 200 horas de uso e precisam de manutenção.</p>
            </div>
            <a href="<?= base_url('operacional/geradores'); ?>" class="btn btn-sm btn-danger">Acessar geradores</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($generatorUsage['has_warning'])): ?>
        <div class="app-banner app-banner-warning alert-dismissible fade show mb-3" role="alert">
            <div>
                <strong>Gerador próximo da manutenção</strong>
                <p>Há gerador acima de 180 horas de uso. Programe a manutenção antes do limite.</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($cedaeAlert['is_alert'])): ?>
        <div class="app-banner app-banner-danger alert-dismissible fade show mb-3" role="alert">
            <?php
                // Lista de eventos com link direto para edição quando houver ID disponível.
                $cedaeEvents = is_array($cedaeAlert['events'] ?? null) ? $cedaeAlert['events'] : [];
                $cedaeLabels = [];
                foreach ($cedaeEvents as $evt) {
                    if (is_array($evt)) {
                        $id = isset($evt['id']) ? (int)$evt['id'] : 0;
                        $name = trim((string)($evt['name'] ?? ''));
                        $consumo = (float)($evt['consumo'] ?? 0);
                        if ($name !== '') {
                            $label = esc($name) . ' (' . br_number($consumo, 0) . ' m³)';
                            if ($id > 0) {
                                $label = '<a href="' . esc(base_url('/update/operacional/' . $id)) . '" class="alert-link">' . $label . '</a>';
                            }
                            $cedaeLabels[] = $label;
                        }
                        continue;
                    }
                    $name = trim((string)$evt);
                    if ($name !== '') {
                        $cedaeLabels[] = esc($name);
                    }
                }
                $cedaeEventList = implode(', ', $cedaeLabels);
            ?>
            <div>
                <strong>Atenção CEDAE</strong>
                <p>Variação de consumo acima de <?= br_number((float)$cedaeAlert['limit'], 0); ?> m³.
                <?= $cedaeEventList !== '' ? (' ' . $cedaeEventList . '.') : ''; ?>
                Possível vazamento ou consumo anormal de água.</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($pocoAlert['is_alert'])): ?>
        <div class="app-banner app-banner-danger alert-dismissible fade show mb-3" role="alert">
            <?php
                // Mesmo padrão do alerta CEDAE, mas usando consumo da água do poço.
                $pocoEvents = is_array($pocoAlert['events'] ?? null) ? $pocoAlert['events'] : [];
                $pocoLabels = [];
                foreach ($pocoEvents as $evt) {
                    if (is_array($evt)) {
                        $id = isset($evt['id']) ? (int)$evt['id'] : 0;
                        $name = trim((string)($evt['name'] ?? ''));
                        $consumo = (float)($evt['consumo'] ?? 0);
                        if ($name !== '') {
                            $label = esc($name) . ' (' . br_number($consumo, 0) . ' m³)';
                            if ($id > 0) {
                                $label = '<a href="' . esc(base_url('/update/operacional/' . $id)) . '" class="alert-link">' . $label . '</a>';
                            }
                            $pocoLabels[] = $label;
                        }
                        continue;
                    }
                    $name = trim((string)$evt);
                    if ($name !== '') {
                        $pocoLabels[] = esc($name);
                    }
                }
                $pocoEventList = implode(', ', $pocoLabels);
            ?>
            <div>
                <strong>Atenção água do poço</strong>
                <p>Variação de consumo acima de <?= br_number((float)$pocoAlert['limit'], 0); ?> m³.
                <?= $pocoEventList !== '' ? (' ' . $pocoEventList . '.') : ''; ?>
                Possível vazamento ou consumo anormal de água.</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
    <style>
        .generator-warning-strong {
            color: #d89a1a !important;
        }
        .progress-bar.generator-warning-strong {
            background-color: #d89a1a !important;
        }
        .kpi-card {
            padding: 0.85rem 0.95rem !important;
            min-height: 158px;
        }
        .kpi-card .fs-2 {
            font-size: 1.95rem !important;
            line-height: 1.15;
        }
        .kpi-card .fs-6 {
            font-size: 1.05rem !important;
        }
        .kpi-card .small {
            line-height: 1.2;
        }
        @media (min-width: 1200px) {
            .kpi-col {
                flex: 0 0 20%;
                max-width: 20%;
            }
        }
        table.dt-stack td.dashboard-actions-cell {
            padding-left: 0 !important;
            text-align: center !important;
            justify-content: center !important;
            gap: 0.5rem;
        }
        table.dt-stack td.dashboard-actions-cell::before {
            position: static !important;
            display: block;
            width: 100%;
            text-align: left;
            margin-bottom: 0.4rem;
        }
    </style>
    <div class="row g-3 align-items-stretch">
        <?php $kwhTotal = (float)($sum->kwh_total ?? 0); ?>
        <div class="col-12 col-sm-6 col-lg-4 kpi-col order-2 order-lg-1">
            <div class="stat-card bg-white rounded shadow-lg h-100 text-start p-3 kpi-card">
                <div class="small text-muted">Custo diesel</div>
                <div class="small text-muted"><?= (int)($sum->diesel_litros ?? 0); ?> L</div>
                <div class="fw-bold fs-2 money text-dark mt-2"><?= br_money($sum->diesel_total);?></div>
                <?php $t = $trendFor('diesel_total'); ?>
                <div class="small <?= esc($t['class']); ?>">vs período anterior: <?= esc($t['text']); ?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 kpi-col order-2 order-lg-1">
            <div class="stat-card bg-white rounded shadow-lg h-100 text-start p-3 kpi-card">
                <div class="small text-muted">Consumo gerador</div>
                <div class="small text-muted">kWh</div>
                <div class="fw-bold fs-2 text-dark mt-2"><?= br_number($kwhTotal, 0); ?> <span class="fs-6">kWh</span></div>
                <?php $t = $trendFor('kwh_total'); ?>
                <div class="small <?= esc($t['class']); ?>">vs período anterior: <?= esc($t['text']); ?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 kpi-col order-2 order-lg-1">
            <div class="stat-card bg-white rounded shadow-lg h-100 text-start p-3 kpi-card">
                <div class="small text-muted">Água e esgoto</div>
                <div class="small text-muted"><?= br_number((float)($sum->cedae_consumo ?? 0), 0); ?> m³</div>
                <div class="fw-bold fs-2 money text-dark mt-2"><?= br_money($sum->cedae_total);?></div>
                <?php $t = $trendFor('cedae_total'); ?>
                <div class="small <?= esc($t['class']); ?>">vs período anterior: <?= esc($t['text']); ?></div>
            </div>
        </div>
         <div class="col-12 col-sm-6 col-lg-4 kpi-col order-2 order-lg-1">
            <div class="stat-card bg-white rounded shadow-lg h-100 text-start p-3 kpi-card">
                <div class="small text-muted">Consumo água CEDAE</div>                
                <div class="fw-bold fs-2 text-dark mt-2"><?= br_number((float)($sum->cedae_consumo ?? 0), 0); ?> <span class="fs-6">m³</span></div>
                <?php $t = $trendFor('cedae_consumo'); ?>
                <div class="small <?= esc($t['class']); ?>">vs período anterior: <?= esc($t['text']); ?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 kpi-col order-2 order-lg-1">
            <div class="stat-card bg-white rounded shadow-lg h-100 text-start p-3 kpi-card">
                <div class="small text-muted">Custo light</div>
                <div class="small text-muted"><?= br_number((float)($sum->light_consumo ?? 0), 0); ?> kWh</div>
                <div class="fw-bold fs-2 money text-dark mt-2"><?= br_money($sum->light_total);?></div>
                <?php $t = $trendFor('light_total'); ?>
                <div class="small <?= esc($t['class']); ?>">vs período anterior: <?= esc($t['text']); ?></div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4 kpi-col order-2 order-lg-1">
            <div class="stat-card bg-white rounded shadow-lg h-100 text-start p-3 kpi-card">
                <div class="small text-muted">Consumo água poço</div>                
                <div class="fw-bold fs-2 text-dark mt-2"><?= br_number((float)($sum->poco_consumo ?? 0), 0); ?> <span class="fs-6">m³</span></div>
                <?php $t = $trendFor('poco_consumo'); ?>
                <div class="small <?= esc($t['class']); ?>">vs período anterior: <?= esc($t['text']); ?></div>
            </div>
        </div>       
        <div class="col-12 col-sm-6 col-lg-4 kpi-col order-2 order-lg-1">
            <div class="stat-card bg-white rounded shadow-lg h-100 text-start p-3 kpi-card">
                <div class="small text-muted">Custo pré-montagem</div>
                <div class="small text-muted">R$</div>
                <div class="fw-bold fs-2 money text-dark mt-2"><?= br_money($sum->valor_premontagem);?></div>
                <?php $t = $trendFor('valor_premontagem'); ?>
                <div class="small <?= esc($t['class']); ?>">vs período anterior: <?= esc($t['text']); ?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 kpi-col order-2 order-lg-1">
            <div class="stat-card bg-white rounded shadow-lg h-100 text-start p-3 kpi-card">
                <div class="small text-muted">Custo operacional evento</div>
                <div class="small text-muted">R$</div>
                <div class="fw-bold fs-2 money text-dark mt-2"><?= br_money($sum->super_op);?></div>
                <?php $t = $trendFor('super_op'); ?>
                <div class="small <?= esc($t['class']); ?>">vs período anterior: <?= esc($t['text']); ?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 kpi-col order-2 order-lg-1">
            <div class="stat-card bg-white rounded shadow-lg h-100 text-start p-3 kpi-card">
                <div class="small text-muted">Custo médio por evento</div>
                <div class="small text-muted">R$</div>
                <div class="fw-bold fs-2 money text-dark mt-2"><?= br_money($avg_event_cost ?? 0);?></div>
                <?php $t = $trendFor('avg_event_cost'); ?>
                <div class="small <?= esc($t['class']); ?>">vs período anterior: <?= esc($t['text']); ?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 kpi-col order-2 order-lg-1">
            <div class="stat-card bg-white rounded shadow-lg h-100 text-start p-3 kpi-card">
                <div class="small text-muted">Custo total no período</div>
                <div class="small text-muted">R$</div>
                <div class="fw-bold fs-2 money text-dark mt-2"><?= br_money($sum->total_consumo);?></div>
                <?php $t = $trendFor('total_consumo'); ?>
                <div class="small <?= esc($t['class']); ?>">vs período anterior: <?= esc($t['text']); ?></div>
            </div>
        </div>
    </div>

    <div class="row g-3 align-items-stretch mt-1">
        <div class="col-12 col-lg-4">
            <a href="<?= base_url('operacional/taxa-cozinha'); ?>" class="text-decoration-none">
                <div class="stat-card bg-white rounded shadow-lg h-100 text-start p-3 kpi-card">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="small text-muted">Taxa de cozinha</div>
                            <div class="small text-muted">Recebido no período</div>
                        </div>
                        <?php if ((int)($taxaCozinhaSummary['alertas'] ?? 0) > 0): ?>
                            <span class="badge bg-danger"><?= (int)$taxaCozinhaSummary['alertas']; ?> alerta(s)</span>
                        <?php endif; ?>
                    </div>
                    <div class="fw-bold fs-2 money text-success mt-2"><?= br_money($taxaCozinhaSummary['recebido'] ?? 0); ?></div>
                    <div class="small text-muted">
                        Previsto: <?= br_money($taxaCozinhaSummary['previsto'] ?? 0); ?>
                    </div>
                    <div class="small <?= ((float)($taxaCozinhaSummary['pendente'] ?? 0) > 0) ? 'text-danger' : 'text-success'; ?>">
                        Pendente: <?= br_money($taxaCozinhaSummary['pendente'] ?? 0); ?>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-lg-4">
            <?php $g1 = $generatorUsage['g1']; ?>
            <?php
                $g1Progress = (float)($g1['progress_percent'] ?? 0);
                $g1Tone = !empty($g1['is_limit_reached']) ? 'danger' : (!empty($g1['is_warning']) ? 'warning' : 'success');
                $g1ToneClass = $g1Tone === 'warning' ? 'generator-warning-strong' : 'text-' . $g1Tone;
                $g1BarClass = $g1Tone === 'warning' ? 'generator-warning-strong' : 'bg-' . $g1Tone;
            ?>
            <div class="stat-card bg-white rounded shadow-lg text-start p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-semibold">Uso Gerador G1</div>
                    <?php if (!empty($g1['is_limit_reached'])): ?>
                        <span class="badge bg-danger">Manutenção vencida</span>
                    <?php elseif (!empty($g1['is_warning'])): ?>
                        <span class="badge bg-warning text-dark">Próximo da manutenção</span>
                    <?php endif; ?>
                </div>
                <div class="fs-5 fw-bold <?= $g1ToneClass; ?>">
                    <?= esc($hourOnly((string)($g1['total_hhmm'] ?? '00:00'))); ?> / 200
                </div>
                <div class="progress mt-2" style="height: 8px;">
                    <div class="progress-bar <?= $g1BarClass; ?>" role="progressbar" style="width: <?= $g1Progress; ?>%"></div>
                </div>
                <small class="text-muted d-block mt-2">
                    Horas faltantes: <?= esc($hourOnly((string)($g1['remaining_hhmm'] ?? '00:00'))); ?>
                </small>
                <small class="text-muted d-block mt-2">
                    Última manutenção:
                    <span class="<?= !empty($g1['last_maintenance_date']) ? '' : 'text-warning'; ?>">
                        <?= !empty($g1['last_maintenance_date']) ? br_date($g1['last_maintenance_date']) : 'não registrada'; ?>
                    </span>
                </small>
                <small class="d-block <?= $g1ToneClass; ?>">
                    Projeção manutenção: <?= isset($g1['days_to_maintenance']) ? ((int)$g1['days_to_maintenance'] . ' dias') : 'sem dados recentes'; ?>
                </small>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <?php $g2 = $generatorUsage['g2']; ?>
            <?php
                $g2Progress = (float)($g2['progress_percent'] ?? 0);
                $g2Tone = !empty($g2['is_limit_reached']) ? 'danger' : (!empty($g2['is_warning']) ? 'warning' : 'success');
                $g2ToneClass = $g2Tone === 'warning' ? 'generator-warning-strong' : 'text-' . $g2Tone;
                $g2BarClass = $g2Tone === 'warning' ? 'generator-warning-strong' : 'bg-' . $g2Tone;
            ?>
            <div class="stat-card bg-white rounded shadow-lg text-start p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-semibold">Uso Gerador G2</div>
                    <?php if (!empty($g2['is_limit_reached'])): ?>
                        <span class="badge bg-danger">Manutenção vencida</span>
                    <?php elseif (!empty($g2['is_warning'])): ?>
                        <span class="badge bg-warning text-dark">Próximo da manutenção</span>
                    <?php endif; ?>
                </div>
                <div class="fs-5 fw-bold <?= $g2ToneClass; ?>">
                    <?= esc($hourOnly((string)($g2['total_hhmm'] ?? '00:00'))); ?> / 200
                </div>
                <div class="progress mt-2" style="height: 8px;">
                    <div class="progress-bar <?= $g2BarClass; ?>" role="progressbar" style="width: <?= $g2Progress; ?>%"></div>
                </div>
                <small class="text-muted d-block mt-2">
                    Horas faltantes: <?= esc($hourOnly((string)($g2['remaining_hhmm'] ?? '00:00'))); ?>
                </small>
                <small class="text-muted d-block mt-2">
                    Última manutenção:
                    <span class="<?= !empty($g2['last_maintenance_date']) ? '' : 'text-warning'; ?>">
                        <?= !empty($g2['last_maintenance_date']) ? br_date($g2['last_maintenance_date']) : 'não registrada'; ?>
                    </span>
                </small>
                <small class="d-block <?= $g2ToneClass; ?>">
                    Projeção manutenção: <?= isset($g2['days_to_maintenance']) ? ((int)$g2['days_to_maintenance'] . ' dias') : 'sem dados recentes'; ?>
                </small>
            </div>
        </div>
    </div>

</div>
</div>

<div class="dashboard-chart-grid">
    <h5 class="chart-section-title">Distribuição e custos</h5>
    <div class="chart row g-3 mt-2 mb-2">
        <div class="col-12 col-md-6">
            <div class="chart-box" id="donut_chart"></div>
        </div>
        <div class="col-12 col-md-6">
            <div class="chart-box" id="column_chart"></div>
        </div>
    </div>

    <h5 class="chart-section-title">Indicadores operacionais</h5>
    <div class="chart row g-3 mt-2 mb-2">
        <div class="col-12 col-lg-4">
            <div class="chart-box" id="column_chart2"></div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="chart-box" id="public_category_chart"></div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="chart-box" id="taxa_cozinha_status_chart"></div>
        </div>
    </div>

    <h5 class="chart-section-title">Ranking e manutenção</h5>
    <div class="chart row g-3 mt-2 mb-2">
        <div class="col-12 col-md-6">
            <?php if ($hasTopEvents): ?>
                <div class="chart-box" id="top_events_chart"></div>
            <?php else: ?>
                <div class="chart-box d-flex align-items-center justify-content-center">
                    <div class="empty-state w-100">
                        <div class="d-flex align-items-center gap-3">
                            <span class="empty-state-icon"><i class="bi bi-bar-chart"></i></span>
                            <div>
                                <h5>Ranking indisponível</h5>
                                <p>Ainda não há eventos suficientes para montar o ranking.</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-12 col-md-6">
            <div class="chart-box" id="line_generator_chart"></div>
        </div>
    </div>

    <h5 class="chart-section-title">Custo anual</h5>
    <div class="chart row g-3 mt-2 mb-2">
        <div class="col-12">
            <div class="chart-box" id="line_chart"></div>
        </div>
    </div>
</div>

<!-- <div class="chart row d-flex border m-1">
    <canvas class="col-12 col-md-6 col-lg-3 chart-container w-50 h-25" id="meuGrafico"></canvas>
    <canvas class="col-12 col-md-6 col-lg-3 chart-container w-50 h-25" id="meuGrafico2"></canva>
</div> -->
<?php else:?>
    <?php if(isset($search)):?>
        <?php
            $inicio = implode('/', array_reverse(explode('-', $search['data_inicial'])));
            $fim = implode('/', array_reverse(explode('-', $search['data_final'])));
        ?>
        <div class="px-4 mb-3 mt-2 d-flex align-items-center justify-content-between">

            <h4 class="mt-4"><?= "Sua pesquisa por " . "<span class='text-danger'>" . $inicio ." a ". $fim . "</span>" . " não retornou nenhum resultado.";?></h4>
            <form class="row" method="get" action="<?= url_to('search.operacional.get');?>">
                <div class="col">
                    <input type="date" id="data_inicial" name="data_inicial" class="form-control"  required>
                </div>
                <div class="col">
                    <input type="date" id="data_final" name="data_final" class="form-control" required>
                </div>
                <div class="col">
                    <button type="submit" class="form-control btn btn-success w-auto" onclick="return validarDatas(this.form)">Pesquisar</button>
                </div>
            </form>
        </div>
    <?php else:?>
        <div class="empty-state mt-4">
            <div class="d-flex align-items-center gap-3">
                <span class="empty-state-icon"><i class="bi bi-calendar-x"></i></span>
                <div>
                    <h4>Sem informações para exibir</h4>
                    <p>Cadastre o primeiro evento para alimentar o dashboard.</p>
                </div>
            </div>
        </div>
    <?php endif;?>
 <div>
    <a href="<?= (isset($get))? base_url('operacional/planejamento/cadastro') : base_url('cadastro/operacional') ?>" class="btn btn-success">+ Cadastrar evento</a>
 </div>
<?php endif ;?>

<?php if(!empty($data)):?>
<div class="dashboard-table-card bg-white d-block px-3 pt-3 pb-2">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h5 class="mb-0 fw-bold">Eventos realizados</h5>
            <small class="text-muted">Últimos eventos do período exibido</small>
        </div>
        <a href="<?= (isset($get))? base_url('operacional/planejamento/lista') : base_url('/operacional/lista');?>" class="btn btn-sm btn-outline-secondary">Ver lista completa</a>
    </div>
    <div class="table-responsive">
    <table class="table table-white table-hover mt-2 dt-stack">
    <thead>
        <tr>
        <th>Data</th>
          <th>Nome</th>
          <th>Tipo de evento</th>
          <th>Operacional</th>
          <th>Gerador</th>
          <th>Gerador Kwh</th>
          <th>Cedae</th>
          <th>Poço</th>
          <th>Light</th>
          <th>Custo do Evento</th>
          <th>Ação</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($rows as $item):?>
            <?php
                $rowDate = br_date($item->data);
                $kwhGerador = (float)$item->kwh_saida_g1 - (float)$item->kwh_entrada_g1;
                $kwhGerador += (float)$item->kwh_saida_g2 - (float)$item->kwh_entrada_g2;
            ?>
        <tr>
            <td data-label="Data"><?= $rowDate;?></td>
            <td data-label="Nome"><?= esc((string)$item->nome);?></td>
            <td data-label="Tipo de evento"><?= esc((string)$item->tipo);?></td>
            <?php $operacional = $item->super_op + $item->valor_premontagem; ?>
            <td data-label="Operacional" class=""><?= br_money($operacional);?></td>
            <td data-label="Gerador" class=""><?= br_money($item->diesel_total);?></td>
            <td data-label="Gerador Kwh" class=""><?= br_number($kwhGerador, 0);?><?= $kwhGerador ? ' kWh' : ''; ?></td>
            <td data-label="Cedae" class=""><?= br_money($item->cedae_total);?></td>
            <td data-label="Poço" class=""><?= br_number((float)($item->poco_consumo ?? 0), 0); ?> m³</td>
            <td data-label="Light" class=""><?= br_money($item->light_total);?></td>
            <td data-label="Custo do Evento" class=""><?= br_money($item->total_consumo);?></td>
            <td data-label="Ação" class="d-flex justify-content-center dashboard-actions-cell">
                <?php if(!empty($item->descricao)):?>
                    <a href="#" type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#dashboardInfo<?= (int)$item->id;?>"><i class="bi bi-chat-left-text"></i></a>
                <?php else:?>
                    <a href="#" type="button" class="btn btn-secondary text-white"><i class="bi bi-chat-left-text"></i></a>
                <?php endif;?>
                <a href="<?= base_url('/update/operacional/'.(int)$item->id);?>" type="button" class="btn btn-warning text-white"><i class="bi bi-pencil"></i></a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#dashboardDelete<?= (int)$item->id;?>"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
        <div class="modal fade" id="dashboardInfo<?= (int)$item->id;?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5"><?= esc((string)$item->nome);?></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body"><?= esc((string)($item->descricao ?? 'Sem observações.'));?></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="dashboardDelete<?= (int)$item->id;?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Confirmação de exclusão</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Quer mesmo excluir essa informação?</p>
                        <strong><?= esc((string)$item->nome);?></strong>
                        <strong><?= esc($rowDate);?></strong>
                    </div>
                    <div class="modal-footer">
                        <form method="post" action="<?= base_url('/delete/operacional');?>">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="id" value="<?= (int)$item->id;?>" />
                            <input type="hidden" name="nome" value="<?= esc((string)$item->nome);?>" />
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Excluir</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach;?>
    </tbody>
    </table>
    </div>
</div>
<?php endif;?>

<?php else:?>
    <div class="px-4 mb-3 mt-2 d-flex align-items-center justify-content-end dashboard-filter-wrap">
        <form class="row g-2 dashboard-filter-form w-100 justify-content-end" method="get" action="<?= url_to('search.operacional.get');?>">
             <div class="col-12 col-md-3 col-lg-2">
            <input type="date" id="data_inicial" name="data_inicial" class="form-control"  required>
            </div>
            <div class="col-12 col-md-3 col-lg-2">
                <input type="date" id="data_final" name="data_final" class="form-control" required>
            </div>
            <div class="col-12 col-md d-flex align-items-center flex-wrap gap-2 justify-content-md-end">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tipos[]" value="social" id="flexCheck1" <?= $isTipoChecked('social') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="flexCheck1">
                        Social
                    </label>
                    </div>
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tipos[]" value="corporativo" id="flexCheck2" <?= $isTipoChecked('corporativo') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="flexCheck2">
                        Corporativo
                    </label>
                    </div>
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tipos[]" value="entretenimento" id="flexCheck3" <?= $isTipoChecked('entretenimento') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="flexCheck3">
                        Entretenimento
                    </label>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-auto d-grid">
                <button type="submit" class="btn btn-success" onclick="return validarDatas(this.form)">Pesquisar</button>
            </div>
        </form>
    </div>
    <div class="empty-state mt-4">
        <div class="d-flex align-items-center gap-3">
            <span class="empty-state-icon"><i class="bi bi-search"></i></span>
            <div>
                <h4>Sem eventos no período selecionado</h4>
                <p>Ajuste os filtros ou cadastre um novo evento para este período.</p>
            </div>
        </div>
    </div>
    <a href="<?= (isset($get))? base_url('operacional/planejamento/cadastro') : base_url('cadastro/operacional') ?>" class="btn btn-success px-4 mb-3 mt-2">+ Cadastrar evento</a>
<?php endif ;?>

<script type="text/javascript">

$(document).ready(function(){

//   Aplica mascara
  $('input.money').mask('000.000.000.000.000,00', {reverse: true});
  $('.phone').mask('(00)00000-0000', {reverse: false});
});

function validarDatas(form) {
    const dataInicial = form.querySelector('input[name="data_inicial"]')?.value || '';
    const dataFinal = form.querySelector('input[name="data_final"]')?.value || '';

    if (!dataInicial || !dataFinal) {
        alert("Preencha data inicial e final.");
        return false;
    }

    let dtInicial = new Date(dataInicial);
    let dtFinal = new Date(dataFinal);

    if (dtInicial > dtFinal) {
        alert("A data inicial deve ser anterior à data final!");
        return false; // Impede o envio do formulário
    }

    const checkboxes = form.querySelectorAll('input[type="checkbox"][name]');
    if (checkboxes.length) {
        const checked = Array.from(checkboxes).some(function (cb) { return cb.checked; });
        if (!checked) {
            alert("Selecione ao menos um tipo de evento.");
            return false;
        }
    }

    return true; // Permite o envio do formulário
}



const bar = <?= $bar ?? '[]' ?>;
const barChart = <?= $barChart ?? '[]' ?>;
const line = <?= $line ?? '[]' ?>;
const lineTitle = <?= json_encode($lineTitle ?? 'Custo anual') ?>;
const lineGenerator = <?= $lineGenerator ?? '[]' ?>;
const topEvents = <?= $topEvents ?? '[]' ?>;
const publicByCategory = <?= $publicByCategory ?? '[]' ?>;
const taxaCozinhaStatus = <?= $taxaCozinhaStatus ?? '[]' ?>;
const donut = <?= $donut ?? '[]' ?>;
const chartInstances = [];

function hasChartRows(arr) {
    return Array.isArray(arr) && arr.length > 1;
}

function cssVar(name) {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
}

function chartPalette() {
    return [
        cssVar('--app-chart-palette-1'),
        cssVar('--app-chart-palette-2'),
        cssVar('--app-chart-palette-3'),
        cssVar('--app-chart-palette-4'),
        cssVar('--app-chart-palette-5'),
        cssVar('--app-chart-palette-6'),
    ];
}

function numberValue(value) {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : 0;
}

function money(value) {
    return numberValue(value).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        maximumFractionDigits: 2,
    });
}

function chartTheme() {
    return {
        mode: document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light',
        foreColor: cssVar('--app-text'),
    };
}

function baseOptions(extra) {
    return Object.assign({
        chart: {
            fontFamily: 'Roboto, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
            toolbar: { show: false },
            foreColor: cssVar('--app-text'),
            background: 'transparent',
            animations: { enabled: true, speed: 420 },
        },
        theme: chartTheme(),
        colors: chartPalette(),
        dataLabels: { style: { fontWeight: 700 } },
        grid: {
            borderColor: cssVar('--app-chart-grid'),
            strokeDashArray: 4,
        },
        legend: {
            position: 'bottom',
            labels: { colors: cssVar('--app-text') },
        },
        tooltip: {
            theme: document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light',
        },
        noData: {
            text: 'Sem dados para exibir',
            style: { color: cssVar('--app-muted') },
        },
    }, extra);
}

function renderChart(id, options) {
    const el = document.getElementById(id);
    if (!el || !window.ApexCharts) {
        return;
    }

    const chart = new ApexCharts(el, options);
    chart.render();
    chartInstances.push(chart);
}

function tableCategories(table) {
    return table.slice(1).map((row) => String(row[0] ?? ''));
}

function tableSeries(table) {
    const header = table[0] || [];
    return header.slice(1).map((name, columnIndex) => ({
        name: String(name),
        data: table.slice(1).map((row) => numberValue(row[columnIndex + 1])),
    }));
}

function renderAllCharts() {
    while (chartInstances.length) {
        chartInstances.pop().destroy();
    }

    if (hasChartRows(donut)) {
        renderChart('donut_chart', baseOptions({
            chart: { type: 'donut', height: 330, toolbar: { show: false }, background: 'transparent' },
            labels: tableCategories(donut),
            series: donut.slice(1).map((row) => numberValue(row[1])),
            plotOptions: { pie: { donut: { size: '66%' } } },
            dataLabels: {
                formatter: (value) => `${Math.round(value)}%`,
            },
            tooltip: { y: { formatter: money } },
        }));
    }

    if (hasChartRows(bar)) {
        renderChart('column_chart', baseOptions({
            chart: { type: 'bar', height: 330, toolbar: { show: false }, background: 'transparent' },
            plotOptions: { bar: { borderRadius: 5, columnWidth: '58%' } },
            series: tableSeries(bar),
            xaxis: { categories: tableCategories(bar), labels: { style: { colors: cssVar('--app-muted') } } },
            yaxis: { labels: { formatter: (value) => money(value).replace('R$', 'R$ ') } },
            tooltip: { y: { formatter: money } },
        }));
    }

    if (hasChartRows(barChart)) {
        renderChart('column_chart2', baseOptions({
            chart: { type: 'bar', height: 330, toolbar: { show: false }, background: 'transparent' },
            plotOptions: { bar: { borderRadius: 5, columnWidth: '42%' } },
            series: tableSeries(barChart),
            xaxis: { categories: tableCategories(barChart), labels: { style: { colors: cssVar('--app-muted') } } },
            yaxis: { labels: { formatter: money } },
            tooltip: { y: { formatter: money } },
        }));
    }

    if (hasChartRows(publicByCategory)) {
        renderChart('public_category_chart', baseOptions({
            chart: { type: 'bar', height: 330, toolbar: { show: false }, background: 'transparent' },
            plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '56%' } },
            series: [{ name: 'Público', data: publicByCategory.slice(1).map((row) => numberValue(row[1])) }],
            xaxis: { categories: tableCategories(publicByCategory), labels: { formatter: (value) => Number(value).toLocaleString('pt-BR') } },
            dataLabels: { enabled: true, formatter: (value) => Number(value).toLocaleString('pt-BR') },
            tooltip: { y: { formatter: (value) => Number(value).toLocaleString('pt-BR') } },
            colors: [cssVar('--app-chart-palette-2')],
        }));
    }

    if (hasChartRows(taxaCozinhaStatus)) {
        renderChart('taxa_cozinha_status_chart', baseOptions({
            chart: { type: 'donut', height: 330, toolbar: { show: false }, background: 'transparent' },
            labels: tableCategories(taxaCozinhaStatus),
            series: taxaCozinhaStatus.slice(1).map((row) => numberValue(row[1])),
            colors: [cssVar('--app-success'), cssVar('--app-warning'), cssVar('--app-danger')],
            plotOptions: { pie: { donut: { size: '68%' } } },
            tooltip: { y: { formatter: (value) => `${Number(value).toLocaleString('pt-BR')} registro(s)` } },
        }));
    }

    if (hasChartRows(topEvents)) {
        const rows = topEvents.slice(1);
        renderChart('top_events_chart', baseOptions({
            chart: {
                type: 'bar',
                height: 330,
                toolbar: { show: false },
                background: 'transparent',
                events: {
                    dataPointSelection: function (_event, _ctx, config) {
                        const id = rows[config.dataPointIndex]?.[5];
                        if (id) {
                            window.location.href = "<?= site_url('update/operacional') ?>/" + id;
                        }
                    },
                },
            },
            plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '58%' } },
            series: [{ name: 'Custo total', data: rows.map((row) => numberValue(row[1])) }],
            xaxis: { categories: rows.map((row) => String(row[0] ?? '')), labels: { formatter: money } },
            tooltip: {
                y: { formatter: money },
                custom: function ({ dataPointIndex }) {
                    const row = rows[dataPointIndex] || [];
                    return `<div class="px-2 py-1"><strong>${row[0] || ''}</strong><br>${money(row[1])}<br><small>${String(row[3] || '').replace(/\\n/g, '<br>')}</small></div>`;
                },
            },
            dataLabels: { enabled: false },
        }));
    }

    if (hasChartRows(lineGenerator)) {
        renderChart('line_generator_chart', baseOptions({
            chart: { type: 'line', height: 330, toolbar: { show: false }, background: 'transparent' },
            stroke: { curve: 'smooth', width: [3, 3, 0, 0] },
            markers: { size: [3, 3, 6, 6], strokeWidth: 0 },
            series: tableSeries(lineGenerator),
            xaxis: { categories: tableCategories(lineGenerator), labels: { style: { colors: cssVar('--app-muted') } } },
            yaxis: { title: { text: 'Horas acumuladas' } },
            tooltip: { y: { formatter: (value) => `${Number(value || 0).toLocaleString('pt-BR')} h` } },
        }));
    }

    if (hasChartRows(line)) {
        renderChart('line_chart', baseOptions({
            chart: { type: 'area', height: 340, toolbar: { show: false }, background: 'transparent' },
            stroke: { curve: 'smooth', width: 3 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.32, opacityTo: 0.04 } },
            markers: { size: 4, hover: { size: 7 } },
            series: tableSeries(line),
            xaxis: { categories: tableCategories(line), labels: { style: { colors: cssVar('--app-muted') } } },
            yaxis: { labels: { formatter: money } },
            tooltip: { y: { formatter: money } },
            title: { text: lineTitle, style: { color: cssVar('--app-heading'), fontSize: '15px' } },
        }));
    }
}

function initCharts() {
    if (window.ApexCharts) {
        renderAllCharts();
        return;
    }
}

window.addEventListener('load', initCharts);
window.addEventListener('apexcharts:ready', initCharts);
window.addEventListener('app:themechange', function () {
    if (window.ApexCharts) {
        renderAllCharts();
    }
});

</script>


<?php $this->endSection('content');?>
