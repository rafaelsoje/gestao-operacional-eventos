<?= $this->extend('main') ?>;

<?php $this->section('content'); ?>
<?php helper('format'); ?>

<div class="container mt-3">
    <div class="container border pt-2 pb-2 bg-white">
        <h3 class="text-center mb-3 border p-1"><?= isset($item) ? 'Atualização de visita' : 'Cadastro de Visitas'; ?></h3>

        <?php if (session()->has('error')) : ?>
            <div class="alert alert-danger text-center"><?= esc((string)session()->get('error')); ?></div>
        <?php endif; ?>
        <?php if (session()->has('success')) : ?>
            <div class="alert alert-success text-center"><?= esc((string)session()->get('success')); ?></div>
        <?php endif; ?>

        <form class="row g-3" method="post" action="<?= url_to('visitas.store'); ?>">
            <?= csrf_field(); ?>
            <?php if (isset($item)) : ?>
                <input type="hidden" name="id" value="<?= (int)$item->id; ?>">
            <?php endif; ?>

            <div class="col-12 col-md-6">
                <label class="form-label">Data da visita</label>
                <input type="date" class="form-control" name="data_visita" value="<?= esc(old('data_visita', isset($item) ? (string)$item->data_visita : '')); ?>" required>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label">Tipo de visita</label>
                <input type="text" class="form-control" name="tipo_visita" value="<?= esc(old('tipo_visita', isset($item) ? (string)$item->tipo_visita : '')); ?>" required>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label">Responsável (EXC)</label>
                <input type="text" class="form-control" name="responsavel_exc" value="<?= esc(old('responsavel_exc', isset($item) ? (string)$item->responsavel_exc : '')); ?>" required>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label">Cliente</label>
                <input type="text" class="form-control" name="cliente" value="<?= esc(old('cliente', isset($item) ? (string)$item->cliente : '')); ?>" required>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label">Evento</label>
                <input type="text" class="form-control" name="evento" value="<?= esc(old('evento', isset($item) ? (string)$item->evento : '')); ?>" required>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label">Data do evento</label>
                <input type="date" class="form-control" name="data_evento" value="<?= esc(old('data_evento', isset($item) ? (string)$item->data_evento : '')); ?>" required>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label">Horário de início</label>
                <input type="time" class="form-control" name="horario_inicio" value="<?= esc(old('horario_inicio', isset($item) ? substr((string)$item->horario_inicio, 0, 5) : '')); ?>" required>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label">Horário de término</label>
                <input type="time" class="form-control" name="horario_termino" value="<?= esc(old('horario_termino', isset($item) ? substr((string)$item->horario_termino, 0, 5) : '')); ?>" required>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label">Público estimado</label>
                <input type="number" class="form-control" name="publico_estimado" min="0" step="1" value="<?= esc(old('publico_estimado', isset($item) ? (string)$item->publico_estimado : '')); ?>" required>
            </div>

            <div class="col-12">
                <label class="form-label">Objetivo da visita</label>
                <textarea class="form-control" name="objetivo_visita" rows="4" required><?= esc(old('objetivo_visita', isset($item) ? (string)$item->objetivo_visita : '')); ?></textarea>
            </div>

            <div class="col-12 d-flex justify-content-end">
                <?php if (isset($item)) : ?>
                    <a href="<?= base_url('visitas'); ?>" class="btn btn-outline-secondary me-2">Cancelar edição</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary"><?= isset($item) ? 'Atualizar visita' : 'Salvar visita'; ?></button>
            </div>
        </form>
    </div>

    <div class="container border pt-2 pb-2 bg-white mt-3">
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
                    <?php if (!empty($data ?? [])) : ?>
                        <?php foreach (($data ?? []) as $item) : ?>
                            <tr>
                                <td data-order="<?= esc((string)$item->data_visita); ?>"><?= esc(br_date((string)$item->data_visita)); ?></td>
                                <td><?= esc((string)$item->tipo_visita); ?></td>
                                <td><?= esc((string)$item->cliente); ?></td>
                                <td><?= esc((string)$item->evento); ?></td>
                                <td data-order="<?= esc((string)$item->data_evento); ?>"><?= esc(br_date((string)$item->data_evento)); ?></td>
                                <td><?= esc(substr((string)$item->horario_inicio, 0, 5)); ?></td>
                                <td><?= esc(substr((string)$item->horario_termino, 0, 5)); ?></td>
                                <td><?= esc((string)$item->publico_estimado); ?></td>
                                <td class="d-flex justify-content-center">
                                    <a href="#" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#view<?= (int)$item->id; ?>"><i class="bi bi-eye"></i></a>
                                    <a href="<?= base_url('/update/visita/' . (int)$item->id); ?>" class="btn btn-warning text-white me-2 ms-2"><i class="bi bi-pencil"></i></a>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete<?= (int)$item->id; ?>"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>

                            <div class="modal fade" id="view<?= (int)$item->id; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5">Detalhes da visita</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="mb-1"><strong>Data da visita:</strong> <?= esc(br_date((string)$item->data_visita)); ?></p>
                                            <p class="mb-1"><strong>Tipo:</strong> <?= esc((string)$item->tipo_visita); ?></p>
                                            <p class="mb-1"><strong>Responsável (EXC):</strong> <?= esc((string)$item->responsavel_exc); ?></p>
                                            <p class="mb-1"><strong>Cliente:</strong> <?= esc((string)$item->cliente); ?></p>
                                            <p class="mb-1"><strong>Evento:</strong> <?= esc((string)$item->evento); ?></p>
                                            <p class="mb-1"><strong>Data do evento:</strong> <?= esc(br_date((string)$item->data_evento)); ?></p>
                                            <p class="mb-1"><strong>Início:</strong> <?= esc(substr((string)$item->horario_inicio, 0, 5)); ?></p>
                                            <p class="mb-1"><strong>Término:</strong> <?= esc(substr((string)$item->horario_termino, 0, 5)); ?></p>
                                            <p class="mb-1"><strong>Público estimado:</strong> <?= esc((string)$item->publico_estimado); ?></p>
                                            <hr>
                                            <p class="mb-0"><strong>Objetivo da visita:</strong><br><?= nl2br(esc((string)$item->objetivo_visita)); ?></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="delete<?= (int)$item->id; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5">Confirmação de exclusão</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Quer mesmo excluir a visita do evento <strong><?= esc((string)$item->evento); ?></strong> do cliente <strong><?= esc((string)$item->cliente); ?></strong>?
                                        </div>
                                        <div class="modal-footer">
                                            <form method="post" action="<?= url_to('visitas.delete'); ?>">
                                                <?= csrf_field(); ?>
                                                <input type="hidden" name="id" value="<?= (int)$item->id; ?>">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-danger">Excluir</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#visitasTable').DataTable({
            "order": [[0, "desc"]],
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "pageLength": 10,
            "language": {
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

<?php $this->endSection('content'); ?>
