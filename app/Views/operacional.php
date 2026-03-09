<?= $this->extend('main')?>;

<?php

 $this->section('content');?>
<?php helper('format'); ?>
<?php
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
$selectedTipos = $tipo ?? ['social', 'corporativo', 'entretenimento'];
$isTipoChecked = static function (string $value) use ($selectedTipos): bool {
    return in_array($value, $selectedTipos, true);
};
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

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger mt-3 mb-0"><?= esc((string)session()->get('error')); ?></div>
<?php endif; ?>
<?php if (session()->has('success')): ?>
    <div class="alert alert-success mt-3 mb-0"><?= esc((string)session()->get('success')); ?></div>
<?php endif; ?>

<?php if($hasRows || (float)($sum->total_consumo ?? 0) > 0):?>

<?php if($hasRows):?>
<div class="px-4 mb-3 mt-2 d-flex align-items-center justify-content-end dashboard-filter-wrap">

<!-- <nav class="" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url();?>">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Library</li>
        </ol>
    </nav> -->
    <form class="row g-2 dashboard-filter-form w-100 justify-content-end" method="get" action="<?= url_to('search.operacional.get');?>">
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
    <div>
        <?php
            $inicio = br_date($search['data_inicial']);
            $fim = br_date($search['data_final']);
        ?>
        <h6>Exibindo resultados do período de <span class="text-danger"><?= $inicio . " a " . $fim;?></span>
        <?php foreach($tipo as $item){
            echo " " . $item . " ";
        }
        ?></h6>
    </div>
<?php elseif(isset($get)):?>
<h6>Exibindo planejamento para o mês de  <span class="text-danger"><?= $mes; ?></span></h6>
<?php else:?>
<h6>Exibindo lançamentos de <span class="text-danger"><?= $mes; ?></span></h6>
<?php endif;?>



<div class="px-1 text-center p-2">
    <?php if (!empty($generatorUsage['has_alert'])): ?>
        <div class="alert alert-danger alert-dismissible fade show text-start mb-3" role="alert">
            Atenção: um ou mais geradores já atingiram 200 horas de uso e precisam de manutenção.
            <a href="<?= base_url('operacional/geradores'); ?>" class="btn btn-sm btn-danger ms-2">Acessar Geradores</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($generatorUsage['has_warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show text-start mb-3" role="alert">
            Alerta: gerador próximo da manutenção (acima de 180 horas de uso).
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($cedaeAlert['is_alert'])): ?>
        <div class="alert alert-danger alert-dismissible fade show text-start mb-3" role="alert">
            <?php
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
            Atenção CEDAE: variação de consumo acima de <?= br_number((float)$cedaeAlert['limit'], 0); ?> m³.
            <?= $cedaeEventList !== '' ? (' ' . $cedaeEventList . '.') : ''; ?>
            Possível vazamento ou consumo anormal de água.
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
                <div class="small text-muted">Consumo água poço</div>
                <div class="small text-muted">Total no período</div>
                <div class="fw-bold fs-2 text-dark mt-2"><?= br_number((float)($sum->poco_consumo ?? 0), 0); ?> <span class="fs-6">m³</span></div>
                <?php $t = $trendFor('poco_consumo'); ?>
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
                <div class="small text-muted">Consumo água CEDAE</div>
                <div class="small text-muted">Total no período</div>
                <div class="fw-bold fs-2 text-dark mt-2"><?= br_number((float)($sum->cedae_consumo ?? 0), 0); ?> <span class="fs-6">m³</span></div>
                <?php $t = $trendFor('cedae_consumo'); ?>
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
        <div class="col-12 col-md-6 order-1 order-lg-2">
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
        <div class="col-12 col-md-6 order-1 order-lg-2">
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

<div class="chart row g-3 mt-2 mb-2">
    <div class="col-12 col-md-6">
        <div class="chart-box shadow-lg" id="donut_chart"></div>
    </div>
    <div class="col-12 col-md-6">
        <div class="chart-box shadow-lg" id="column_chart"></div>
    </div>
</div>

<div class="chart row g-3 mt-2 mb-2">
    <div class="col-12 col-md-6">
        <div class="chart-box shadow" id="column_chart2" ></div>
    </div>
    <div class="col-12 col-md-6">
        <div class="chart-box shadow" id="public_category_chart"></div>
    </div>
</div>

<div class="chart row g-3 mt-2 mb-2">
    <div class="col-12 col-md-6">
        <?php if ($hasTopEvents): ?>
            <div class="chart-box shadow" id="top_events_chart"></div>
        <?php else: ?>
            <div class="chart-box shadow d-flex align-items-center justify-content-center text-muted">
                Ainda não há eventos suficientes para montar o ranking.
            </div>
        <?php endif; ?>
    </div>
     <div class="col-12 col-md-6">
        <div class="chart-box shadow" id="line_generator_chart"></div>
     </div>
</div>

<div class="chart row g-3 mt-2 mb-2">
    <div class="col-12">
        <div class="chart-box shadow" id="line_chart"></div>
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
        <h4 class="mt-4">Sem informaçẽos para exibir!</h4>
    <?php endif;?>
 <div>
    <a href="<?= (isset($get))? base_url('operacional/planejamento/cadastro') : base_url('cadastro/operacional') ?>" class="btn btn-success">+ Cadastrar evento</a>
 </div>
<?php endif ;?>

<?php if(!empty($data)):?>

<a href="<?= (isset($get))? base_url('operacional/planejamento/lista') : base_url('/operacional/lista');?>" class="shadow-lg text-decoration-none bg-white d-block px-2 pt-1">
    <h6 class="mt-3">Eventos realizados</h6>
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
            <th scope="row"><?= $rowDate;?></th>
            <td><?= $item->nome;?></td>
            <td><?= $item->tipo;?></td>
            <?php $operacional = $item->super_op + $item->valor_premontagem; ?>
            <td class=""><?= br_money($operacional);?></td>
            <td class=""><?= br_money($item->diesel_total);?></td>
            <td class=""><?= br_number($kwhGerador, 0);?><?= $kwhGerador ? ' kWh' : ''; ?></td>
            <td class=""><?= br_money($item->cedae_total);?></td>
            <td class=""><?= br_number((float)($item->poco_consumo ?? 0), 0); ?> m³</td>
            <td class=""><?= br_money($item->light_total);?></td>
            <td class=""><?= br_money($item->total_consumo);?></td>

        </tr>
    </tbody>
    <?php endforeach;?>
    </table>
    </div>
</a>
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
    <div class="alert alert-info mt-4">Sem eventos no período selecionado.</div>
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



// GOOGLE CHARTS

// Carregar os pacotes de gráficos
 google.charts.load('current', { packages: ['corechart', 'line', 'scatter'] });

 // Definir a função para desenhar os gráficos
 google.charts.setOnLoadCallback(drawCharts);

 function drawCharts() {
    drawDonutChart();
     drawColumnChart();
     drawColumnChart2();
     drawPublicCategoryChart();
     drawTopEventsChart();
     drawLineChart();
     drawLineGeneratorChart();
 }

//  GRAFICO COLUNA

// Dados passados do PHP para o JavaScript
//
//
//
var bar = <?= $bar ?? '[]' ?>;


 function drawColumnChart() {
     if (!hasChartRows(bar) || !document.getElementById('column_chart')) {
         return;
     }
    //   var data = google.visualization.arrayToDataTable([
    //       ['ano', 'Vendas', 'Despesas', 'teste', 'teste2'],
    //       ['Social', 1000, 400, 300, 100],
    //       ['2020', 1170, 460, 200, 500],
    //       ['2021', 660, 1120, 900, 600],
    //       ['2022', 1030, 540, 450, 300]
    //   ]);

      var data = google.visualization.arrayToDataTable(bar);



    //  var data = google.visualization.arrayToDataTable(columnChart);

     var options = {
         title: 'Comparação por tipo de evento',
         hAxis: { title: '' },
         vAxis: { title: 'Total'},
         //backgroundColor: '#cccccc', // Cor de fundo
         legend: { position: 'bottom' },
         seriesType: 'bars'
     };

     var chart = new google.visualization.ComboChart(document.getElementById('column_chart'));
     chart.draw(data, options);
 }


//  GRAFICO PIZZA

 function drawPieChart() {
      var data = google.visualization.arrayToDataTable([
          ['Categoria', 'Valor'],
          ['Alimentos', 30],
          ['Transporte', 20],
          ['Habitação', 25],
          ['Saúde', 15],
          ['Lazer', 10]
      ]);

    //  var data = google.visualization.arrayToDataTable(pieChart);

     var options = {
         title: 'Total de de eventos',
         pieSliceText: 'label',
         is3D: true
     };

     var chart = new google.visualization.PieChart(document.getElementById('pie_chart'));
     chart.draw(data, options);
 }



var line = <?= $line ?? '[]' ?>;
var lineTitle = <?= json_encode($lineTitle ?? 'Custo anual') ?>;
var lineGenerator = <?= $lineGenerator ?? '[]' ?>;
var topEvents = <?= $topEvents ?? '[]' ?>;
var publicByCategory = <?= $publicByCategory ?? '[]' ?>;

function hasChartRows(arr) {
    return Array.isArray(arr) && arr.length > 1;
}

function drawPublicCategoryChart() {
    if (!hasChartRows(publicByCategory) || !document.getElementById('public_category_chart')) {
        return;
    }

    var rawData = google.visualization.arrayToDataTable(publicByCategory);
    var view = new google.visualization.DataView(rawData);
    view.setColumns([
        0,
        1,
        {
            calc: function (dt, row) {
                return Number(dt.getValue(row, 1) || 0).toLocaleString('pt-BR');
            },
            type: 'string',
            role: 'annotation'
        }
    ]);

    var options = {
        title: 'Público por categoria',
        legend: { position: 'none' },
        bars: 'horizontal',
        height: 320,
        colors: ['#198754'],
        chartArea: { left: 130, width: '68%' },
        hAxis: { title: 'Público total', minValue: 0 },
        vAxis: { title: '' },
        annotations: {
            textStyle: { color: '#1f2937', fontSize: 11, bold: true }
        }
    };

    var chart = new google.visualization.BarChart(document.getElementById('public_category_chart'));
    chart.draw(view, options);
}
//  GRAFICO LINHA
 function drawLineChart() {
     if (!hasChartRows(line) || !document.getElementById('line_chart')) {
         return;
     }
     var lineSeriesCount = Array.isArray(line) && line[0] ? Math.max(0, line[0].length - 1) : 0;
     var hasSinglePoint = lineSeriesCount === 1 && line.length === 2;
     var data = null;

     if (hasSinglePoint) {
         var periodLabel = line[1][0];
         var costValue = Number(line[1][1] || 0);
         var formatted = costValue.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
         data = google.visualization.arrayToDataTable([
             ['Período', 'Custo', { role: 'annotation' }],
             [periodLabel, costValue, 'R$ ' + formatted]
         ]);
     } else {
         data = google.visualization.arrayToDataTable(line);
     }

     var options = {
         title: lineTitle,
         vAxis: { title: 'Custo' },
         legend: { position: 'bottom' },
         colors: lineSeriesCount > 1 ? ['#999', '#f00'] : ['#0d6efd'],
         pointSize: hasSinglePoint ? 8 : 4,
         annotations: {
             textStyle: { color: '#212529', fontSize: 12, bold: true }
         }
     };

     var chart = new google.visualization.LineChart(document.getElementById('line_chart'));
     chart.draw(data, options);
 }

 function drawLineGeneratorChart() {
     if (!hasChartRows(lineGenerator) || !document.getElementById('line_generator_chart')) {
         return;
     }
     var header = lineGenerator[0] || [];
     var rows = lineGenerator.slice(1);
     var data = new google.visualization.DataTable();
     data.addColumn('string', String(header[0] || 'Data'));
     for (var c = 1; c < header.length; c++) {
         data.addColumn('number', String(header[c] || ('Série ' + c)));
     }
     data.addRows(rows.map(function (row) {
         var out = [String(row[0] || '')];
         for (var i = 1; i < header.length; i++) {
             var raw = row[i];
             if (raw === null || raw === undefined || raw === '') {
                 out.push(null);
                 continue;
             }
             var n = Number(raw);
             out.push(Number.isFinite(n) ? n : null);
         }
         return out;
     }));

     var options = {
         title: 'Acúmulo de uso dos geradores (horas)',
         hAxis: { title: 'Data' },
         vAxis: { title: 'Horas acumuladas' },
         legend: { position: 'bottom' },
         colors: ['#198754', '#0d6efd', '#dc3545', '#fd7e14'],
         pointSize: 5,
         series: {
             0: { lineWidth: 2, pointSize: 5 },
             1: { lineWidth: 2, pointSize: 5 },
             2: { lineWidth: 0, pointSize: 8, pointShape: 'diamond' },
             3: { lineWidth: 0, pointSize: 8, pointShape: 'diamond' }
         }
     };

     var chart = new google.visualization.LineChart(document.getElementById('line_generator_chart'));
     chart.draw(data, options);
 }

//  Grafico de curva
function drawChart() {
    var data = google.visualization.arrayToDataTable([
        ['Year', 'Sales', 'Expenses', 'teste'],
        ['2004',  1000,      400, 300],
        ['2005',  1170,      460, 500],
        ['2006',  660,       1120, 100],
        ['2007',  1030,      540, 900]
    ]);

    var options = {
        title: 'Company Performance',
        curveType: 'function',
        legend: { position: 'bottom' }
    };

    var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

    chart.draw(data, options);
}

var barChart = <?= $barChart ?? '[]' ?>;

 function drawColumnChart2() {
    if (!hasChartRows(barChart) || !document.getElementById('column_chart2')) {
        return;
    }
    //    var data = google.visualization.arrayToDataTable([
    //        ['ano', 'Vendas', 'Despesas', 'teste', 'teste2'],
    //        ['Social', 1000, 400, 300, 100],
    //        ['2020', 1170, 460, 200, 500],
    //        ['2021', 660, 1120, 900, 600],
    //        ['2022', 1030, 540, 450, 300]
    //    ]);

    var data = google.visualization.arrayToDataTable(barChart);
      
     var options = {
         //colors: ['#00f'],
         title: 'Custo médio por evento por tipo',
         hAxis: { title: '' },
         vAxis: { title: 'Valor médio (R$)'},
         //backgroundColor: '#cccccc', // Cor de fundo
         legend: { position: 'bottom' },
         seriesType: 'bars'
         
     };

     var chart = new google.visualization.ComboChart(document.getElementById('column_chart2'));
     chart.draw(data, options);
 }



  function drawScatterChart() {
      var data = google.visualization.arrayToDataTable([
          ['X', 'Y'],
          [1, 1],
          [2, 4],
          [3, 9],
          [4, 16],
          [5, 25]
      ]);

var options = {
          title: 'Gráfico de Dispersão',
          hAxis: { title: 'X' },
          vAxis: { title: 'Y' },
          legend: 'none'
      };

      var chart = new google.visualization.ScatterChart(document.getElementById('scatter_chart'));
      chart.draw(data, options);
}

function drawTopEventsChart() {
    if (!hasChartRows(topEvents) || !document.getElementById('top_events_chart')) {
        return;
    }
    var data = google.visualization.arrayToDataTable(topEvents);
    var view = new google.visualization.DataView(data);
    view.setColumns([0, 1, 3, 4]);

    var options = {
        title: 'Ranking de eventos por custo',
        legend: { position: 'none' },
        bars: 'horizontal',
        height: 320,
        colors: ['#3b82f6'],
        chartArea: { left: 140, width: '70%' },
        tooltip: { isHtml: false }
    };
    var chart = new google.visualization.BarChart(document.getElementById('top_events_chart'));
    chart.draw(view, options);

    google.visualization.events.addListener(chart, 'select', function () {
        var selection = chart.getSelection();
        if (!selection.length) return;
        var row = selection[0].row;
        var id = data.getValue(row, 5);
        if (id) {
            window.location.href = "<?= site_url('update/operacional') ?>/" + id;
        }
    });
}

var donut = <?= $donut ?? '[]' ?>;

function drawDonutChart() {
    if (!hasChartRows(donut) || !document.getElementById('donut_chart')) {
        return;
    }
    // Dados do gráfico
    // var data = google.visualization.arrayToDataTable([
    //     ['Categoria', 'Valor'],
    //     ['Categoria A', 1130.20],
    //     ['Categoria B', 70.20],
    //     ['Categoria C', 40.59],
    //     ['Categoria D', 60.24]
    // ]);
    var data = google.visualization.arrayToDataTable(donut);

    // Opções do gráfico
    var options = {
        title: 'Distribuição por Categorias',
        pieHole: 0.4, // Tamanho do furo no meio
        slices: {
            0: { offset: 0.1 }, // Destacar a primeira fatia (opcional)
            1: { offset: 0.1 }, // Destacar a segunda fatia (opcional)
        },
        legend: { position: 'right' },
        pieSliceText: 'percentage', // Mostrar percentual nas fatias
        is3D: false // Se deseja um efeito 3D (opcional)
    };

    // Criar e desenhar o gráfico
    var chart = new google.visualization.PieChart(document.getElementById('donut_chart'));
    chart.draw(data, options);
}

</script>


<?php $this->endSection('content');?>
