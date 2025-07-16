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
  <div class="row">
    <div class="col ms-1 me-1 bg-white text-primary rounded shadow-lg">
     <div class="pt-3">Total Propostas aceitas</div>
     <div class="fw-bold pt-3 pb-3 fs-4"><?= "R$ " . number_format($aceita['value'], 2, ',', '.');?></div>
    </div>
    <div class="col ms-1 me-1 text-success bg-white bg-gradient rounded shadow-lg">
      <div class="pt-3">Saldo acumulado <?= date('Y');?></div>
      <div class="fw-bold pt-3 pb-3 fs-4"><?= "R$ " . number_format($saldo, 2, ',', '.');?></div>
    </div>
    <div class="col ms-1 me-1 bg-white bg-gradient rounded text-info shadow-lg">
      <div class="pt-3">Propostas em análise</div>
      <div class="fw-bold pt-3 pb-3 fs-4"><?= "R$ " . number_format($analise['value'], 2, ',', '.');?></div>
    </div>
    <div class="col ms-1 me-1 bg-white bg-gradient rounded text-warning shadow-lg">
      <div class="pt-3">sem utilização</div>
      <div class="fw-bold pt-3 pb-3 fs-4">Sem informação</div>
    </div>
  </div>
</div>


<div class="chart row d-flex mt-2 mb-2 h-25">
    <div class="col-6" id="column_chart"></div>
    <div class="col-6" id="pie_chart"></div>
</div>
<!-- <div class="chart row d-flex border m-1 h-25">
    <div class="col-6" id="donut_chart"></div>
    <div class="col-6" id="line_chart"></div>
    <div class="col-6" id="scatter_chart"></div>

</div> -->


<!-- <div class="chart row d-flex border m-1">
    <canvas class="col-3 chart-container w-50 h-25" id="meuGrafico"></canvas>
    <canvas class="col-3 chart-container w-50 h-25" id="meuGrafico2"></canva>
</div> -->

<?php if(!empty($recent)):?>

    <a href="<?= base_url('/proposta');?>" class="shadow text-decoration-none bg-white d-block px-2 pt-1">
        <h6 class="mt-3">Últimas Propostas enviadas</h6>
        <table class="table table-white table-hover mt-2">
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
    </a>
<?php endif;?>




<script type="text/javascript">

$(document).ready(function(){

//   Aplica mascara
  $('.money').mask('000.000.000.000.000,00', {reverse: true});
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
        alert("Você precisa preencher os campos!")
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
     drawLineChart();
     drawDonutChart();
     drawScatterChart();
 }

//  GRAFICO COLUNA

// Dados passados do PHP para o JavaScript
var columnChart = <?= $columnChart; ?>;
var pieChart = <?= $pieChart; ?>;

 function drawColumnChart() {
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



//  GRAFICO LINHA
 function drawLineChart() {
     var data = new google.visualization.DataTable();
     data.addColumn('number', 'Ano');
     data.addColumn('number', 'Crescimento');

     data.addRows([
         [2019, 10],
         [2020, 15],
         [2021, 25],
         [2022, 30],
         [2023, 35],
         [2024, 20]
     ]);

     var options = {
         title: 'Crescimento Anual',
         hAxis: { title: 'Ano' },
         vAxis: { title: 'Crescimento (%)' },
         legend: { position: 'bottom' }
     };

     var chart = new google.visualization.LineChart(document.getElementById('line_chart'));
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

function drawDonutChart() {
    // Dados do gráfico
    var data = google.visualization.arrayToDataTable([
        ['Categoria', 'Valor'],
        ['Categoria A', 30],
        ['Categoria B', 70],
        ['Categoria C', 40],
        ['Categoria D', 60]
    ]);

    // Opções do gráfico
    var options = {
        title: 'Distribuição de Categorias',
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