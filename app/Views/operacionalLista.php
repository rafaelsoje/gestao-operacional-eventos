<?= $this->extend('main')?>;

<?php $this->section('content');?>
<?php helper('format'); ?>

<!-- <nav class="mt-3" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url();?>">Home</a></li>            
            <li class="breadcrumb-item active" aria-current="page">Propostas</li>
        </ol>
</nav> -->

<div class="page-shell mt-3">
        <?php if (session()->has('error')) : ?>
          <div class="flash text-center alert alert-danger text-danger"><?php echo session()->get('error') ?? ''; ?></div>
        <?php endif; ?>
        <?php if (session()->has('success')) : ?>
          <div class="flash text-center alert alert-success text-success"><?php echo session()->get('success') ?? ''; ?></div>
        <?php endif; ?>
  
  <div class="page-hero">
    <div>
      <h2>Lista de eventos</h2>
      <p>Consulte custos, consumos e registros operacionais dos eventos.</p>
    </div>
      <a href="<?= base_url('/cadastro/operacional');?>" type="button" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Cadastrar evento
      </a>
  </div>
     <!-- Tabela Striped
     <div class="table-responsive">
     <table class="table table-striped dt-stack">
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
            <a href="#" type="button" class="btn btn-info text-white"><i class="bi bi-eye"></i></a>
            <a href="" type="button" class="btn btn-warning text-white"><i class="bi bi-pencil"></i></a>
            <a href="" type="button" class="btn btn-danger"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
        <tr>
          <td>2</td>
          <td>Bruno Lima</td>
          <td>bruno.lima@example.com</td>
          <td>Desenvolvedor</td>
          <td>
            <a href="#" type="button" class="btn btn-info text-white"><i class="bi bi-eye"></i></a>
            <a href="" type="button" class="btn btn-warning text-white"><i class="bi bi-pencil"></i></a>
            <a href="" type="button" class="btn btn-danger"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
        <tr>
          <td>3</td>
          <td>Carlos Souza</td>
          <td>carlos.souza@example.com</td>
          <td>Designer</td>
          <td>
            <a href="#" type="button" class="btn btn-info text-white"><i class="bi bi-eye"></i></a>
            <a href="" type="button" class="btn btn-warning text-white"><i class="bi bi-pencil"></i></a>
            <a href="" type="button" class="btn btn-danger"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
        <tr>
          <td>4</td>
          <td>Daniela Castro</td>
          <td>daniela.castro@example.com</td>
          <td>Analista</td>
          <td>
            <a href="#" type="button" class="btn btn-info text-white"><i class="bi bi-eye"></i></a>
            <a href="" type="button" class="btn btn-warning text-white"><i class="bi bi-pencil"></i></a>
            <a href="" type="button" class="btn btn-danger"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
      </tbody>
     </table>
     </div>
</div> -->


<!-- Button trigger modal
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
  Launch demo modal
</button>-->

<!-- Tabela -->

<div class="content-card p-3">
  <?php if($data):?>
    <div class="content-card-header">
      <div>
        <h5>Eventos cadastrados</h5>
        <small><?= count($data); ?> registro(s) encontrados</small>
      </div>
    </div>
    <div class="table-responsive">
    <table id="propostas" class="display table table-striped table-hover dt-stack app-table" style="width:100%">
      <thead>
        <tr>
        <!-- <th>ID</th> -->
          <th>Data</th>
          <th>Nome</th>
          <th>Tipo de evento</th>          
          <th>Operacional</th>
          <th>Gerador</th>
          <th>Cedae</th>
          <th>Poço</th>
          <th>Light</th>
          <!-- <th>KWH G1</th> -->
          <!-- <th>KWH G2</th> -->
          <th>Custo do Evento</th>
          <th>Ação</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($data as $item):?>
          <?php 
            $data = br_date($item->data);
            $operacional =  $item->valor_premontagem + $item->super_op;
            $kwhG1 = (float)$item->kwh_saida_g1 - (float)$item->kwh_entrada_g1;
            $kwhG2 = (float)$item->kwh_saida_g2 - (float)$item->kwh_entrada_g2;
          ?>          
        <tr>
          <td data-label="Data"><?= $data;?></td>
          <td data-label="Nome"><?= esc((string)$item->nome);?></td>
          <td data-label="Tipo de evento"><?= esc((string)$item->tipo);?></td>
          <td data-label="Operacional" class=""><?= br_money($operacional);?></td>
          <td data-label="Gerador" class=""><?= br_money($item->diesel_total);?></td>
          <td data-label="Cedae" class=""><?= br_money($item->cedae_total);?></td>
          <td data-label="Poço" class=""><?= br_number((float)($item->poco_consumo ?? 0), 0); ?> m³</td>
          <td data-label="Light" class=""><?= br_money($item->light_total);?></td>
          <!-- <td class=""><?= br_number($kwhG1, 0);?><?= $kwhG1 ? ' kWh' : ''; ?></td> -->
          <!-- <td class=""><?= br_number($kwhG2, 0);?><?= $kwhG2 ? ' kWh' : ''; ?></td> -->
          <td data-label="Custo do Evento" class=""><?= br_money($item->total_consumo);?></td>
          <td data-label="Ação" class="d-flex justify-content-center action-cell" >
            <?php if(!empty($item->descricao)):?>
              <a href="#" type="button" class="btn btn-info text-white btn-icon" data-bs-toggle="modal" data-bs-target="#i<?= $item->id;?>"><i class="bi bi-chat-left-text"></i></a>
              <?php else:?>
                <a href="#" type="button" class="btn btn-secondary text-white btn-icon" data-bs-target="#i<?= $item->id;?>"><i class="bi bi-chat-left-text"></i></a>
            
              <?php endif;?>

              <a href="<?= base_url('/update/operacional/'.$item->id);?>" type="button" class="btn btn-warning text-white btn-icon"><i class="bi bi-pencil"></i></a>
            <button type="button" class="btn btn-danger btn-icon" data-bs-toggle="modal" data-bs-target="#e<?= $item->id;?>"><i class="bi bi-trash"></i></button>
            <!-- <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(1, 'Ana Silva')">Excluir</button> -->
          </td>
        </tr> 

        <!-- Modal Info-->
          <div class="modal fade" id="i<?= $item->id?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="exampleModalLabel"><?= esc((string)$item->nome);?></h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <?= nl2br(esc((string)($item->descricao ?? '')));?>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
                </div>
              </div>
            </div>
          </div>


          <!-- Modal Exclusão-->
          <div class="modal fade" id="e<?= $item->id?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="exampleModalLabel">Confirmação de exclusão!</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <p>Quer mesmo excluir essa informação?</p>
                  <strong> <?= esc((string)$item->nome);?></strong>
                  <strong> <?= esc((string)$data);?></strong>                  
                </div>
                <div class="modal-footer">
                  <form method="post" action="<?= base_url('/delete/operacional');?>">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="id" value="<?= (int)$item->id;?>" />
                    <input type="hidden" name="nome" value="<?= esc((string)$item->nome);?>" />
                    
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
    </div>
    <?php else:?>
      <div class="alert alert-info mb-0">Nenhum dado cadastrado.</div>
    <?php endif;?>  
</div>
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

    // DataTables não entende dd/mm/aaaa nativamente; plugin abaixo normaliza para timestamp.
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
      $('#propostas').DataTable({

      "columnDefs": [
        {
            "type": "date-dd-mm-yyyy", // Define o tipo da coluna como o plugin customizado
            "targets": 0              // Altere para o índice da coluna de datas
        }
    ],
    // Mantém os eventos mais recentes no topo por padrão.
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
    });



    

  </script>
<?php $this->endSection('content');?>
