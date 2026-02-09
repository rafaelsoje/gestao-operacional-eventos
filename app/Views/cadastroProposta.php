<?= $this->extend('main')?>;

<?php $this->section('content');?>

<div class="container mt-3">
    <div class="d-flex align-items-end justify-content-end mb-3">
        <a href="<?= base_url('/proposta');?>" type="button" class="btn btn-success">Lista de Propostas</a>
    </div>
</div>

<div class="container border pt-2 pb-2">
    <h3 class="text-center mb-3 border p-1 bg-white">Cadastro de Propostas</h3>
    <form class="row g-3" method="post" action="<?= url_to('cadastro.proposta.store');?>" enctype="multipart/form-data">
        <?= csrf_field(); ?>
        
        <?php if (session()->has('error')) : ?>
          <div class="flash text-center alert alert-danger text-danger"><?php echo session()->get('error') ?? ''; ?></div>
        <?php endif; ?>
        <?php if (session()->has('success')) : ?>
          <div class="flash text-center alert alert-success text-success"><?php echo session()->get('success') ?? ''; ?></div>
        <?php endif; ?>

        <div class="col-md-2">
            <label class="form-label">ID do evento</label>
            <input type="number" class="form-control" name="excolab_id" required>
        </div>
        <div class="col-md-2">
            <label for="inputState" class="form-label">Tipo de evento</label>
            <select id="inputState" class="form-select" name="type" required>
                <option selected>Selecione...</option>
                <option value="Social">Social</option>
                <option value="Corporativo">Corporativo</option>                
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Data início</label>
            <input type="date" class="form-control" name="start_date" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Data término</label>
            <input type="date" class="form-control" name="end_date">
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label">Nome do evento</label>
            <input type="text" class="form-control" name="event_name">
        </div>
        <div class="col-12 col-md-6">
            <label for="inputAddress2" class="form-label">Nome Cliente</label>
            <input type="text" class="form-control" id="inputAddress2" name="client_name">
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email">
        </div>
        <div class="col-md-6">
            <label class="form-label">Telefone</label>
            <input type="text" class="form-control phone" name="phone">            
        </div>               
        <div class="col-md-2">
            <label class="form-label">Valor da proposta</label>
            <input type="text" id="value" class="form-control money" name="value" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Custo Operacional</label>
            <input type="text" id="charge" class="form-control money" name="charge">
        </div>
        <div class="col-md-2">
            <label class="form-label">Aluguel de Materiais</label>
            <input type="text" id="rent" class="form-control money" name="rent">
        </div>
        <div class="col-md-2">
            <label class="form-label">Materiais de Terceiros</label>
            <input type="text" id="third" class="form-control money" name="third">
        </div>
        <div class="col-md-4">
            <label class="form-label">Saldo</label>
            <input type="text" id="total" class="form-control money" name="saldo" readonly>
        </div>
        <div class="col-12 text-center">
            <div class="form-check form-check-inline">
                <input class="form-check-input bg-success" type="radio" id="inlineRadio1" name="status" value="1">
                <label class="form-check-label text-success" for="inlineRadio1">Proposta aceita</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input bg-warning" type="radio" name="status" id="inlineRadio2" value="2">
                <label class="form-check-label text-warning" for="inlineRadio2">Proposta em análise</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input bg-danger" type="radio" name="status" id="inlineRadio3" value="3">
                <label class="form-check-label text-danger" for="inlineRadio3">Proposta recusada</label>
            </div>

        </div>
        <div class="mb-12">
            <label for="exampleFormControlTextarea1" class="form-label">Descrição / Observação</label>
            <textarea class="form-control" id="exampleFormControlTextarea1" rows="4" name="description" ></textarea>
        </div>
        <div class="col-md-12">
            <label for="inputCity" class="form-label">Anexar Proposta</label>
            <input type="file" class="form-control" id="inputCity" name="file">
        </div>
        <div class="col-md-12">
            <label for="inputCity" class="form-label">Anexar Rider</label>
            <input type="file" class="form-control" id="inputCity" name="rider">
        </div>
        <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Cadastrar Proposta</button>
        </div>
    </form>
</div>

  <!-- Script para aplicar a máscara -->
  <script>
    $(document).ready(function(){
      $('input.money').mask('000.000.000.000.000,00', {reverse: true});
    });

    $(document).ready(function(){
      $('.phone').mask('(00)00000-0000', {reverse: false});
    });

    // Adiciona o prefixo "R$ " automaticamente ao campo
    $('.money').on('focus', function() {
        if ($(this).val() === '') {
            // $(this).val('R$ ');
        }
    });

    $(document).ready(function () {
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

    });    
  </script>
 
<?php $this->endSection('content');?>
