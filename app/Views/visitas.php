<?= $this->extend('main') ?>;

<?php $this->section('content'); ?>
<?php helper('format'); ?>

<?php
$visitas = $data ?? [];
$timeValue = static function (mixed $value): string {
    return substr((string)$value, 0, 5);
};
?>

<div class="container-fluid mt-3 mb-5">
    <?php if (session()->has('error')) : ?>
        <div class="alert alert-danger text-center"><?= esc((string)session()->get('error')); ?></div>
    <?php endif; ?>
    <?php if (session()->has('success')) : ?>
        <div class="alert alert-success text-center"><?= esc((string)session()->get('success')); ?></div>
    <?php endif; ?>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h3 class="mb-0">Visitas</h3>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#visitaCreateModal">
            <i class="bi bi-plus-circle"></i> Cadastrar visita
        </button>
    </div>

    <div class="bg-white border p-3">
        <h5 class="mb-3">Visitas cadastradas</h5>
        <div class="table-responsive">
            <table id="visitasTable" class="display table table-striped table-hover dt-stack" style="width:100%">
                <thead>
                    <tr>
                        <th>Data visita</th>
                        <th>Tipo</th>
                        <th>Cliente</th>
                        <th>Evento</th>
                        <th>Data evento</th>
                        <th>Início</th>
                        <th>Término</th>
                        <th>Público</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($visitas as $visita) : ?>
                        <tr>
                            <td data-label="Data visita" data-order="<?= esc((string)$visita->data_visita); ?>"><?= esc(br_date((string)$visita->data_visita)); ?></td>
                            <td data-label="Tipo"><?= esc((string)$visita->tipo_visita); ?></td>
                            <td data-label="Cliente"><?= esc((string)$visita->cliente); ?></td>
                            <td data-label="Evento"><?= esc((string)$visita->evento); ?></td>
                            <td data-label="Data evento" data-order="<?= esc((string)$visita->data_evento); ?>"><?= esc(br_date((string)$visita->data_evento)); ?></td>
                            <td data-label="Início"><?= esc($timeValue($visita->horario_inicio)); ?></td>
                            <td data-label="Término"><?= esc($timeValue($visita->horario_termino)); ?></td>
                            <td data-label="Público"><?= esc((string)$visita->publico_estimado); ?></td>
                            <td data-label="Ação" class="d-flex justify-content-center">
                                <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#visitaView<?= (int)$visita->id; ?>">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-warning text-white me-2 ms-2" data-bs-toggle="modal" data-bs-target="#visitaEdit<?= (int)$visita->id; ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#visitaDelete<?= (int)$visita->id; ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="visitaCreateModal" tabindex="-1" aria-labelledby="visitaCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="visitaCreateModalLabel">Cadastrar visita</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="visitaCreateForm" method="post" action="<?= url_to('visitas.store'); ?>">
                    <?= csrf_field(); ?>
                    <?= view('visitaFormFields', ['item' => null]); ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="visitaCreateForm" class="btn btn-primary">Cadastrar visita</button>
            </div>
        </div>
    </div>
</div>

<?php foreach ($visitas as $visita) : ?>
    <?php $modalId = (int)$visita->id; ?>
    <div class="modal fade" id="visitaView<?= $modalId; ?>" tabindex="-1" aria-labelledby="visitaViewLabel<?= $modalId; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="visitaViewLabel<?= $modalId; ?>">Detalhes da visita</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Data da visita:</strong> <?= esc(br_date((string)$visita->data_visita)); ?></div>
                        <div class="col-md-6"><strong>Tipo:</strong> <?= esc((string)$visita->tipo_visita); ?></div>
                        <div class="col-md-6"><strong>Responsável (EXC):</strong> <?= esc((string)$visita->responsavel_exc); ?></div>
                        <div class="col-md-6"><strong>Cliente:</strong> <?= esc((string)$visita->cliente); ?></div>
                        <div class="col-md-6"><strong>Evento:</strong> <?= esc((string)$visita->evento); ?></div>
                        <div class="col-md-6"><strong>Data do evento:</strong> <?= esc(br_date((string)$visita->data_evento)); ?></div>
                        <div class="col-md-6"><strong>Início:</strong> <?= esc($timeValue($visita->horario_inicio)); ?></div>
                        <div class="col-md-6"><strong>Término:</strong> <?= esc($timeValue($visita->horario_termino)); ?></div>
                        <div class="col-md-6"><strong>Público estimado:</strong> <?= esc((string)$visita->publico_estimado); ?></div>
                        <div class="col-12"><strong>Objetivo da visita:</strong><br><?= nl2br(esc((string)$visita->objetivo_visita)); ?></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="visitaEdit<?= $modalId; ?>" tabindex="-1" aria-labelledby="visitaEditLabel<?= $modalId; ?>" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="visitaEditLabel<?= $modalId; ?>">Alterar visita</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form id="visitaEditForm<?= $modalId; ?>" method="post" action="<?= url_to('visitas.store'); ?>">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= $modalId; ?>">
                        <?= view('visitaFormFields', ['item' => $visita]); ?>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="visitaEditForm<?= $modalId; ?>" class="btn btn-primary">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="visitaDelete<?= $modalId; ?>" tabindex="-1" aria-labelledby="visitaDeleteLabel<?= $modalId; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="visitaDeleteLabel<?= $modalId; ?>">Confirmação de exclusão</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    Quer mesmo excluir a visita do evento <strong><?= esc((string)$visita->evento); ?></strong> do cliente <strong><?= esc((string)$visita->cliente); ?></strong>?
                </div>
                <div class="modal-footer">
                    <form method="post" action="<?= url_to('visitas.delete'); ?>">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= $modalId; ?>">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    $(document).ready(function () {
        $('#visitasTable').DataTable({
            order: [[0, "desc"]],
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            pageLength: 10,
            columnDefs: [
                {orderable: false, targets: 8}
            ],
            language: {
                sEmptyTable: "Nenhum dado disponível na tabela",
                sInfo: "Mostrando _START_ até _END_ de _TOTAL_ registros",
                sInfoEmpty: "Mostrando 0 até 0 de 0 registros",
                sInfoFiltered: "(filtrado de _MAX_ registros no total)",
                sInfoPostFix: "",
                sInfoThousands: ".",
                sLengthMenu: "Mostrar _MENU_ registros",
                sLoadingRecords: "Carregando...",
                sProcessing: "Processando...",
                sSearch: "Pesquisar:",
                sZeroRecords: "Nenhum registro encontrado",
                oPaginate: {
                    sFirst: "Primeiro",
                    sLast: "Último",
                    sNext: "Próximo",
                    sPrevious: "Anterior"
                },
                oAria: {
                    sSortAscending: ": Ordenar colunas de forma ascendente",
                    sSortDescending: ": Ordenar colunas de forma descendente"
                }
            }
        });
    });
</script>

<?php $this->endSection('content'); ?>
