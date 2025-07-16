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

 function drawColumnChart() {
     var data = google.visualization.arrayToDataTable([
         ['Ano', 'Vendas', 'Despesas'],
         ['2019', 1000, 400],
         ['2020', 1170, 460],
         ['2021', 660, 1120],
         ['2022', 1030, 540]
     ]);

     var options = {
         title: 'Vendas e Despesas',
         hAxis: { title: 'Ano' },
         vAxis: { title: 'Valor'},
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

     var options = {
         title: 'Despesas Mensais',
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
         [2023, 35]
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
