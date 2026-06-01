<?= $this->extend('main')?>;

<?php $this->section('content');?>

<!-- <nav class="mt-3" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url();?>">Home</a></li>            
            <li class="breadcrumb-item active" aria-current="page">Propostas</li>
        </ol>
</nav> -->



<div class="page-shell mt-3">
  <div class="page-hero">
    <div>
      <h2>Usuários</h2>
      <p>Gerencie acessos, permissões e atividade recente da equipe.</p>
    </div>
    <a href="<?= base_url('/cadastro/usuario');?>" type="button" class="btn btn-success">
      <i class="bi bi-person-plus"></i> Adicionar usuário
    </a>
  </div>


<!-- Tabela -->

<div class="content-card p-3">
    <?php if (session()->has('error')) : ?>
        <div class="flash text-center alert alert-danger text-danger"><?php echo session()->get('error') ?? ''; ?></div>
    <?php endif; ?>
    
    <?php if (session()->has('success')) : ?>
        <div class="flash text-center alert alert-success text-success"><?php echo session()->get('success') ?? ''; ?></div>
    <?php endif; ?>
  <?php if(isset($users) && !empty($users)): ?>
    <?php $superEmail = env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', 'rafaelsoje@gmail.com')); ?>
    <div class="content-card-header">
      <div>
        <h5>Usuários cadastrados</h5>
        <small><?= count($users); ?> usuário(s) listados</small>
      </div>
    </div>
    <div class="table-responsive">
    <table id="example" class="display table table-striped table-hover dt-stack app-table" style="width:100%">
      <thead>
        <tr>
          <th>Nome</th>
          <th>E-mail</th>
          <th>Nivel de acesso</th>
          <th>Última atividade</th>
          <th>Ação</th>
        </tr>
      </thead>
      <tbody>  
        <?php foreach ($users as $user) :?>
          <tr>
            <td><?= $user->name;?></td>
            <td><?=$user->email;?></td>                
              <?php
                $role = $user->role ?? (($user->admin ?? 0) == 1 ? 'admin' : 'user');
                if ($role === 'admin') {
                  $nivel = "Administrador";
                } else {
                  $nivel = "Usuário comum";
                }
              ?>
            <td><?=$nivel;?></td>
            <td>
              <?php $activityTs = (int)($user->last_activity_ts ?? 0); ?>
              <?php if (!empty($user->is_online_now)): ?>
                <span class="badge bg-success">Online agora</span>
              <?php elseif ($activityTs > 0): ?>
                <?= esc(date('d/m/Y H:i:s', $activityTs)); ?>
              <?php else: ?>
                <span class="text-muted">Sem atividade</span>
              <?php endif; ?>
            </td>
            <td class="d-flex justify-content-center action-cell" >
              
              <a href="#" type="button" class="btn btn-info text-white btn-icon" data-bs-toggle="modal" data-bs-target="#view<?=$user->id;?>"><i class="bi bi-eye"></i></a>

              <?php $session =  session()->get('user');?>
              <?php $sessionRole = $session->role ?? (($session->admin ?? 0) == 1 ? 'admin' : 'user'); ?>
              <?php if($user->email == $session->email):?>
                <a href="<?= base_url('/perfil');?>" type="button" class="btn btn-warning text-white btn-icon"><i class="bi bi-pencil"></i></a>
              <?php elseif($sessionRole === 'admin' || $sessionRole === 'superuser'):?>
                <a href="<?= base_url('/update/usuario/' . $user->id);?>" type="button" class="btn btn-warning text-white btn-icon"><i class="bi bi-pencil"></i></a>
              <?php else:?>
                <a href="#" type="button" class="btn btn-warning text-white btn-icon disabled"><i class="bi bi-pencil"></i></a>
              <?php endif;?>

                                         
              <?php if($user->email != $session->email && ($sessionRole === 'admin' || $sessionRole === 'superuser')):?>                       
                <button type="button" class="btn btn-danger text-white btn-icon" data-bs-toggle="modal" data-bs-target="#delete<?=$user->id;?>"><i class="bi bi-trash"></i></button>
              <?php elseif($user->email == $session->email):?>
                <button href="#" type="button" class="btn btn-secondary text-white btn-icon disabled" data-bs-toggle="modal" data-bs-target="#delete<?=$user->id;?>"><i class="bi bi-trash"></i></button>
              <?php else:?>
                <button href="#" type="button" class="btn btn-secondary text-white btn-icon disabled" data-bs-toggle="modal" data-bs-target="#delete<?=$user->id;?>"><i class="bi bi-trash"></i></button>
              <?php endif;?>
              <!-- <button type="button" class="btn btn-danger" onclick="confirmarExclusao(1, 'Ana Silva')"><i class="bi bi-trash"></i></button> -->
              <!-- <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(1, 'Ana Silva')">Excluir</button> -->
            </td>
          </tr>

              <!-- Modal view-->
              <div class="modal fade" id="view<?=$user->id;?>" tabindex="-1" aria-labelledby="viewUserLabel<?=$user->id;?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-light">
                      <h1 class="modal-title fs-5" id="viewUserLabel<?=$user->id;?>">Perfil do usuário</h1>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body p-4">
                      <div class="row align-items-center g-3">
                        <div class="col-12 col-md-4 text-center">
                          <img class="rounded-circle shadow-sm" src="<?= url_to('avatar.show', $user->avatar ?? 'avatar.svg');?>" style="width:120px;height:120px;object-fit:cover;" alt="Foto de <?=$user->name;?>"/>
                          <div class="mt-3">
                            <span class="badge bg-secondary"><?=$nivel;?></span>
                          </div>
                        </div>
                        <div class="col-12 col-md-8">
                          <div class="mb-2">
                            <small class="text-muted">Nome</small>
                            <div class="fw-semibold"><?=$user->name;?></div>
                          </div>
                          <div class="mb-2">
                            <small class="text-muted">E-mail</small>
                            <div class="fw-semibold"><?=$user->email;?></div>
                          </div>
                          <div class="mb-2">
                            <small class="text-muted">Nível de acesso</small>
                            <div class="fw-semibold"><?=$nivel;?></div>
                          </div>
                          <div class="mb-2">
                            <small class="text-muted">Última atividade</small>
                            <div class="fw-semibold">
                              <?php $activityTs = (int)($user->last_activity_ts ?? 0); ?>
                              <?php if (!empty($user->is_online_now)): ?>
                                Online agora
                              <?php elseif ($activityTs > 0): ?>
                                <?= esc(date('d/m/Y H:i:s', $activityTs)); ?>
                              <?php else: ?>
                                Sem atividade
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                  </div>
                </div>
              </div>



              <!-- Modal delete -->
              <div class="modal fade" id="delete<?=$user->id;?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h1 class="modal-title fs-5" id="exampleModalLabel">Confirmação de Exclusão</h1>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">                      
                      <?php if($user->email != $superEmail):?>                        
                      <p>Tem certeza de que deseja excluir  <strong><?=$user->name;?></strong>?</p>
                      <?php else:?>
                        <p>Você não tem permissão para excluir  <strong><?=$user->name;?></strong>?</p>
                      <?php endif;?>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                      <?php if($user->email != $superEmail):?>
                        <form method="post" action="<?= base_url('/delete/usuario');?>" class="d-inline">
                          <?= csrf_field(); ?>
                          <input type="hidden" name="id" value="<?= $user->id; ?>">
                          <button type="submit" class="btn btn-danger">Excluir</button>
                        </form>
                      <?php endif;?>
                    </div>
                  </div>
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
      $('#example').DataTable({
        "paging": true,       // Ativar/desativar paginação
        "searching": true,    // Ativar/desativar o campo de busca
        "ordering": true,     // Ativar/desativar a ordenação das colunas
        "info": true,         // Exibir informações (por exemplo, 'Mostrando 1 a 10 de 50 registros')
        "pageLength": 10,      // Número de linhas por página
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
