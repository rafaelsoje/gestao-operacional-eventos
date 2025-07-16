<?= $this->extend('main')?>;

<?php $this->section('content');?>

<div class="container mt-3">
    <div class="d-flex align-items-end justify-content-end mb-3">
        <a href="<?= base_url('/proposta');?>" type="button" class="btn btn-success">Lista de Propostas</a>
    </div>
</div>

<div class="container border pt-2 pb-2">
    <h3 class="text-center mb-3 border p-1">Cadastro de Propostas</h3>
    <form class="row g-3">
        <div class="col-md-2">
            <label for="inputEmail4" class="form-label">ID do evento</label>
            <input type="text" class="form-control" id="inputEmail4">
        </div>
        <div class="col-md-2">
            <label for="inputState" class="form-label">Tipo de evento</label>
            <select id="inputState" class="form-select">
                <option selected>Selecione...</option>
                <option>Social</option>
                <option>Corporativo</option>                
            </select>
        </div>
        <div class="col-md-4">
            <label for="inputPassword4" class="form-label">Data início</label>
            <input type="date" class="form-control" id="inputPassword4">
        </div>
        <div class="col-md-4">
            <label for="inputPassword4" class="form-label">Data término</label>
            <input type="date" class="form-control" id="inputPassword4">
        </div>
        <div class="col-6">
            <label for="inputAddress" class="form-label">Nome do evento</label>
            <input type="text" class="form-control" id="inputAddress">
        </div>
        <div class="col-6">
            <label for="inputAddress2" class="form-label">Nome Cliente</label>
            <input type="text" class="form-control" id="inputAddress2">
        </div>
        <div class="col-md-6">
            <label for="inputCity" class="form-label">Email</label>
            <input type="email" class="form-control" id="inputCity">
        </div>
        <div class="col-md-3">
            <label for="inputState" class="form-label">Telefone</label>
            <input type="text" class="form-control" id="inputZip">
            <!-- <select id="inputState" class="form-select">
                <option selected>Choose...</option>
                <option>...</option>
            </select> -->
        </div>
        <div class="col-md-3">
            <label for="inputZip" class="form-label">Telefone 2</label>
            <input type="text" class="form-control" id="inputZip">
        </div>
        <!-- <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="gridCheck">
                <label class="form-check-label" for="gridCheck">
                Check me out
                </label>
            </div>
        </div> -->
        <div class="col-md-3">
            <label for="inputEmail4" class="form-label">Valor da proposta</label>
            <input type="text" class="form-control money">
        </div>
        <div class="col-md-3">
            <label for="inputEmail4" class="form-label">Custo Operacional</label>
            <input type="text" class="form-control money" >
        </div>
        <div class="col-md-3">
            <label for="inputEmail4" class="form-label">Aluguel de Materiais</label>
            <input type="text" class="form-control money" >
        </div>
        <div class="col-md-3">
            <label for="inputEmail4" class="form-label">Materiais de Terceiros</label>
            <input type="text" class="form-control money" >
        </div>
        <div class="col-12 text-center">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="option1">
                <label class="form-check-label text-success" for="inlineRadio1">Proposta aceita</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="option2">
                <label class="form-check-label text-warning" for="inlineRadio2">Proposta em análise</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio3" value="option3">
                <label class="form-check-label text-danger" for="inlineRadio3">Proposta recusada</label>
            </div>

        </div>
        <div class="mb-12">
            <label for="exampleFormControlTextarea1" class="form-label">Descrição / Observação</label>
            <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
        </div>
        <div class="col-md-12">
            <label for="inputCity" class="form-label">Anexar Proposta</label>
            <input type="file" class="form-control" id="inputCity">
        </div>
        <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Cadastrar Proposta</button>
        </div>
    </form>
</div>

  <!-- Script para aplicar a máscara -->
  <script>
    $(document).ready(function(){
      $('.money').mask('000.000.000.000.000,00', {reverse: true});
    });

    // Adiciona o prefixo "R$ " automaticamente ao campo
    $('.money').on('focus', function() {
        if ($(this).val() === '') {
          $(this).val('R$ ');
        }
    });
    
  </script>
 
<?php $this->endSection('content');?>