<?= $this->extend('main')?>;

<?php

use Composer\Installers\PhpBBInstaller;

 $this->section('content');?>



<?php if($sum->total_consumo):?>

<?php if(!empty($data)):?>
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
            <input type="date" id="data_inicial" name="data_inicial" class="form-control"  required>
        </div>
        <div class="col">
            <input type="date" id="data_final" name="data_final" class="form-control" required>
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
            <button type="submit" class="form-control btn btn-success w-auto" onclick="return validarDatas()">Pesquisar</button>
        </div>
    </form>
</div>

<?php helper('format'); ?>
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
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card bg-white text-success rounded shadow-lg">
                <div class="pt-3">Gerador </br><?= $sum->diesel_litros . " litros" # em " . ($sum->tempo_g1 + $sum->tempo_g2). " horas"?></div>
                <div class="fw-bold pt-3 pb-3 fs-4 money"><?= br_money($sum->diesel_total);?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card bg-white text-success rounded shadow-lg">
                <div class="pt-3">Gerador Kwh</div>
                <?php $kwhTotal = (float)($sum->kwh_total ?? 0); ?>
                <div class="fw-bold pt-3 pb-3 fs-4"><?= br_number($kwhTotal, 0);?><?= $kwhTotal ? ' kWh' : ''; ?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card text-success bg-white bg-gradient rounded shadow-lg">
                <div class="pt-3">Água e esgoto </br><?= $sum->cedae_consumo . " M³";?></div>
                <div class="fw-bold pt-3 pb-3 fs-4 money"><?= br_money($sum->cedae_total);?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card bg-white bg-gradient rounded text-success shadow-lg">
                <div class="pt-3">Light </br><?= $sum->light_consumo . " Kwh";?></div>
                <div class="fw-bold pt-3 pb-3 fs-4 money"><?= br_money($sum->light_total);?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card bg-white bg-gradient rounded text-success shadow-lg">
                <div class="pt-3">Operacional </br>Pré-montagem</div>
                <div class="fw-bold pt-3 pb-3 fs-4 money"><?= br_money($sum->valor_premontagem);?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card bg-white bg-gradient rounded text-success shadow-lg">
                <div class="pt-3">Operacional </br> evento</div>
                <div class="fw-bold pt-3 pb-3 fs-4 money"><?= br_money($sum->super_op);?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card bg-white bg-gradient rounded text-primary shadow-lg">
                <div class="pt-3">Custo médio<br>por evento</div>
                <div class="fw-bold pt-3 pb-3 fs-4 money"><?= br_money($avg_event_cost ?? 0);?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card bg-white bg-gradient rounded text-danger shadow-lg">
                <div class="pt-3">Custo total <br>no período</div>
                <div class="fw-bold pt-3 pb-3 fs-4 money"><?= br_money($sum->total_consumo);?></div>
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
        <div class="chart-box shadow" id="top_events_chart"></div>
    </div>
    <!-- <div class="col-12 col-md-6 shadow" id="curve_chart" ></div> -->
     <!-- <div class="col-12 col-md-6 shadow" id="line_chart"></div> -->
</div>
<div class="chart row g-3 mt-2 mb-2">
    <!-- <div class="col-12 col-md-6 shadow" id="column_chart2" ></div> -->
    <!-- <div class="col-12 col-md-6 shadow" id="curve_chart" ></div> -->
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
            <form class="row" method="post" action="<?= url_to('search.operacional');?>" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="col">
                    <input type="date" id="data_inicial" name="data_inicial" class="form-control"  required>
                </div>
                <div class="col">
                    <input type="date" id="data_final" name="data_final" class="form-control" required>
                </div>
                <div class="col">
                    <button type="submit" class="form-control btn btn-success w-auto" onclick="return validarDatas()">Pesquisar</button>
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
          <th>Light</th>
          <th>Custo do Evento</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data as $item):?>
            <?php
                $data = br_date($item->data);
                $kwhGerador = (float)$item->kwh_saida_g1 - (float)$item->kwh_entrada_g1;
                $kwhGerador += (float)$item->kwh_saida_g2 - (float)$item->kwh_entrada_g2;
            ?>
        <tr>
            <th scope="row"><?= $data;?></th>
            <td><?= $item->nome;?></td>
            <td><?= $item->tipo;?></td>
            <?php $operacional = $item->super_op + $item->valor_premontagem; ?>
            <td class=""><?= br_money($operacional);?></td>
            <td class=""><?= br_money($item->diesel_total);?></td>
            <td class=""><?= br_number($kwhGerador, 0);?><?= $kwhGerador ? ' kWh' : ''; ?></td>
            <td class=""><?= br_money($item->cedae_total);?></td>
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
    <div class="px-4 mb-3 mt-2 d-flex align-items-center justify-content-end">
        <form class="row" method="post" action="<?= url_to('search.operacional');?>" enctype="multipart/form-data">
            <?= csrf_field(); ?>
             <div class="col">
            <input type="date" id="data_inicial" name="data_inicial" class="form-control"  required>
            </div>
            <div class="col">
                <input type="date" id="data_final" name="data_final" class="form-control" required>
            </div>
            <div class="col d-flex align-items-center justify-content-end">
                <div class="d-flex align-items-center justify-content-end">
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="social" value="social" id="flexCheck1" checked>
                    <label class="form-check-label me-3" for="flexCheck1">
                        Social
                    </label>
                    </div>
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="corporativo" value="Corporativo" id="flexCheck2" checked>
                    <label class="form-check-label me-3" for="flexCheck2">
                        Corporativo
                    </label>
                    </div>
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="entretenimento" value="Entretenimento" id="flexCheck3" checked>
                    <label class="form-check-label" for="flexCheck3">
                        Entretenimento
                    </label>
                    </div>
                </div>
            </div>
                <button type="submit" class="form-control btn btn-success w-auto" onclick="return validarDatas()">Pesquisar</button>
            </div>
        </form>
    </div>
    <h4 class="mt-4">Sem eventos no período!</h4>
    <a href="<?= (isset($get))? base_url('operacional/planejamento/cadastro') : base_url('cadastro/operacional') ?>" class="btn btn-success px-4 mb-3 mt-2">+ Cadastrar evento</a>
<?php endif ;?>

<script type="text/javascript">

$(document).ready(function(){

//   Aplica mascara
  $('input.money').mask('000.000.000.000.000,00', {reverse: true});
  $('.phone').mask('(00)00000-0000', {reverse: false});
});

function validarDatas() {
    let dataInicial = document.getElementById("data_inicial").value;
    let dataFinal = document.getElementById("data_final").value;

    if (dataInicial && dataFinal) { // Verifica se os campos estão preenchidos
        let dtInicial = new Date(dataInicial);
        let dtFinal = new Date(dataFinal);

        if (dtInicial > dtFinal) {
            alert("A data inicial deve ser anterior à data final!");
            return false; // Impede o envio do formulário
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
     drawTopEventsChart();
     drawLineChart();
 }

//  GRAFICO COLUNA

// Dados passados do PHP para o JavaScript
//
//
//
var bar = <?= $bar ?? '[]' ?>;


 function drawColumnChart() {
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
var topEvents = <?= $topEvents ?? '[]' ?>;
//  GRAFICO LINHA
 function drawLineChart() {
     var data = google.visualization.arrayToDataTable(line);

     var options = {
         title: 'Custo Anual',
        //  hAxis: { title: ''   },
         vAxis: { title: 'Custo' },
         legend: { position: 'bottom' },
         colors: ['#999', '#f00']
     };

     var chart = new google.visualization.LineChart(document.getElementById('line_chart'));
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
         title: 'Comparação por tipo de evento',
         hAxis: { title: '' },
         vAxis: { title: 'Total'},
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
    if (!topEvents || topEvents.length <= 1) {
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
