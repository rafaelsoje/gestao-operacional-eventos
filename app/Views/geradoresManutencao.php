<?= $this->extend('main') ?>;
<?php $this->section('content'); ?>
<?php helper('format'); ?>

<?php
$usage = $generator_usage ?? [
    'g1' => ['total_hhmm' => '00:00', 'progress_percent' => 0, 'is_limit_reached' => false, 'is_warning' => false, 'last_maintenance_date' => null],
    'g2' => ['total_hhmm' => '00:00', 'progress_percent' => 0, 'is_limit_reached' => false, 'is_warning' => false, 'last_maintenance_date' => null],
    'has_alert' => false,
    'has_warning' => false,
];
$rows = $manutencoes ?? [];
$hasRows = !empty($rows);
$usageRows = $historico_uso ?? [];
$hasUsageRows = !empty($usageRows);
$usageColumnsReady = !empty($usage_columns_ready);
$tableReady = !empty($maintenance_table_ready);

$statusModal = null;
if (session()->has('error')) {
    $statusModal = [
        'title' => 'Erro',
        'message' => (string)session()->get('error'),
        'class' => 'text-danger',
    ];
} elseif (session()->has('success')) {
    $statusModal = [
        'title' => 'Sucesso',
        'message' => (string)session()->get('success'),
        'class' => 'text-success',
    ];
} elseif (!$tableReady) {
    $statusModal = [
        'title' => 'Atenção',
        'message' => 'Tabela de manutenção dos geradores não encontrada no banco. Execute o SQL de atualização antes de usar esta tela.',
        'class' => 'text-warning',
    ];
} elseif (!empty($usage['has_alert'])) {
    $statusModal = [
        'title' => 'Atenção',
        'message' => 'Um ou mais geradores atingiram 200:00 horas de uso.',
        'class' => 'text-danger',
    ];
} elseif (!empty($usage['has_warning'])) {
    $statusModal = [
        'title' => 'Alerta',
        'message' => 'Um ou mais geradores estão acima de 180:00 horas de uso.',
        'class' => 'text-warning',
    ];
}
?>

<div class="container-fluid mt-3">
    <div class="row g-3 mb-3">
        <?php $g1 = $usage['g1']; ?>
        <div class="col-12 col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-1">Gerador G1</h5>
                    <div class="fs-4 fw-bold <?= !empty($g1['is_limit_reached']) ? 'text-danger' : (!empty($g1['is_warning']) ? 'text-warning' : 'text-success'); ?>">
                        <?= esc((string)($g1['total_hhmm'] ?? '00:00')); ?> / 200:00
                    </div>
                    <div class="progress mt-2" style="height: 10px;">
                        <div class="progress-bar <?= !empty($g1['is_limit_reached']) ? 'bg-danger' : (!empty($g1['is_warning']) ? 'bg-warning' : 'bg-success'); ?>" role="progressbar" style="width: <?= (float)($g1['progress_percent'] ?? 0); ?>%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        Última manutenção: <?= !empty($g1['last_maintenance_date']) ? br_date((string)$g1['last_maintenance_date']) : 'não registrada'; ?>
                    </small>
                    <small class="text-muted d-block">
                        Projeção manutenção: <?= isset($g1['days_to_maintenance']) ? ((int)$g1['days_to_maintenance'] . ' dias') : 'sem dados recentes'; ?>
                    </small>
                </div>
            </div>
        </div>
        <?php $g2 = $usage['g2']; ?>
        <div class="col-12 col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-1">Gerador G2</h5>
                    <div class="fs-4 fw-bold <?= !empty($g2['is_limit_reached']) ? 'text-danger' : (!empty($g2['is_warning']) ? 'text-warning' : 'text-success'); ?>">
                        <?= esc((string)($g2['total_hhmm'] ?? '00:00')); ?> / 200:00
                    </div>
                    <div class="progress mt-2" style="height: 10px;">
                        <div class="progress-bar <?= !empty($g2['is_limit_reached']) ? 'bg-danger' : (!empty($g2['is_warning']) ? 'bg-warning' : 'bg-success'); ?>" role="progressbar" style="width: <?= (float)($g2['progress_percent'] ?? 0); ?>%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        Última manutenção: <?= !empty($g2['last_maintenance_date']) ? br_date((string)$g2['last_maintenance_date']) : 'não registrada'; ?>
                    </small>
                    <small class="text-muted d-block">
                        Projeção manutenção: <?= isset($g2['days_to_maintenance']) ? ((int)$g2['days_to_maintenance'] . ' dias') : 'sem dados recentes'; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <?php if ($tableReady): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title">Registrar manutenção</h5>
                <form class="row g-2" method="post" action="<?= url_to('geradores.store'); ?>">
                    <?= csrf_field(); ?>
                    <div class="col-12 col-md-2">
                        <label class="form-label">Gerador</label>
                        <select class="form-select" name="gerador" required>
                            <option value="G1">G1</option>
                            <option value="G2">G2</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Data manutenção</label>
                        <input type="date" class="form-control" name="data_manutencao" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label">Observação</label>
                        <input type="text" class="form-control" name="observacao" maxlength="255">
                    </div>
                    <div class="col-12 col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Salvar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Histórico de manutenções</h5>
                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label">Filtrar gerador</label>
                        <select id="filtroGerador" class="form-select" <?= !$hasRows ? 'disabled' : ''; ?>>
                            <option value="">Todos</option>
                            <option value="G1">G1</option>
                            <option value="G2">G2</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Data de</label>
                        <input type="date" id="filtroDataInicio" class="form-control" <?= !$hasRows ? 'disabled' : ''; ?>>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Data até</label>
                        <input type="date" id="filtroDataFim" class="form-control" <?= !$hasRows ? 'disabled' : ''; ?>>
                    </div>
                    <div class="col-12 col-md-3 d-flex align-items-end">
                        <button type="button" id="limparFiltrosManutencao" class="btn btn-outline-secondary w-100" <?= !$hasRows ? 'disabled' : ''; ?>>Limpar filtros</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="manutencoesTable" class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Gerador</th>
                                <th>Observação</th>
                                <th>Registrado em</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rows)): ?>
                                <?php foreach ($rows as $item): ?>
                                    <tr>
                                        <td data-order="<?= esc((string)$item->data_manutencao); ?>" data-search="<?= esc((string)$item->data_manutencao); ?>"><?= br_date((string)$item->data_manutencao); ?></td>
                                        <td><?= esc((string)$item->gerador); ?></td>
                                        <td><?= esc((string)($item->observacao ?? '')); ?></td>
                                        <td><?= !empty($item->created_at) ? esc(date('d/m/Y H:i', strtotime((string)$item->created_at))) : '-'; ?></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-warning btn-sm text-white" data-bs-toggle="modal" data-bs-target="#editManut<?= (int)$item->id; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm btn-delete-manut" data-id="<?= (int)$item->id; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editManut<?= (int)$item->id; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="post" action="<?= url_to('geradores.update'); ?>">
                                                    <?= csrf_field(); ?>
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Editar manutenção</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= (int)$item->id; ?>">
                                                        <div class="mb-2">
                                                            <label class="form-label">Gerador</label>
                                                            <select class="form-select" name="gerador" required>
                                                                <option value="G1" <?= ($item->gerador === 'G1') ? 'selected' : ''; ?>>G1</option>
                                                                <option value="G2" <?= ($item->gerador === 'G2') ? 'selected' : ''; ?>>G2</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Data manutenção</label>
                                                            <input type="date" class="form-control" name="data_manutencao" value="<?= esc((string)$item->data_manutencao); ?>" required>
                                                        </div>
                                                        <div>
                                                            <label class="form-label">Observação</label>
                                                            <input type="text" class="form-control" name="observacao" maxlength="255" value="<?= esc((string)($item->observacao ?? '')); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary">Salvar alterações</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Nenhuma manutenção registrada.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <h5 class="card-title">Histórico de uso dos geradores</h5>
                <?php if (!$usageColumnsReady): ?>
                    <div class="text-muted">Colunas de uso não encontradas no banco. Execute o SQL de atualização.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table id="usoGeradoresTable" class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Evento</th>
                                    <th>Uso G1</th>
                                    <th>Uso G2</th>
                                    <th>Total uso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($hasUsageRows): ?>
                                    <?php foreach ($usageRows as $u): ?>
                                        <?php
                                            $g1Min = (int)($u->uso_min_g1_calc ?? $u->uso_min_g1 ?? 0);
                                            $g2Min = (int)($u->uso_min_g2_calc ?? $u->uso_min_g2 ?? 0);
                                            $totMin = $g1Min + $g2Min;
                                            $g1H = str_pad((string)intdiv(max(0, $g1Min), 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string)(max(0, $g1Min) % 60), 2, '0', STR_PAD_LEFT);
                                            $g2H = str_pad((string)intdiv(max(0, $g2Min), 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string)(max(0, $g2Min) % 60), 2, '0', STR_PAD_LEFT);
                                            $ttH = str_pad((string)intdiv(max(0, $totMin), 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string)(max(0, $totMin) % 60), 2, '0', STR_PAD_LEFT);
                                        ?>
                                        <tr>
                                            <td data-order="<?= esc((string)$u->data); ?>"><?= br_date((string)$u->data); ?></td>
                                            <td><?= esc((string)($u->nome ?? '-')); ?></td>
                                            <td><?= esc($g1H); ?></td>
                                            <td><?= esc($g2H); ?></td>
                                            <td><?= esc($ttH); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Nenhum histórico de uso encontrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
  $(function () {
    <?php if (!empty($statusModal)): ?>
      const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
      statusModal.show();
    <?php endif; ?>

    $('.btn-delete-manut').on('click', function () {
      const id = $(this).data('id');
      $('#deleteManutId').val(id);
      const deleteModal = new bootstrap.Modal(document.getElementById('deleteManutModal'));
      deleteModal.show();
    });

    function parseDateToTs(iso) {
      if (!iso) return null;
      const parts = iso.split('-');
      if (parts.length !== 3) return null;
      const y = parseInt(parts[0], 10);
      const m = parseInt(parts[1], 10) - 1;
      const d = parseInt(parts[2], 10);
      if (Number.isNaN(y) || Number.isNaN(m) || Number.isNaN(d)) return null;
      return new Date(y, m, d).getTime();
    }

    if ($.fn.DataTable && $('#manutencoesTable').length && !<?= $hasRows ? 'false' : 'true'; ?>) {
      const table = $('#manutencoesTable').DataTable({
        pageLength: 10,
        lengthMenu: [[10], [10]],
        order: [[0, 'desc']],
        language: {
          url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json'
        }
      });

      $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'manutencoesTable') return true;

        const geradorFiltro = $('#filtroGerador').val();
        const dataInicio = parseDateToTs($('#filtroDataInicio').val());
        const dataFim = parseDateToTs($('#filtroDataFim').val());

        const rowNode = table.row(dataIndex).node();
        const rowDateIso = $('td:eq(0)', rowNode).attr('data-order') || '';
        const rowDateTs = parseDateToTs(rowDateIso);
        const rowGerador = (data[1] || '').trim();

        if (geradorFiltro && rowGerador !== geradorFiltro) {
          return false;
        }
        if (dataInicio !== null && rowDateTs !== null && rowDateTs < dataInicio) {
          return false;
        }
        if (dataFim !== null && rowDateTs !== null && rowDateTs > dataFim) {
          return false;
        }
        return true;
      });

      $('#filtroGerador, #filtroDataInicio, #filtroDataFim').on('change', function () {
        table.draw();
      });

      $('#limparFiltrosManutencao').on('click', function () {
        $('#filtroGerador').val('');
        $('#filtroDataInicio').val('');
        $('#filtroDataFim').val('');
        table.search('').draw();
      });
    }

    if ($.fn.DataTable && $('#usoGeradoresTable').length && !<?= $hasUsageRows ? 'false' : 'true'; ?>) {
      $('#usoGeradoresTable').DataTable({
        pageLength: 10,
        lengthMenu: [[10], [10]],
        order: [[0, 'desc']],
        language: {
          url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json'
        }
      });
    }
  });
</script>

<?php if (!empty($statusModal)): ?>
  <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title <?= esc($statusModal['class']); ?>"><?= esc($statusModal['title']); ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <?= esc($statusModal['message']); ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="modal fade" id="deleteManutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= url_to('geradores.delete'); ?>">
        <?= csrf_field(); ?>
        <div class="modal-header">
          <h5 class="modal-title text-danger">Confirmar exclusão</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          Tem certeza que deseja excluir este registro de manutenção?
          <input type="hidden" name="id" id="deleteManutId" value="">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Excluir</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php $this->endSection('content'); ?>
