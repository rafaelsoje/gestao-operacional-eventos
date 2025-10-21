<?= $this->extend('main')?>;

<?php $this->section('content');?>

<!-- <nav class="mt-3" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url();?>">Home</a></li>            
            <li class="breadcrumb-item active" aria-current="page">Propostas</li>
        </ol>
</nav> -->

<div class="container-fluid mt-3">
        <?php if (session()->has('error')) : ?>
          <div class="flash text-center alert alert-danger text-danger"><?php echo session()->get('error') ?? ''; ?></div>
        <?php endif; ?>
        <?php if (session()->has('success')) : ?>
          <div class="flash text-center alert alert-success text-success"><?php echo session()->get('success') ?? ''; ?></div>
        <?php endif; ?>
  
        <div class="d-flex justify-content-between">
    <div class="col-4">        
        <!-- <select id="inputState" class="form-select ">
            <option selected>Todas as propostas</option>
            <option>Proposta aceita</option>
            <option>Proposta recusada</option>                
        </select>         -->
    </div>
    <div class="d-flex align-items-end justify-content-end mb-3">
        <a href="<?= base_url('/cadastro/proposta');?>" type="button" class="btn btn-success">+ Cadastrar Proposta</a>
    </div>
  </div>    
     <!-- Tabela Striped
     <table class="table table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Nome</th>
          <th>E-mail</th>
          <th>Cargo</th>
          <th>Ação</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Ana Silva</td>
          <td>ana.silva@example.com</td>
          <td>Gerente</td>
          <td>
            <a href="#" type="button" class="btn btn-info text-white"><i class="fas fa-eye"></i></a>
            <a href="" type="button" class="btn btn-warning text-white"><i class="fas fa-pen"></i></a>
            <a href="" type="button" class="btn btn-danger"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
        <tr>
          <td>2</td>
          <td>Bruno Lima</td>
          <td>bruno.lima@example.com</td>
          <td>Desenvolvedor</td>
          <td>
            <a href="#" type="button" class="btn btn-info text-white"><i class="fas fa-eye"></i></a>
            <a href="" type="button" class="btn btn-warning text-white"><i class="fas fa-pen"></i></a>
            <a href="" type="button" class="btn btn-danger"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
        <tr>
          <td>3</td>
          <td>Carlos Souza</td>
          <td>carlos.souza@example.com</td>
          <td>Designer</td>
          <td>
            <a href="#" type="button" class="btn btn-info text-white"><i class="fas fa-eye"></i></a>
            <a href="" type="button" class="btn btn-warning text-white"><i class="fas fa-pen"></i></a>
            <a href="" type="button" class="btn btn-danger"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
        <tr>
          <td>4</td>
          <td>Daniela Castro</td>
          <td>daniela.castro@example.com</td>
          <td>Analista</td>
          <td>
            <a href="#" type="button" class="btn btn-info text-white"><i class="fas fa-eye"></i></a>
            <a href="" type="button" class="btn btn-warning text-white"><i class="fas fa-pen"></i></a>
            <a href="" type="button" class="btn btn-danger"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
      </tbody>
    </table>
</div> -->


<!-- Button trigger modal
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
  Launch demo modal
</button>-->

<!-- Tabela -->

<div class="container-fluid">
  <?php if($propostas):?>
    <table id="propostas" class="display table table-striped" style="width:100%">
      <thead>
        <tr>
        <!-- <th>ID</th> -->
          <th>Data</th>
          <th>Nome</th>
          <th>Valor Total</th>
          <th>Saldo</th>
          <th>Status</th>
          <th>Arquivo</th>
          <th>Ação</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($propostas as $proposta):?>
          <?php 
            $data = implode('/', array_reverse(explode('-',$proposta->start_date)))
          ?>
          <?php
            if($proposta->status == 1){
              $status = "Proposta aceita";
            }elseif($proposta->status == 2){
              $status = "Em análise";
            }else{
              $status = "Recusada";
            }
          ?>
        <tr>
        <!-- <td><?= $proposta->id;?></td> -->
          <td><?= $data;?></td>
          <!-- <td><?= $proposta->start_date;?></td> -->
          <td><?= $proposta->event_name;?></td>
          <td class="text-end"><?= "R$ " . number_format($proposta->value, 2, ',', '.');?></td>
          <td class="text-end"><?= "R$ " . number_format($proposta->saldo, 2, ',', '.');?></td>
          <td><?= $status;?></td>
          <td>
            <a  href="<?= base_url('/uploads' . "/" . $proposta->start_date . "/" . $proposta->file );?>" target="_blank"><?= (!empty($proposta->file)? 'Proposta' : '');?></a>
            <?php if(!empty($proposta->rider)):?>/ <a  href="<?= base_url('/uploads' . "/" . $proposta->start_date . "/" . $proposta->rider );?>" target="_blank">Rider</a> <?php endif;?>
          </td>
          <td class="d-flex justify-content-center" >
            <a href="#" type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#i<?= $proposta->id;?>"><i class="fas fa-eye"></i></a>
            <a href="<?= base_url('/update/proposta/' . $proposta->id);?>" type="button" class="btn btn-warning text-white me-2 ms-2"><i class="fas fa-pen"></i></a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#e<?= $proposta->id;?>"><i class="fas fa-trash"></i></button>
            <!-- <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(1, 'Ana Silva')">Excluir</button> -->
          </td>
        </tr> 
        <!-- Modal Info-->
          <div class="modal fade" id="i<?= $proposta->id?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="exampleModalLabel"><?= $proposta->event_name;?></h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div><strong>Excolab id: </strong><?= $proposta->excolab_id;?></div>
                  <div><strong>Tipo de evento: </strong><?= $proposta->type;?></div>
                  <div><strong>Evento: </strong><?= $proposta->event_name;?></div>
                  <div><strong>Data: </strong><?= $data;?></div>
                  <div><strong>Descrição / Observação: </strong><br/><?= $proposta->description;?></div>
                  <div><strong>Proposta: </strong><br/><a  href="<?= base_url('/uploads' . "/" . $proposta->start_date . "/" . $proposta->file );?>" target="_blank"><?= (!empty($proposta->file)? $proposta->file : '');?></a></div>
                  
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
                </div>
              </div>
            </div>
          </div>


          <!-- Modal Exclusão-->
          <div class="modal fade" id="e<?= $proposta->id?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="exampleModalLabel">Confirmação de exclusão!</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <p>Quer mesmo excluir essa proposta?</p>
                  <strong> <?= $proposta->event_name;?></strong>
                  <strong> <?= $data;?></strong>                  
                </div>
                <div class="modal-footer">
                  <form method="post" action="<?= base_url('/delete/proposta');?>">
                    <input type="hidden" name="id" value="<?= $proposta->id;?>" />
                    <input type="hidden" name="file" value="<?= $proposta->file;?>" />
                    <input type="hidden" name="date" value="<?= $proposta->start_date;?>" />
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" data-bs-dismiss="modal">Excluir</button>
                    <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
                  </form>
                </div>
              </div>
            </div>
          </div>

        <?php endforeach;?>       
      </tbody>
    </table>    
    <?php else:?>
      <h4>Nenhuma proposta cadastrada!</h4>
    <?php endif;?>  
</div>


<!-- Modal de Confirmação -->
<div class="modal fade" id="modalExclusao" tabindex="-1" aria-labelledby="modalExclusaoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalExclusaoLabel">Confirmar Exclusão</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Tem certeza de que deseja excluir o usuário <strong id="nomeUsuario"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">Excluir</button>
      </div>
    </div>
  </div>
</div>


<script>

    let usuarioId; // Variável para armazenar o ID do usuário a ser excluído

    function confirmarExclusao(id, nome) {
      // Armazena o ID do usuário
      usuarioId = id;
      
      // Atualiza o texto do modal com o nome do usuário
      document.getElementById('nomeUsuario').textContent = nome;

      // Exibe o modal
      var myModal = new bootstrap.Modal(document.getElementById('modalExclusao'));
      myModal.show();
    }

    // Quando o botão "Excluir" no modal for clicado
    document.getElementById('btnConfirmarExclusao').addEventListener('click', function() {
      // Aqui você pode adicionar sua lógica de exclusão, como uma requisição AJAX para deletar o usuário
      alert('Usuário de ID ' + usuarioId + ' excluído com sucesso!');

      // Fechar o modal
      var modalElement = document.getElementById('modalExclusao');
      var modalInstance = bootstrap.Modal.getInstance(modalElement);
      modalInstance.hide();
    });
  </script>

  <!-- Script para ativar DataTables -->
  <script>

    // Adiciona um método de ordenação para o formato dd/mm/aaaa
    jQuery.extend(jQuery.fn.dataTable.ext.type.order, {
        "date-dd-mm-yyyy-pre": function(d) {
            if (d === null || d === "") {
                return 0;
            }
            var parts = d.split("/");
            return new Date(parts[2], parts[1] - 1, parts[0]).getTime();
        }
    });



    $(document).ready(function() {
      $('#propostas').DataTable();
    });
    $('#propostas').DataTable({

      "columnDefs": [
        {
            "type": "date-dd-mm-yyyy", // Define o tipo da coluna como o plugin customizado
            "targets": 0              // Altere para o índice da coluna de datas
        }
    ],
    "order": [[0, "desc"]], // Ordenar pela coluna 'Data' (primeira coluna) de forma decrescente
    "paging": true,       // Ativar/desativar paginação
    "searching": true,    // Ativar/desativar o campo de busca
    "ordering": true,     // Ativar/desativar a ordenação das colunas
    "info": true,         // Exibir informações (por exemplo, 'Mostrando 1 a 10 de 50 registros')
    "pageLength": 25,     // Número de linhas por página    
    "language": {
        // "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/Portuguese-Brasil.json" // Tradução para PT-BR
        "sEmptyTable": "Nenhum dado disponível na tabela",
            "sInfo": "Mostrando _START_ até _END_ de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
            "sInfoFiltered": "(filtrado de _MAX_ registros no total)",
            "sInfoPostFix": "",
            "sInfoThousands": ".",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sLoadingRecords": "Carregando...",
            "sProcessing": "Processando...",
            "sSearch": "Pesquisar:",
            "sZeroRecords": "Nenhum registro encontrado",
            "oPaginate": {
                "sFirst": "Primeiro",
                "sLast": "Último",
                "sNext": "Próximo",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Ordenar colunas de forma ascendente",
                "sSortDescending": ": Ordenar colunas de forma descendente"
            }
    }
    
    });



    

  </script>
<?php $this->endSection('content');?>