<?= $this->extend('main')?>;

<?php $this->section('content');?>

<div class="container mt-3">
    <div class="d-flex align-items-end justify-content-end mb-3">
        <a href="<?= base_url('/operacional/lista');?>" type="button" class="btn btn-success">Lista Operacional</a>
    </div>
</div>

<div class="container border pt-2 pb-2 mb-5">
    <h3 class="text-center mb-3 border p-1 bg-white">Atualização de dado Operacional</h3>
    <form class="row g-3" method="post" action="<?= url_to('update.operacional.store');?>" enctype="multipart/form-data">
        <?= csrf_field(); ?>
        
        <?php if (session()->has('error')) : ?>
          <div class="flash text-center alert alert-danger text-danger"><?php echo session()->get('error') ?? ''; ?></div>
        <?php endif; ?>
        <?php if (session()->has('success')) : ?>
          <div class="flash text-center alert alert-success text-success"><?php echo session()->get('success') ?? ''; ?></div>
        <?php endif; ?>

        <input type="hidden" class="form-control" name="id" value="<?= $item->id ;?>" required>
        <div class="col-md-12">
            <label class="form-label">Nome do evento</label>
            <input type="text" class="form-control" name="nome" value="<?= $item->nome;?>" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Data do evento</label>
            <input type="date" class="form-control" name="data" value="<?= $item->data;?>" required>
        </div>
        <div class="col-md-2">
            <label for="inputState" class="form-label">Tipo de evento</label>
            <select id="inputState" class="form-select" name="tipo" required>
                <option >Selecione...</option>
                <option value="Social" <?php echo ($item->tipo == "Social")? "Selected": "" ;?> >Social</option>
                <option value="Corporativo" <?php echo ($item->tipo == "Corporativo")? "Selected": "" ;?> >Corporativo</option> 
                <option value="Entretenimento" <?php echo ($item->tipo == "Entretenimento")? "Selected": "" ;?> >Entretenimento</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Hora início</label>
            <input type="time" class="form-control" name="hora_inicio" value="<?= $item->hora_inicio ;?>" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Hora término</label>
            <input type="time" class="form-control" name="hora_fim" value="<?= $item->hora_fim;?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Hora extra</label>
            <input type="number" class="form-control" name="hora_extra" value="<?= $item->hora_extra;?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Temperatura entrada</label>
            <input type="number" step="0.1" class="form-control" name="temp_inicio" value="<?= $item->temp_inicio;?>" >
        </div>
        <div class="col-md-2">
            <label class="form-label">Temperatura saída</label>
            <input type="number" step="0.1" class="form-control" name="temp_fim" value="<?= $item->temp_fim;?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Numero de fornecedores</label>
            <input type="number" class="form-control" name="qtd_fornecedor" value="<?= $item->qtd_fornecedor;?>">
        </div>        
        <div class="col-md-4">
            <label class="form-label">Monitoramento sonoro</label>
            <input type="number" class="form-control" name="monitor_sonoro" value="<?= $item->monitor_sonoro;?>" >
        </div>       
        <div class="p-1 fs-5">Gerador</div>
        <div class="col-12 col-md-4 col-lg-2">
            <label class="form-label">Gerador 1 ligado</label>
            <input type="time" class="form-control" name="liga_g1" value="<?= $item->liga_g1;?>" >
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label for="inputAddress2" class="form-label">Gerador 1 desligado</label>
            <input type="time" class="form-control" name="desliga_g1" value="<?= $item->desliga_g1;?>" >
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label for="inputAddress2" class="form-label">Tempo gerador 1</label>
            <input type="time" class="form-control" name="tempo_g1" value="<?= $item->tempo_g1;?>" >
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label class="form-label">Leitura G1 (hora)</label>
            <input type="time" class="form-control" name="leitura_g1" value="<?= $item->leitura_g1;?>" >
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label class="form-label">KWH entrada G1</label>
            <input type="number" step="1" inputmode="numeric" class="form-control" name="kwh_entrada_g1" value="<?= (int)$item->kwh_entrada_g1;?>" >
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label class="form-label">KWH saída G1</label>
            <input type="number" step="1" inputmode="numeric" class="form-control" name="kwh_saida_g1" value="<?= (int)$item->kwh_saida_g1;?>" >
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label class="form-label">Consumo KWH G1</label>
            <input type="number" step="1" inputmode="numeric" class="form-control" name="kwh_consumo_g1" readonly>
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label class="form-label">Gerador 2 ligado</label>
            <input type="time" class="form-control" name="liga_g2" value="<?= $item->liga_g2;?>">
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label for="inputAddress2" class="form-label">Gerador 2 desligado</label>
            <input type="time" class="form-control" name="desliga_g2"value="<?= $item->desliga_g2;?>" >
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label for="inputAddress2" class="form-label">Tempo gerador 2</label>
            <input type="time" class="form-control" name="tempo_g2" value="<?= $item->tempo_g2;?>">
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label class="form-label">Leitura G2 (hora)</label>
            <input type="time" class="form-control" name="leitura_g2" value="<?= $item->leitura_g2;?>">
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label class="form-label">KWH entrada G2</label>
            <input type="number" step="1" inputmode="numeric" class="form-control" name="kwh_entrada_g2" value="<?= (int)$item->kwh_entrada_g2;?>">
        </div>
        <div class="col-2">
            <label class="form-label">KWH saída G2</label>
            <input type="number" step="1" inputmode="numeric" class="form-control" name="kwh_saida_g2" value="<?= (int)$item->kwh_saida_g2;?>">
        </div>
        <div class="col-2">
            <label class="form-label">Consumo KWH G2</label>
            <input type="number" step="1" inputmode="numeric" class="form-control" name="kwh_consumo_g2" readonly>
        </div>
        <div class="p-1 fs-5">Equipe operacional</div>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label">Supervisor</label>
            <input type="number" class="form-control" name="supervisor" value="<?= $item->supervisor;?>">
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <label for="inputAddress2" class="form-label">Segurança</label>
            <input type="number" class="form-control" name="seguranca" value="<?= $item->seguranca;?>">
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label">Bombeiro</label>
            <input type="number" class="form-control" name="bombeiro" value="<?= $item->bombeiro;?>">
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <label for="inputAddress2" class="form-label">Limpeza</label>
            <input type="number" class="form-control" name="limpeza" value="<?= $item->limpeza;?>">
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label">Equipe manhã</label>
            <?php $equipeManha = $item->equipe_manha ?? 0; ?>
            <select class="form-select" name="equipe_manha">
                <option value="0" <?= empty($equipeManha) ? 'selected' : ''; ?>>Não</option>
                <option value="1" <?= (int)$equipeManha === 1 ? 'selected' : ''; ?>>Sim</option>
            </select>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label">Equipe noite</label>
            <?php $equipeNoite = $item->equipe_noite ?? 0; ?>
            <select class="form-select" name="equipe_noite">
                <option value="0" <?= empty($equipeNoite) ? 'selected' : ''; ?>>Não</option>
                <option value="1" <?= (int)$equipeNoite === 1 ? 'selected' : ''; ?>>Sim</option>
            </select>
        </div>
        <div class="p-1 fs-5">Público</div>
        <div class="col-md-4">
            <label class="form-label">Público final</label>
            <input type="number" class="form-control" name="publico_final"value="<?= $item->publico_final;?>" >
        </div>
        <div class="col-md-4">
            <label class="form-label">Público final tribuna</label>
            <input type="number" class="form-control" name="publico_tribuna" value="<?= $item->publico_tribuna;?>">            
        </div>               
        <div class="col-md-4">
            <label class="form-label">Público final saída</label>
            <input type="number" class="form-control" name="publico_saida" value="<?= $item->publico_saida;?>">
        </div>
        <div class="p-1 fs-5">Consumo</div>
        <div class="col-md-3">
            <label class="form-label">Valor Diesel</label>
            <input type="text" class="form-control money" name="valor_diesel" value="<?= $item->valor_diesel;?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Diesel entrada</label>
            <input type="number" class="form-control" name="diesel_inicio" value="<?= $item->diesel_inicio;?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Diesel saída</label>
            <input type="number" class="form-control" name="diesel_fim" value="<?= $item->diesel_fim;?>">            
        </div>               
        <div class="col-md-2">
            <label class="form-label">Consumo (litros)</label>
            <input type="number" class="form-control" name="diesel_litros" value="<?= $item->diesel_litros;?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Consumo (valor)</label>
            <input type="text" class="form-control money" name="diesel_total" value="<?= $item->diesel_total;?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Água Cedae entrada</label>
            <input type="number" class="form-control" name="cedae_inicio" value="<?= $item->cedae_inicio;?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Água Cedae saída</label>
            <input type="number" class="form-control" name="cedae_fim" value="<?= $item->temp_fim;?>">            
        </div>               
        <div class="col-md-4">
            <label class="form-label">Consumo</label>
            <input type="numcenterber" class="form-control" name="cedae_consumo" value="<?= $item->cedae_consumo;?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Água poço entrada</label>
            <input type="number" class="form-control" name="poco_inicio" value="<?= $item->poco_inicio;?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Água poço saída</label>
            <input type="number" class="form-control" name="poco_fim" value="<?= $item->temp_fim;?>">            
        </div>               
        <div class="col-md-4">
            <label class="form-label">Consumo</label>
            <input type="numcenterber" class="form-control" name="poco_consumo" value="<?= $item->poco_consumo;?>">
        </div>
        <div class="mb-12">
            <label for="exampleFormControlTextarea1" class="form-label">Observação</label>
            <textarea class="form-control" id="exampleFormControlTextarea1" rows="4" name="descricao"><?= $item->descricao;?></textarea>
        </div>
        <!-- <div class="col-md-12">
            <label for="inputCity" class="form-label">Anexar Proposta</label>
            <input type="file" class="form-control" id="inputCity" name="file">
        </div>
        <div class="col-md-12">
            <label for="inputCity" class="form-label">Anexar Rider</label>
            <input type="file" class="form-control" id="inputCity" name="rider">
        </div>-->
        <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Atualizar dados operaionais</button>
        </div>
    </form>
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
            let cedaeConsumo = cedaeInicio - cedaeFim;
            $("input[name='cedae_consumo']").val(cedaeConsumo);

            // Água Poço
            let pocoInicio = parseFloat($("input[name='poco_inicio']").val()) || 0;
            let pocoFim = parseFloat($("input[name='poco_fim']").val()) || 0;
            let pocoConsumo = pocoInicio - pocoFim;
            $("input[name='poco_consumo']").val(pocoConsumo);
        }   

        $("input").on("input", calcularConsumo);

        function calcularKwh() {
            const entradaG1 = parseFloat($("input[name='kwh_entrada_g1']").val()) || 0;
            const saidaG1 = parseFloat($("input[name='kwh_saida_g1']").val()) || 0;
            const consumoG1 = saidaG1 - entradaG1;
            $("input[name='kwh_consumo_g1']").val(consumoG1.toFixed(0));

            const entradaG2 = parseFloat($("input[name='kwh_entrada_g2']").val()) || 0;
            const saidaG2 = parseFloat($("input[name='kwh_saida_g2']").val()) || 0;
            const consumoG2 = saidaG2 - entradaG2;
            $("input[name='kwh_consumo_g2']").val(consumoG2.toFixed(0));
        }

        $("input[name='kwh_entrada_g1'], input[name='kwh_saida_g1'], input[name='kwh_entrada_g2'], input[name='kwh_saida_g2']").on("input", calcularKwh);
        calcularKwh();

        function calcularTempo(entrada, saida, resultado) {
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
