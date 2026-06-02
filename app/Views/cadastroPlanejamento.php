<?= $this->extend('main')?>;

<?php $this->section('content');?>

<div class="page-shell mt-3 mb-5">
    <div class="page-hero">
        <div>
            <h2><?= (isset($item)) ? "Atualização de planejamento" : "Previsão de custo"; ?></h2>
            <p>Planeje equipe, consumos e custo previsto antes do evento.</p>
        </div>
        <a href="<?= base_url('/operacional/planejamento/lista');?>" type="button" class="btn btn-outline-secondary">Lista de eventos</a>
    </div>

<div class="content-card p-3">
    <form class="row g-3" method="post" action="<?= url_to('cadastro.planejamento.store');?>" enctype="multipart/form-data">
        <?= csrf_field(); ?>
        <div class="col-12">
            <div class="content-card-soft d-flex flex-wrap align-items-center gap-2">
                <span class="badge text-bg-primary">1. Evento</span>
                <span class="badge text-bg-light">2. Equipe</span>
                <span class="badge text-bg-light">3. Consumos</span>
                <span class="badge text-bg-light">4. Custos</span>
                <span class="badge text-bg-light">5. Observações</span>
            </div>
        </div>
        
        <?php if (session()->has('error')) : ?>
          <div class="flash text-center alert alert-danger text-danger"><?php echo session()->get('error') ?? ''; ?></div>
        <?php endif; ?>
        <?php if (session()->has('success')) : ?>
          <div class="flash text-center alert alert-success text-success"><?php echo session()->get('success') ?? ''; ?></div>
        <?php endif; ?>
        <?php if(isset($item)):?>
        <input type="hidden" class="form-control" name="id" <?= isset($item) ? 'value="' . $item->id . '"' : '';?>>        
        <?php endif;?>
        <input type="hidden" class="form-control" name="flag" value="0">
        <div class="col-md-8">
            <label class="form-label">Nome do evento</label>
            <input type="text" class="form-control" name="nome" <?= isset($item) ? 'value="' . $item->nome . '"' : ''; ?> required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Data do evento</label>
            <input type="date" class="form-control" name="data" <?= isset($item) ? 'value="' . $item->data . '"' : ''; ?> required>
        </div>
        <div class="col-md-2">
            <?php if(isset($item)):?>
                <label for="inputState" class="form-label">Tipo de evento</label>
                <select id="inputState" class="form-select" name="tipo" required>
                    <option >Selecione...</option>
                    <option value="Social" <?php echo ($item->tipo == "Social")? "Selected": "" ;?> >Social</option>
                    <option value="Corporativo" <?php echo ($item->tipo == "Corporativo")? "Selected": "" ;?> >Corporativo</option> 
                    <option value="Entretenimento" <?php echo ($item->tipo == "Entretenimento")? "Selected": "" ;?> >Entretenimento</option>
                </select>
            <?php else:?>
                <label for="inputState" class="form-label">Tipo de evento</label>
                <select id="inputState" class="form-select" name="tipo" required>
                    <option selected></option>
                    <option value="Social">Social</option>
                    <option value="Corporativo">Corporativo</option> 
                    <option value="Entretenimento">Entretenimento</option>
                </select>
            <?php endif?>
        </div>                             
        <div class="form-section-title">Equipe operacional</div>
        <div class="col-md-1">
            <label class="form-label">Montagem</label>
            <input type="number" class="form-control" name="premontagem" <?= isset($item) ? 'value="' . $item->premontagem . '"' : ''; ?>>
        </div>
        <div class="col-md-2">
            <label class="form-label">Valor cachê</label>
            <input type="text" class="form-control money" name="cache_premontagem" <?= isset($item) ? 'value="' . $item->cache_premontagem . '"' : ''; ?>>
        </div>
        <div class="col-md-2">
            <label class="form-label">Total</label>
            <input type="text" class="form-control money" name="valor_premontagem" <?= isset($item) ? 'value="' . $item->valor_premontagem . '"' : ''; ?>>
        </div>
        <div class="col-md-1">
            <label class="form-label">Evento</label>
            <input type="number" class="form-control" name="operacional" <?= isset($item) ? 'value="' . $item->operacional . '"' : ''; ?>>
        </div>
        <div class="col-md-2">
            <label class="form-label">Valor cachê</label>
            <input type="text" class="form-control money" name="valor_operacional" <?= isset($item) ? 'value="' . $item->valor_operacional . '"' : ''; ?>>
        </div>
        <div class="col-md-2">
            <label class="form-label">Total</label>
            <input type="text" class="form-control money" name="super_op" <?= isset($item) ? 'value="' . $item->super_op . '"' : ''; ?>>            
        </div>  
        <div class="col-md-2">
            <label class="form-label">Total</label>
            <input type="text" class="form-control money" name="total_operacional" <?= isset($item) ? 'value="' . $item->total_operacional . '"' : ''; ?>>            
        </div> 
        <div class="col-md-3">
            <label class="form-label">Equipe manhã</label>
            <select class="form-select" name="equipe_manha">
                <option value="0" <?= (isset($item) && empty($item->equipe_manha)) ? 'selected' : ''; ?>>Não</option>
                <option value="1" <?= (isset($item) && (int)$item->equipe_manha === 1) ? 'selected' : ''; ?>>Sim</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Equipe noite</label>
            <select class="form-select" name="equipe_noite">
                <option value="0" <?= (isset($item) && empty($item->equipe_noite)) ? 'selected' : ''; ?>>Não</option>
                <option value="1" <?= (isset($item) && (int)$item->equipe_noite === 1) ? 'selected' : ''; ?>>Sim</option>
            </select>
        </div>
        
        <div class="form-section-title">Consumo</div>
        <div class="col-md-3">
            <label class="form-label">Valor Diesel</label>
            <input type="text" class="form-control money" name="valor_diesel" <?= isset($item) ? 'value="' . $item->valor_diesel . '"' : ''; ?>>
        </div>
        <div class="col-md-2">
            <label class="form-label">Diesel entrada</label>
            <input type="number" class="form-control" name="diesel_inicio" <?= isset($item) ? 'value="' . $item->diesel_inicio . '"' : ''; ?>>
        </div>
        <div class="col-md-2">
            <label class="form-label">Diesel saída</label>
            <input type="number" class="form-control" name="diesel_fim" <?= isset($item) ? 'value="' . $item->diesel_fim . '"' : ''; ?>>            
        </div>               
        <div class="col-md-2">
            <label class="form-label">Consumo (litros)</label>
            <input type="number" class="form-control" name="diesel_litros" <?= isset($item) ? 'value="' . $item->diesel_litros . '"' : ''; ?>>
        </div>
        <div class="col-md-3">
            <label class="form-label">Consumo (valor)</label>
            <input type="text" class="form-control money" name="diesel_total" <?= isset($item) ? 'value="' . $item->diesel_total . '"' : ''; ?>>
        </div>
        <div class="col-md-3">
            <label class="form-label">Valor água Cedae</label>
            <input type="text" class="form-control money" name="cedae_valor" <?= isset($item) ? 'value="' . $item->cedae_valor . '"' : ''; ?>>
        </div>
        <div class="col-md-2">
            <label class="form-label">Água Cedae entrada</label>
            <input type="number" class="form-control" name="cedae_inicio" <?= isset($item) ? 'value="' . $item->cedae_inicio . '"' : ''; ?>>
        </div>
        <div class="col-md-2">
            <label class="form-label">Água Cedae saída</label>
            <input type="number" class="form-control" name="cedae_fim"<?= isset($item) ? 'value="' . $item->cedae_fim . '"' : ''; ?>>            
        </div>               
        <div class="col-md-2">
            <label class="form-label">Consumo</label>
            <input type="number" class="form-control" name="cedae_consumo" <?= isset($item) ? 'value="' . $item->cedae_consumo . '"' : ''; ?>>
        </div>
        <div class="col-md-3">
            <label class="form-label">Consumo (valor)</label>
            <input type="text" class="form-control money" name="cedae_total"<?= isset($item) ? 'value="' . $item->cedae_total . '"' : ''; ?>>
        </div>
        <div class="col-md-3">
            <label class="form-label">Valor Light (KWH)</label>
            <input type="text" class="form-control money" name="light_valor" <?= isset($item) ? 'value="' . $item->light_valor . '"' : ''; ?>>
        </div>
        <div class="col-md-2">
            <label class="form-label">Light inicio</label>
            <input type="number" class="form-control" name="light_inicio" <?= isset($item) ? 'value="' . $item->light_inicio . '"' : ''; ?>>
        </div>
        <div class="col-md-2">
            <label class="form-label">Light fim</label>
            <input type="number" class="form-control" name="light_fim" <?= isset($item) ? 'value="' . $item->light_fim . '"' : ''; ?>>
        </div>
        <div class="col-md-2">
            <label class="form-label">Light consumo (KWH)</label>
            <input type="number" class="form-control" name="light_consumo" <?= isset($item) ? 'value="' . $item->light_consumo . '"' : ''; ?>>            
        </div>               
        <div class="col-md-3">
            <label class="form-label">Light Total</label>
            <input type="text" class="form-control money" name="light_total" <?= isset($item) ? 'value="' . $item->light_total . '"' : ''; ?>>
        </div>
        <div class="col-md-4">
            <label class="form-label">Água poço entrada</label>
            <input type="number" class="form-control" name="poco_inicio" <?= isset($item) ? 'value="' . ($item->poco_inicio ?? '') . '"' : ''; ?>>
        </div>
        <div class="col-md-4">
            <label class="form-label">Água poço saída</label>
            <input type="number" class="form-control" name="poco_fim" <?= isset($item) ? 'value="' . ($item->poco_fim ?? '') . '"' : ''; ?>>
        </div>
        <div class="col-md-4">
            <label class="form-label">Consumo poço (m³)</label>
            <input type="number" class="form-control" name="poco_consumo" readonly <?= isset($item) ? 'value="' . ($item->poco_consumo ?? '') . '"' : ''; ?>>
        </div>
        <div class="col-md-12 text-end">
            <label class="form-label">Total Consumo</label>
            <input type="text" class="form-control text-end money" name="total_consumo" <?= isset($item) ? 'value="' . $item->total_consumo . '"' : ''; ?>>
        </div>
        <div class="mb-12">
            <label for="exampleFormControlTextarea1" class="form-label">Observação</label>
            <textarea class="form-control" id="exampleFormControlTextarea1" rows="4" name="descricao" ><?= isset($item) ? $item->descricao : ''; ?></textarea>
        </div>        
        <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary "><?= (isset($item)? "Atualizar planejamento" : "Cadastrar previsão" )?></button>
        </div>
    </form>
</div>
</div>

  <script>
    $(document).ready(function(){

    //   Aplica mascara   
      $('input.money').mask('000.000.000.000.000,00', {reverse: true});    
      $('.phone').mask('(00)00000-0000', {reverse: false});

        // Adiciona o prefixo "R$ " automaticamente ao campo
        $('.money').on('focus', function() {
            if ($(this).val() === '') {
                // $(this).val('R$ ');
            }
        });
    
        // Função para limpar o valor e converter para número
        function cleanNumber(value) {
            if (!value) return 0; // Se estiver vazio, retorna 0
            value = value.replace('R$', '').replace(/\./g, '').replace(',', '.'); // Remove "R$", pontos e converte vírgula para ponto
            return parseFloat(value) || 0; // Converte para número ou retorna 0
        }

        // Função para calcular o total
        function calculateTotal() {
            // Pega os valores dos campos e converte para número
            const value = cleanNumber($('#value').val());
            const charge = cleanNumber($('#charge').val());
            const rent = cleanNumber($('#rent').val());
            const third = cleanNumber($('#third').val());

            // Soma os valores
            const total = value - charge - rent - third;

            // Atualiza o campo total com a máscara
            $('#total').val(total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }));
        }

        // Adiciona o evento 'input' aos campos de entrada
        $('.money').on('input', calculateTotal);

    
        function calcularConsumo() {
            // Reaproveita o mesmo motor de cálculo do cadastro operacional para manter consistência dos totais.
            // Diesel
            let dieselInicio = parseFloat($("input[name='diesel_inicio']").val()) || 0;
            let dieselFim = parseFloat($("input[name='diesel_fim']").val()) || 0;
            let valorDiesel = parseFloat($("input[name='valor_diesel']").val().replace(',', '.')) || 0;
            
            let dieselLitros = dieselInicio - dieselFim;
            let dieselValor = dieselLitros * valorDiesel;

            $("input[name='diesel_litros']").val(dieselLitros);
            $("input[name='diesel_total']").val(dieselValor.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
            //$("input[name='diesel_total']").val(dieselValor.toFixed(2));

            // Água Cedae
            let cedaeInicio = parseFloat($("input[name='cedae_inicio']").val()) || 0;
            let cedaeFim = parseFloat($("input[name='cedae_fim']").val()) || 0;
            let cedaeConsumo = cedaeFim -cedaeInicio;
            $("input[name='cedae_consumo']").val(cedaeConsumo);
            let cedaeValor = parseFloat($("input[name='cedae_valor']").val().replace(',','.')) || 0;
            let cedaeTotal = cedaeConsumo * cedaeValor;
            $("input[name='cedae_total']").val(cedaeTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));

            // Light
            let lightvalor = parseFloat($("input[name=light_valor]").val().replace(',','.')) || 0;
            let lightIncio = parseFloat($("input[name=light_inicio]").val()) || 0;
            let lightFim = parseFloat($("input[name=light_fim]").val()) || 0;

            let lightConsumo = lightFim - lightIncio;
            $("input[name=light_consumo]").val(lightConsumo);
            let lightTotal = lightvalor * lightConsumo;
            $("input[name=light_total]").val(lightTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));

            // Água poço
            let pocoInicio = parseFloat($("input[name='poco_inicio']").val()) || 0;
            let pocoFim = parseFloat($("input[name='poco_fim']").val()) || 0;
            // Poço entra só como consumo (m³), sem custo monetário.
            let pocoConsumo = pocoFim - pocoInicio;
            $("input[name='poco_consumo']").val(pocoConsumo);

            // operacional

            //supervisor -> Pré-montagem
            let supervisor = $("input[name=premontagem]").val() || 0 ;
            let valorSupervisor = parseFloat($("input[name=cache_premontagem]").val()) || 0 ;
            let totalSupervisor = supervisor * valorSupervisor; 

            let operacional = $("input[name=operacional]").val() || 0;
            let valorOperacional = parseFloat($("input[name=valor_operacional]").val()) || 0;
            let totalOperacional = operacional * valorOperacional;

            let soma = totalSupervisor + totalOperacional;
            
            $("input[name=valor_premontagem]").val(totalSupervisor.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
            
            $("input[name=super_op]").val(totalOperacional.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));

            $("input[name=total_operacional]").val(soma.toLocaleString('pt-BR', { minimumFractionDigits: 2 }))

            let totalCusto = dieselValor + cedaeTotal + lightTotal + totalSupervisor + totalOperacional; 
            $("input[name=total_consumo]").val(totalCusto.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
        }   

        $(
            "input[name='diesel_inicio'], input[name='diesel_fim'], input[name='valor_diesel'], " +
            "input[name='cedae_inicio'], input[name='cedae_fim'], input[name='cedae_valor'], " +
            "input[name='light_inicio'], input[name='light_fim'], input[name='light_valor'], " +
            "input[name='poco_inicio'], input[name='poco_fim'], " +
            "input[name='premontagem'], input[name='cache_premontagem'], " +
            "input[name='operacional'], input[name='valor_operacional']"
        ).on("input", calcularConsumo);
        calcularConsumo();
    
        function calcularTempo(entrada, saida, resultado) {
            // Trata virada de dia para eventos que atravessam meia-noite.
            let inicio = $("input[name='" + entrada + "']").val();
            let fim = $("input[name='" + saida + "']").val();

            if (inicio && fim) {
                let [h1, m1] = inicio.split(":").map(Number);
                let [h2, m2] = fim.split(":").map(Number);

                let minutosInicio = h1 * 60 + m1;
                let minutosFim = h2 * 60 + m2;
                let diferenca = minutosFim - minutosInicio;

                if (diferenca < 0) diferenca += 24 * 60; // Corrigir se passar da meia-noite

                let horas = Math.floor(diferenca / 60);
                let minutos = diferenca % 60;

                let tempoFormatado = `${String(horas).padStart(2, "0")}:${String(minutos).padStart(2, "0")}`;
                $("input[name='" + resultado + "']").val(tempoFormatado);
            }
        }

        $("input").on("input", function () {
            calcularTempo("liga_g1", "desliga_g1", "tempo_g1");
            calcularTempo("liga_g2", "desliga_g2", "tempo_g2");
        });
    
        function calcularHoraExtra() {
        // Hora extra prevista acima de 5h de jornada.
        let inicio = $("input[name='hora_inicio']").val();
        let fim = $("input[name='hora_fim']").val();

        if (inicio && fim) {
            let [h1, m1] = inicio.split(":").map(Number);
            let [h2, m2] = fim.split(":").map(Number);

            let minutosInicio = h1 * 60 + m1;
            let minutosFim = h2 * 60 + m2;

            if (minutosFim < minutosInicio) {
                minutosFim += 24 * 60; // Se passou da meia-noite, ajusta o cálculo
            }

            let minutosTrabalhados = minutosFim - minutosInicio;
            let horasTrabalhadas = minutosTrabalhados / 60;
            let horaExtra = Math.max(0, horasTrabalhadas - 5); // Se for menor que 5h, retorna 0

            // Arredonda para o número inteiro mais próximo
            $("input[name='hora_extra']").val(Math.round(horaExtra)); 
        }
        }

        $("input").on("input", calcularHoraExtra);

    });

  </script>
 
<?php $this->endSection('content');?>
