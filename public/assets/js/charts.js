//  CHARTS.JS


// Selecionar o elemento canvas onde o gráfico será desenhado
var ctx = document.getElementById('meuGrafico').getContext('2d');

// Criar uma nova instância de Chart e definir os dados
var meuGrafico = new Chart(ctx, {
    type: 'bar',  // Tipo de gráfico: 'line', 'bar', 'pie', etc.
    data: {
        labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho'],  // Labels do eixo x
        datasets: [{
            label: 'Vendas em 2024',  // Legenda do gráfico
            data: [100, 200, 150, 300, 250, 400],  // Dados para o gráfico
            backgroundColor: 'rgba(75, 192, 192, 0.2)',  // Cor de fundo (área sob a linha)
            borderColor: 'rgba(75, 192, 192, 1)',  // Cor da linha
            borderWidth: 2  // Espessura da linha
        }]
    },
    options: {
        responsive: true,  // O gráfico se ajusta ao tamanho da tela
        scales: {
            y: {
                beginAtZero: true  // O eixo Y começa no zero
            }
        }
    }
});



// Selecionar o elemento canvas onde o gráfico será desenhado
var ctx = document.getElementById('meuGrafico2').getContext('2d');

// Criar uma nova instância de Chart e definir os dados
var meuGrafico = new Chart(ctx, {
    type: 'line',  // Tipo de gráfico: 'line', 'bar', 'pie', etc.
    data: {
        labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho'],  // Labels do eixo x
        datasets: [{
            label: 'Vendas em 2024',  // Legenda do gráfico
            data: [100, 200, 150, 300, 250, 400],  // Dados para o gráfico
            backgroundColor: 'rgba(75, 192, 192, 0.2)',  // Cor de fundo (área sob a linha)
            borderColor: 'rgba(75, 192, 192, 1)',  // Cor da linha
            borderWidth: 2  // Espessura da linha
        }]
    },
    options: {
        responsive: true,  // O gráfico se ajusta ao tamanho da tela
        scales: {
            y: {
                beginAtZero: true  // O eixo Y começa no zero
            }
        }
    }
});