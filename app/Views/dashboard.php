<?= $this->extend('main')?>;

<?php $this->section('content');?>

<div class="px-4 mb-3 mt-2 d-flex align-items-center justify-content-end">

<!-- <nav class="" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url();?>">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Library</li>
        </ol>
    </nav> -->
    <form class="row" method="post" action="<?= url_to('search.operacional');?>" enctype="multipart/form-data">
        <?= csrf_field(); ?>
        <div class="col">
            <input type="date" id="data_inicial" name="data_inicial" class="form-control"  required disabled>
        </div>
        <div class="col">
            <input type="date" id="data_final" name="data_final" class="form-control" required disabled>
        </div>
        <div class="col d-flex align-items-center">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="inlineCheckbox1" name="social" value="social" checked>
                <label class="form-check-label" for="inlineCheckbox1">Social</label>
                </div>
                <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="inlineCheckbox2" name="corporativo" value="corporativo" checked>
                <label class="form-check-label" for="inlineCheckbox2">Corporativo</label>
                </div>
                <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="inlineCheckbox3" name="entretenimento" value="entretenimento" checked>
                <label class="form-check-label" for="inlineCheckbox3">Entretenimento</label>
            </div>
        </div>
        <div class="col">
            <button type="submit" class="form-control btn btn-success w-auto" onclick="return validarDatas()" disabled>Pesquisar</button>
        </div>
    </form>
</div>


<div class="px-1 text-center p-2">
  <?php $generatorUsage = $generatorUsage ?? null; ?>
  <?php if (!empty($generatorUsage['has_alert'])): ?>
    <div class="alert alert-danger text-start mb-3" role="alert">
      Atenção: um ou mais geradores atingiram 200:00 horas de uso.
    </div>
  <?php elseif (!empty($generatorUsage['has_warning'])): ?>
    <div class="alert alert-warning text-start mb-3" role="alert">
      Alerta: um ou mais geradores estão acima de 180:00 horas de uso.
    </div>
  <?php endif; ?>
  <?php if (empty($hasDashboardData)): ?>
    <div class="alert alert-info mb-3" role="alert">
      Ainda não há dados suficientes para montar a dashboard.
    </div>
  <?php endif; ?>
  <div class="row g-3">
    <div class="col-12 col-md-6 col-lg-3">
      <div class="stat-card bg-white text-primary rounded shadow-lg">
     <div class="pt-3">Total Propostas aceitas</div>
     <div class="fw-bold pt-3 pb-3 fs-4"><?= "R$ " . number_format($aceita['value'], 2, ',', '.');?></div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="stat-card text-success bg-white bg-gradient rounded shadow-lg">
      <div class="pt-3">Saldo acumulado <?= date('Y');?></div>
      <div class="fw-bold pt-3 pb-3 fs-4"><?= "R$ " . number_format($saldo, 2, ',', '.');?></div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="stat-card bg-white bg-gradient rounded text-info shadow-lg">
      <div class="pt-3">Propostas em análise</div>
      <div class="fw-bold pt-3 pb-3 fs-4"><?= "R$ " . number_format($analise['value'], 2, ',', '.');?></div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="stat-card bg-white bg-gradient rounded text-warning shadow-lg">
      <div class="pt-3">Uso geradores</div>
      <div class="fw-bold pt-3 pb-3 fs-5">
        G1: <?= esc($generatorUsage['g1']['total_hhmm'] ?? '00:00'); ?><br>
        G2: <?= esc($generatorUsage['g2']['total_hhmm'] ?? '00:00'); ?><br>
        <small class="text-muted">Proj. G1: <?= isset($generatorUsage['g1']['days_to_maintenance']) ? ((int)$generatorUsage['g1']['days_to_maintenance'] . ' dias') : 'sem dados'; ?></small><br>
        <small class="text-muted">Proj. G2: <?= isset($generatorUsage['g2']['days_to_maintenance']) ? ((int)$generatorUsage['g2']['days_to_maintenance'] . ' dias') : 'sem dados'; ?></small>
      </div>
      </div>
    </div>
  </div>
</div>


<div class="chart row g-3 mt-2 mb-2 h-25">
    <div class="col-12 col-md-6">
      <?php if (!empty($hasChartData)): ?>
        <div class="chart-box shadow-sm" id="column_chart"></div>
      <?php else: ?>
        <div class="chart-box shadow-sm d-flex align-items-center justify-content-center text-muted">
          Sem dados de propostas por trimestre.
        </div>
      <?php endif; ?>
    </div>
    <div class="col-12 col-md-6">
      <?php if (!empty($hasChartData)): ?>
        <div class="chart-box shadow-sm" id="pie_chart"></div>
      <?php else: ?>
        <div class="chart-box shadow-sm d-flex align-items-center justify-content-center text-muted">
          Sem dados financeiros para distribuição.
        </div>
      <?php endif; ?>
    </div>
</div>
<!-- <div class="chart row d-flex border m-1 h-25">
    <div class="col-12 col-md-6" id="donut_chart"></div>
    <div class="col-12 col-md-6" id="line_chart"></div>
    <div class="col-12 col-md-6" id="scatter_chart"></div>

</div> -->


<!-- <div class="chart row d-flex border m-1">
    <canvas class="col-12 col-md-6 col-lg-3 chart-container w-50 h-25" id="meuGrafico"></canvas>
    <canvas class="col-12 col-md-6 col-lg-3 chart-container w-50 h-25" id="meuGrafico2"></canva>
</div> -->

<?php if(!empty($recent)):?>

    <a href="<?= base_url('/proposta');?>" class="shadow text-decoration-none bg-white d-block px-2 pt-1">
        <h6 class="mt-3">Últimas Propostas enviadas</h6>
        <div class="table-responsive">
        <table class="table table-white table-hover mt-2 dt-stack">
        <thead>
            <tr>
            <th scope="col">Data</th>
            <th scope="col">Tipo</th>
            <th scope="col">Nome do evento</th>
            <th scope="col">Valor bruto</th>
            <th scope="col">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($recent as $item):?>
                <?php
                    $data = implode('/', array_reverse(explode('-', $item->start_date)));
                ?>
            <tr>
                <th scope="row"><?= $data;?></th>
                <td><?= $item->type;?></td>
                <td><?= $item->event_name;?></td>
                <td class="text-end"><?= "R$ " . number_format($item->value, 2, ',', '.');?></td>
                <td class="text-end"><?= "R$ " . number_format($item->saldo, 2, ',', '.');?></td>
            </tr>
        </tbody>
        <?php endforeach;?>
        </table>
        </div>
    </a>
<?php endif;?>
<?php if (empty($recent)): ?>
    <div class="shadow bg-white d-block px-2 pt-3 pb-3 text-center text-muted">
      Nenhuma proposta recente encontrada.
    </div>
<?php endif; ?>




<script type="text/javascript">

$(document).ready(function(){

//   Aplica mascara
  $('input.money').mask('000.000.000.000.000,00', {reverse: true});
  $('.phone').mask('(00)00000-0000', {reverse: false});
});

function validarDatas() {
    let dataInicial = document.getElementById("data_inicial").value;
    let dataFinal = document.getElementById("data_final").value;

    if (!dataInicial || !dataFinal) {
        alert("Você precisa preencher os campos!");
        return false;
    }

    let dtInicial = new Date(dataInicial);
    let dtFinal = new Date(dataFinal);
    if (dtInicial > dtFinal) {
        alert("A data inicial deve ser anterior à data final!");
        return false;
    }

    return true; // Permite o envio do formulário
}

// GOOGLE CHARTS

// Carregar os pacotes de gráficos
 google.charts.load('current', { packages: ['corechart', 'line', 'scatter'] });

 // Definir a função para desenhar os gráficos
 google.charts.setOnLoadCallback(drawCharts);

 function drawCharts() {
     drawColumnChart();
     drawPieChart();
}

//  GRAFICO COLUNA

// Dados passados do PHP para o JavaScript
var columnChart = <?= $columnChart; ?>;
var pieChart = <?= $pieChart; ?>;
var hasChartData = <?= !empty($hasChartData) ? 'true' : 'false'; ?>;

 function drawColumnChart() {
    if (!hasChartData || !document.getElementById('column_chart')) {
      return;
    }
    //  var data = google.visualization.arrayToDataTable([
    //      ['Ano', 'Vendas', 'Despesas'],
    //      ['2019', 1000, 400],
    //      ['2020', 1170, 460],
    //      ['2021', 660, 1120],
    //      ['2022', 1030, 540]
    //  ]);

     var data = google.visualization.arrayToDataTable(columnChart);

     var options = {
         title: 'Propostas enviadas por trimestre',
         hAxis: { title: 'Ano' },
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
    if (!hasChartData || !document.getElementById('pie_chart')) {
      return;
    }
    //  var data = google.visualization.arrayToDataTable([
    //      ['Categoria', 'Valor'],
    //      ['Alimentos', 30],
    //      ['Transporte', 20],
    //      ['Habitação', 25],
    //      ['Saúde', 15],
    //      ['Lazer', 10]
    //  ]);

     var data = google.visualization.arrayToDataTable(pieChart);

     var options = {
         title: 'Total de de eventos',
         pieSliceText: 'label',
         is3D: true
     };

     var chart = new google.visualization.PieChart(document.getElementById('pie_chart'));
     chart.draw(data, options);
 }



</script>

<?php $this->endSection('content');?>
