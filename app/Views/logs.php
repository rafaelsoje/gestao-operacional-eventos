<?= $this->extend('main')?>;

<?php $this->section('content');?>

<!-- Tabela -->
<?php $user = session()->get('user');?>
<div class="container-fluid">
    <?php if (session()->has('error')) : ?>
        <div class="flash text-center alert alert-danger text-danger"><?php echo session()->get('error') ?? ''; ?></div>
    <?php endif; ?>
    
    <?php if (session()->has('success')) : ?>
        <div class="flash text-center alert alert-success text-success"><?php echo session()->get('success') ?? ''; ?></div>
    <?php endif; ?>
  <?php if(isset($logs) && !empty($logs)): ?>
    <div class="table-responsive">
    <table id="example" class="display table table-striped dt-stack" style="width:100%">
      <thead>
        <tr>
        <th>ID</th>
          <th>Ação</th>
          <th>Usuário</th>
          <th>Descrição</th>          
        </tr>
      </thead>
      <tbody>  
        <?php foreach ($logs as $log) :?>
          <tr>
          <td><?= $log->id;?></td>
            <td><?= $log->title;?></td>
            <td><?= esc($log->user_name ?? '---'); ?></td>
            <td><?= esc($log->clean_description ?? $log->description ?? ''); ?></td>
          <?php endforeach ;?>
      </tbody>
    </table>
    </div>
  <?php else:?>

  <?php endif;?>  
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
    $(document).ready(function() {
      $('#example').DataTable();
    });


    $('#example').DataTable({
        "order": [[0, "desc"]], // Ordenar pela coluna 'Data' (primeira coluna) de forma decrescente
        "paging": true,       // Ativar/desativar paginação
        "searching": true,    // Ativar/desativar o campo de busca
        "ordering": true,     // Ativar/desativar a ordenação das colunas
        "info": true,         // Exibir informações (por exemplo, 'Mostrando 1 a 10 de 50 registros')
        "pageLength": 25,      // Número de linhas por página
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
