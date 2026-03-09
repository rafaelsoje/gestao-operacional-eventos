<?= $this->extend('main')?>;

<?php $this->section('content');?>

<div class="container mt-3">
    <div class="d-flex align-items-end justify-content-end mb-3">
        <a href="<?= base_url('/operacional/lista');?>" type="button" class="btn btn-success">Lista de eventos</a>
    </div>
</div>

<div class="container border pt-2 pb-2 mb-5">
    <h3 class="text-center mb-3 border p-1 bg-white"><?= (isset($item))? "Atualização de cadastro" : "Cadastro de Operacional";?></h3>
    <form class="row g-3" method="post" action="<?= url_to('cadastro.operacional.store');?>" enctype="multipart/form-data">
        <?= csrf_field(); ?>
        
        <?php if (session()->has('error')) : ?>
          <div class="flash text-center alert alert-danger text-danger"><?php echo session()->get('error') ?? ''; ?></div>
        <?php endif; ?>
        <?php if (session()->has('success')) : ?>
          <div class="flash text-center alert alert-success text-success"><?php echo session()->get('success') ?? ''; ?></div>
        <?php endif; ?>
        <?php if(isset($item)):?>
        <input type="hidden" class="form-control" name="id" <?= isset($item) ? 'value="' . $item->id . '"' : '';?>>                      
        <?php endif;?>
        <input type="hidden" class="form-control" name="flag" value="1">
        <div class="col-md-12">
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
                    <option selected>Selecione...</option>
                    <option value="Social">Social</option>
                    <option value="Corporativo">Corporativo</option> 
                    <option value="Entretenimento">Entretenimento</option>
                </select>
            <?php endif?>
        </div>
        <div class="col-md-2">
            <label class="form-label">Hora início</label>
            <input type="time" class="form-control" name="hora_inicio" <?= isset($item) ? 'value="' . $item->hora_inicio . '"' : ''; ?> required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Hora término</label>
            <input type="time" class="form-control" name="hora_fim" <?= isset($item) ? 'value="' . $item->hora_fim . '"' : ''; ?> required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Hora extra</label>
            <input type="number" class="form-control" name="hora_extra" <?= isset($item) ? 'value="' . $item->hora_extra . '"' : ''; ?>>
        </div>
        <div class="col-md-4">
            <label class="form-label">Temperatura entrada</label>
            <input type="number" step="0.1" class="form-control" name="temp_inicio" <?= isset($item) ? 'value="' . $item->temp_inicio . '"' : ''; ?>>
        </div>
        <div class="col-md-4">
            <label class="form-label">Temperatura saída</label>
            <input type="number" step="0.1" class="form-control" name="temp_fim" <?= isset($item) ? 'value="' . $item->temp_fim . '"' : ''; ?>>
        </div>
        <div class="col-md-4">
            <label class="form-label">Numero de fornecedores</label>
            <input type="number" class="form-control" name="qtd_fornecedor" <?= isset($item) ? 'value="' . $item->qtd_fornecedor . '"' : ''; ?>>
        </div>        
        <div class="col-md-3">
            <label class="form-label">Ruído Tribuna C</label>
            <input type="number" step="0.1" class="form-control" name="ruido_tribuna_c" <?= isset($item) ? 'value="' . ($item->ruido_tribuna_c ?? '') . '"' : ''; ?>>
        </div>
        <div class="col-md-3">
            <label class="form-label">Ruído Pista de Cavalo</label>
            <input type="number" step="0.1" class="form-control" name="ruido_pista_cavalo" <?= isset($item) ? 'value="' . ($item->ruido_pista_cavalo ?? '') . '"' : ''; ?>>
        </div>
        <div class="col-md-3">
            <label class="form-label">Ruído Corredor JB</label>
            <input type="number" step="0.1" class="form-control" name="ruido_corredor_jb" <?= isset($item) ? 'value="' . ($item->ruido_corredor_jb ?? '') . '"' : ''; ?>>
        </div>
        <div class="col-md-3">
            <label class="form-label">Média ruído</label>
            <?php
                $ruidoMediaInicial = '';
                if (isset($item)) {
                    if (isset($item->ruido_media) && $item->ruido_media !== null && $item->ruido_media !== '') {
                        $ruidoMediaInicial = $item->ruido_media;
                    } elseif (isset($item->monitor_sonoro) && $item->monitor_sonoro !== null && $item->monitor_sonoro !== '') {
                        $ruidoMediaInicial = $item->monitor_sonoro;
                    }
                }
            ?>
            <input type="number" step="0.01" class="form-control" name="ruido_media" readonly <?= $ruidoMediaInicial !== '' ? 'value="' . $ruidoMediaInicial . '"' : ''; ?>>
        </div>       
        <div class="p-1 fs-5">Gerador 1</div>
        <div class="col-12 col-md-4">
            <label class="form-label">Gerador 1 ligado</label>
            <input type="time" class="form-control" name="liga_g1" <?= isset($item) ? 'value="' . $item->liga_g1 . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label for="inputAddress2" class="form-label">Gerador 1 desligado</label>
            <input type="time" class="form-control" name="desliga_g1" <?= isset($item) ? 'value="' . $item->desliga_g1 . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label for="inputAddress2" class="form-label">Tempo gerador 1</label>
            <input type="time" class="form-control" name="tempo_g1" <?= isset($item) ? 'value="' . $item->tempo_g1 . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">Hora gerador entrada G1</label>
            <input type="text" maxlength="7" inputmode="numeric" class="form-control campo-hora-gerador" name="leitura_g1" placeholder="0000:00" <?= isset($item) ? 'value="' . $item->leitura_g1 . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">Hora gerador saída G1</label>
            <input type="text" maxlength="7" inputmode="numeric" class="form-control campo-hora-gerador" name="leitura_saida_g1" placeholder="0000:00" <?= isset($item) ? 'value="' . ($item->leitura_saida_g1 ?? '') . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">Horas de uso G1</label>
            <input type="text" class="form-control" name="diferenca_horas_g1" readonly>
        </div>
        <div class="w-100"></div>
        <div class="col-12 col-md-4">
            <label class="form-label">KWH entrada G1</label>
            <input type="number" step="1" inputmode="numeric" class="form-control" name="kwh_entrada_g1" <?= isset($item) ? 'value="' . (int)$item->kwh_entrada_g1 . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">KWH saída G1</label>
            <input type="number" step="1" inputmode="numeric" class="form-control" name="kwh_saida_g1" <?= isset($item) ? 'value="' . (int)$item->kwh_saida_g1 . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">Consumo KWH G1</label>
            <input type="number" step="1" inputmode="numeric" class="form-control" name="kwh_consumo_g1" readonly>
        </div>
        <div class="p-1 fs-5">Gerador 2</div>
        <div class="col-12 col-md-4">
            <label class="form-label">Gerador 2 ligado</label>
            <input type="time" class="form-control" name="liga_g2" <?= isset($item) ? 'value="' . $item->liga_g2 . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label for="inputAddress2" class="form-label">Gerador 2 desligado</label>
            <input type="time" class="form-control" name="desliga_g2" <?= isset($item) ? 'value="' . $item->desliga_g2 . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label for="inputAddress2" class="form-label">Tempo gerador 2</label>
            <input type="time" class="form-control" name="tempo_g2" <?= isset($item) ? 'value="' . $item->tempo_g2 . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">Hora gerador entrada G2</label>
            <input type="text" maxlength="7" inputmode="numeric" class="form-control campo-hora-gerador" name="leitura_g2" placeholder="0000:00" <?= isset($item) ? 'value="' . $item->leitura_g2 . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">Hora gerador saída G2</label>
            <input type="text" maxlength="7" inputmode="numeric" class="form-control campo-hora-gerador" name="leitura_saida_g2" placeholder="0000:00" <?= isset($item) ? 'value="' . ($item->leitura_saida_g2 ?? '') . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">Horas de uso G2</label>
            <input type="text" class="form-control" name="diferenca_horas_g2" readonly>
        </div>
        <div class="w-100"></div>
        <div class="col-12 col-md-4">
            <label class="form-label">KWH entrada G2</label>
            <input type="number" step="1" inputmode="numeric" class="form-control" name="kwh_entrada_g2" <?= isset($item) ? 'value="' . (int)$item->kwh_entrada_g2 . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">KWH saída G2</label>
            <input type="number" step="1" inputmode="numeric" class="form-control" name="kwh_saida_g2" <?= isset($item) ? 'value="' . (int)$item->kwh_saida_g2 . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">Consumo KWH G2</label>
            <input type="number" step="1" inputmode="numeric" class="form-control" name="kwh_consumo_g2" readonly>
        </div>
        <div class="p-1 fs-5">Público</div>
        <div class="col-md-3">
            <label class="form-label">Público final</label>
            <input type="number" class="form-control" name="publico_final" <?= isset($item) ? 'value="' . $item->publico_final . '"' : ''; ?>>
        </div>
        <div class="col-md-3">
            <label class="form-label">Público final tribuna</label>
            <input type="number" class="form-control" name="publico_tribuna" <?= isset($item) ? 'value="' . $item->publico_tribuna . '"' : ''; ?>>            
        </div>               
        <div class="col-md-3">
            <label class="form-label">Público final saída</label>
            <input type="number" class="form-control" name="publico_saida" <?= isset($item) ? 'value="' . $item->publico_saida . '"' : ''; ?>>
        </div>
        <div class="col-md-3">
            <label class="form-label">Público total</label>
            <?php
                $publicoTotalInicial = (int)($item->publico_final ?? 0) + (int)($item->publico_tribuna ?? 0) + (int)($item->publico_saida ?? 0);
            ?>
            <input type="number" class="form-control" name="publico_total_calc" value="<?= isset($item) ? $publicoTotalInicial : ''; ?>" readonly>
        </div>
        <div class="p-1 fs-5">Equipe operacional</div>        
        <div class="col-12 col-md-4">
            <label for="inputAddress2" class="form-label">Segurança</label>
            <input type="number" class="form-control" name="seguranca" <?= isset($item) ? 'value="' . $item->seguranca . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">Bombeiro</label>
            <input type="number" class="form-control" name="bombeiro" <?= isset($item) ? 'value="' . $item->bombeiro . '"' : ''; ?>>
        </div>
        <div class="col-12 col-md-4">
            <label for="inputAddress2" class="form-label">Limpeza</label>
            <input type="number" class="form-control" name="limpeza" <?= isset($item) ? 'value="' . $item->limpeza . '"' : ''; ?>>
        </div> 
        <div class="w-100"></div>
        <div class="col-12 col-md-6">
            <label class="form-label">Equipe manhã</label>
            <?php
                $equipeManhaRaw = isset($item) ? (string)($item->equipe_manha ?? '') : '';
                $equipeManhaArr = array_filter(array_map('trim', explode(',', $equipeManhaRaw)));
            ?>
            <div class="border rounded bg-white p-2">
                <small class="text-muted d-block mb-1">Selecione quem participou</small>
                <div class="d-flex flex-wrap gap-3">
                    <div class="form-check form-check-inline m-0">
                        <input class="form-check-input" type="checkbox" name="equipe_manha[]" value="Helder" id="manhaHelder" <?= in_array('Helder', $equipeManhaArr, true) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="manhaHelder">Helder</label>
                    </div>
                    <div class="form-check form-check-inline m-0">
                        <input class="form-check-input" type="checkbox" name="equipe_manha[]" value="Nathan" id="manhaNathan" <?= in_array('Nathan', $equipeManhaArr, true) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="manhaNathan">Nathan</label>
                    </div>
                    <div class="form-check form-check-inline m-0">
                        <input class="form-check-input" type="checkbox" name="equipe_manha[]" value="Anderson" id="manhaAnderson" <?= in_array('Anderson', $equipeManhaArr, true) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="manhaAnderson">Anderson</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label">Equipe noite</label>
            <?php
                $equipeNoiteRaw = isset($item) ? (string)($item->equipe_noite ?? '') : '';
                $equipeNoiteArr = array_filter(array_map('trim', explode(',', $equipeNoiteRaw)));
            ?>
            <div class="border rounded bg-white p-2">
                <small class="text-muted d-block mb-1">Selecione quem participou</small>
                <div class="d-flex flex-wrap gap-3">
                    <div class="form-check form-check-inline m-0">
                        <input class="form-check-input" type="checkbox" name="equipe_noite[]" value="Helder" id="noiteHelder" <?= in_array('Helder', $equipeNoiteArr, true) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="noiteHelder">Helder</label>
                    </div>
                    <div class="form-check form-check-inline m-0">
                        <input class="form-check-input" type="checkbox" name="equipe_noite[]" value="Nathan" id="noiteNathan" <?= in_array('Nathan', $equipeNoiteArr, true) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="noiteNathan">Nathan</label>
                    </div>
                    <div class="form-check form-check-inline m-0">
                        <input class="form-check-input" type="checkbox" name="equipe_noite[]" value="Anderson" id="noiteAnderson" <?= in_array('Anderson', $equipeNoiteArr, true) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="noiteAnderson">Anderson</label>
                    </div>
                </div>
            </div>
        </div>
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

        <div class="p-1 fs-5">Consumo</div>        
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
            <input type="number" class="form-control" name="poco_inicio" <?= isset($item) ? 'value="' . $item->poco_inicio . '"' : ''; ?>>
        </div>
        <div class="col-md-4">
            <label class="form-label">Água poço saída</label>
            <input type="number" class="form-control" name="poco_fim" <?= isset($item) ? 'value="' . $item->poco_fim . '"' : ''; ?>>
        </div>
        <div class="col-md-4">
            <label class="form-label">Consumo</label>
            <input type="number" class="form-control" name="poco_consumo" readonly <?= isset($item) ? 'value="' . $item->poco_consumo . '"' : ''; ?>>
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
            <button type="submit" class="btn btn-primary"><?= (isset($item)? "Atualizar cadastro operacional" : "Cadastrar dados operaionais" )?></button>
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

            // Água Poço
            let pocoInicio = parseFloat($("input[name='poco_inicio']").val()) || 0;
            let pocoFim = parseFloat($("input[name='poco_fim']").val()) || 0;
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

        function calcularPublicoTotal() {
            const publicoFinal = parseInt($("input[name='publico_final']").val(), 10) || 0;
            const publicoTribuna = parseInt($("input[name='publico_tribuna']").val(), 10) || 0;
            const publicoSaida = parseInt($("input[name='publico_saida']").val(), 10) || 0;
            const publicoTotal = publicoFinal + publicoTribuna + publicoSaida;
            $("input[name='publico_total_calc']").val(publicoTotal);
        }

        $("input[name='publico_final'], input[name='publico_tribuna'], input[name='publico_saida']").on("input", calcularPublicoTotal);
        calcularPublicoTotal();

        function calcularMediaRuido() {
            const fields = ["ruido_tribuna_c", "ruido_pista_cavalo", "ruido_corredor_jb"];
            const values = fields
                .map(function (name) {
                    const raw = $("input[name='" + name + "']").val();
                    if (raw === undefined || raw === null || raw === "") return null;
                    const parsed = parseFloat(String(raw).replace(",", "."));
                    return Number.isFinite(parsed) ? parsed : null;
                })
                .filter(function (v) { return v !== null; });

            if (!values.length) {
                $("input[name='ruido_media']").val("");
                return;
            }

            const media = values.reduce(function (acc, n) { return acc + n; }, 0) / values.length;
            $("input[name='ruido_media']").val(media.toFixed(2));
        }

        $("input[name='ruido_tribuna_c'], input[name='ruido_pista_cavalo'], input[name='ruido_corredor_jb']").on("input", calcularMediaRuido);
        calcularMediaRuido();

        function formatarHoraGerador(valor) {
            const digitos = (valor || "").replace(/\D/g, "").slice(0, 6);
            if (!digitos) return "";
            if (digitos.length <= 2) return digitos;
            let horas = parseInt(digitos.slice(0, -2), 10);
            let minutos = parseInt(digitos.slice(-2), 10);
            if (Number.isNaN(horas)) horas = 0;
            if (Number.isNaN(minutos)) minutos = 0;
            if (minutos > 59) {
                horas += Math.floor(minutos / 60);
                minutos = minutos % 60;
            }
            return `${String(horas)}:${String(minutos).padStart(2, "0")}`;
        }

        function parseHoraGeradorParaMinutos(valor) {
            if (!valor || !/^\d{1,4}:\d{2}$/.test(valor)) return null;
            const partes = valor.split(":");
            const horas = parseInt(partes[0], 10);
            const minutos = parseInt(partes[1], 10);
            if (Number.isNaN(horas) || Number.isNaN(minutos) || minutos > 59) return null;
            return (horas * 60) + minutos;
        }

        function formatarMinutosEmHora(minutos) {
            const horas = Math.floor(minutos / 60);
            const restoMinutos = minutos % 60;
            return `${String(horas).padStart(2, "0")}:${String(restoMinutos).padStart(2, "0")}`;
        }

        function atualizarDiferencaGerador(campoEntrada, campoSaida, campoDiferenca) {
            const entrada = $("input[name='" + campoEntrada + "']").val();
            const saida = $("input[name='" + campoSaida + "']").val();
            const $campoDiferenca = $("input[name='" + campoDiferenca + "']");

            const minutosEntrada = parseHoraGeradorParaMinutos(entrada);
            const minutosSaida = parseHoraGeradorParaMinutos(saida);

            if (minutosEntrada === null || minutosSaida === null || minutosSaida < minutosEntrada) {
                $campoDiferenca.val("");
                $campoDiferenca.css("background-color", "");
                return;
            }

            const diferenca = minutosSaida - minutosEntrada;
            $campoDiferenca.val(formatarMinutosEmHora(diferenca));
            $campoDiferenca.css("background-color", diferenca >= (200 * 60) ? "#f8d7da" : "");
        }

        $("input.campo-hora-gerador").on("input", function () {
            $(this).val(formatarHoraGerador($(this).val()));
            atualizarDiferencaGerador("leitura_g1", "leitura_saida_g1", "diferenca_horas_g1");
            atualizarDiferencaGerador("leitura_g2", "leitura_saida_g2", "diferenca_horas_g2");
        });

        $("input.campo-hora-gerador").each(function () {
            $(this).val(formatarHoraGerador($(this).val()));
        });
        atualizarDiferencaGerador("leitura_g1", "leitura_saida_g1", "diferenca_horas_g1");
        atualizarDiferencaGerador("leitura_g2", "leitura_saida_g2", "diferenca_horas_g2");

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

        $("input[name='liga_g1'], input[name='desliga_g1'], input[name='liga_g2'], input[name='desliga_g2']").on("input", function () {
            calcularTempo("liga_g1", "desliga_g1", "tempo_g1");
            calcularTempo("liga_g2", "desliga_g2", "tempo_g2");
        });
        calcularTempo("liga_g1", "desliga_g1", "tempo_g1");
        calcularTempo("liga_g2", "desliga_g2", "tempo_g2");
    
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

        $("input[name='hora_inicio'], input[name='hora_fim']").on("input", calcularHoraExtra);
        calcularHoraExtra();

    });

  </script>
 
<?php $this->endSection('content');?>
