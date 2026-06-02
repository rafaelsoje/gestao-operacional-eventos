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
// Mantém padrão visual "somente horas" nos cards (ex.: 66:06 -> 66).
$hourOnly = static function (?string $hhmm): string {
    $value = trim((string)$hhmm);
    if ($value === '') {
        return '0';
    }
    if (strpos($value, ':') !== false) {
        [$hours] = explode(':', $value, 2);
        $hours = preg_replace('/\D+/', '', (string)$hours);
        return $hours !== '' ? (string)((int)$hours) : '0';
    }
    $digits = preg_replace('/\D+/', '', $value);
    return $digits !== '' ? (string)((int)$digits) : '0';
};

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
    // Modal de destaque para manutenção vencida (>= 200h).
    $statusModal = [
        'title' => 'Atenção',
        'message' => 'Um ou mais geradores atingiram 200 horas de uso.',
        'class' => 'text-danger',
    ];
} elseif (!empty($usage['has_warning'])) {
    $statusModal = [
        'title' => 'Alerta',
        'message' => 'Um ou mais geradores estão acima de 180 horas de uso.',
        'class' => 'text-warning',
    ];
}
?>
<style>
    .generator-warning-soft {
        color: #d89a1a !important;
    }
    .progress-bar.generator-warning-soft {
        background-color: #d89a1a !important;
    }
    .geradores-page .generator-card {
        padding: 1.15rem;
    }
    .geradores-page .generator-title {
        color: #172033;
        font-size: 1rem;
        font-weight: 800;
    }
    .geradores-page .generator-value {
        font-size: 1.65rem;
        line-height: 1.15;
    }
    .geradores-page .section-card {
        padding: 1.2rem;
    }
</style>

<div class="page-shell mt-3 mb-5 geradores-page">
    <div class="page-hero">
        <div>
            <h2>Geradores</h2>
            <p>Controle horas de uso, manutenção e histórico operacional dos geradores.</p>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <?php $g1 = $usage['g1']; ?>
        <?php
            $g1ValueClass = !empty($g1['is_limit_reached'])
                ? 'text-danger'
                : (!empty($g1['is_warning']) ? 'generator-warning-soft' : 'text-success');
            $g1BarClass = !empty($g1['is_limit_reached'])
                ? 'bg-danger'
                : (!empty($g1['is_warning']) ? 'generator-warning-soft' : 'bg-success');
        ?>
        <div class="col-12 col-md-6">
            <div class="metric-card generator-card h-100">
                <div class="generator-title mb-2">Gerador G1</div>
                <div class="generator-value fw-bold <?= $g1ValueClass; ?>">
                    <?= esc($hourOnly((string)($g1['total_hhmm'] ?? '00:00'))); ?> / 200
                </div>
                <div class="progress mt-3" style="height: 10px;">
                    <div class="progress-bar <?= $g1BarClass; ?>" role="progressbar" style="width: <?= (float)($g1['progress_percent'] ?? 0); ?>%"></div>
                </div>
                <small class="text-muted mt-3 d-block">
                    Última manutenção: <?= !empty($g1['last_maintenance_date']) ? br_date((string)$g1['last_maintenance_date']) : 'não registrada'; ?>
                </small>
                <small class="text-muted d-block">
                    Projeção manutenção: <?= isset($g1['days_to_maintenance']) ? ((int)$g1['days_to_maintenance'] . ' dias') : 'sem dados recentes'; ?>
                </small>
            </div>
        </div>
        <?php $g2 = $usage['g2']; ?>
        <?php
            $g2ValueClass = !empty($g2['is_limit_reached'])
                ? 'text-danger'
                : (!empty($g2['is_warning']) ? 'generator-warning-soft' : 'text-success');
            $g2BarClass = !empty($g2['is_limit_reached'])
                ? 'bg-danger'
                : (!empty($g2['is_warning']) ? 'generator-warning-soft' : 'bg-success');
        ?>
        <div class="col-12 col-md-6">
            <div class="metric-card generator-card h-100">
                <div class="generator-title mb-2">Gerador G2</div>
                <div class="generator-value fw-bold <?= $g2ValueClass; ?>">
                    <?= esc($hourOnly((string)($g2['total_hhmm'] ?? '00:00'))); ?> / 200
                </div>
                <div class="progress mt-3" style="height: 10px;">
                    <div class="progress-bar <?= $g2BarClass; ?>" role="progressbar" style="width: <?= (float)($g2['progress_percent'] ?? 0); ?>%"></div>
                </div>
                <small class="text-muted mt-3 d-block">
                    Última manutenção: <?= !empty($g2['last_maintenance_date']) ? br_date((string)$g2['last_maintenance_date']) : 'não registrada'; ?>
                </small>
                <small class="text-muted d-block">
                    Projeção manutenção: <?= isset($g2['days_to_maintenance']) ? ((int)$g2['days_to_maintenance'] . ' dias') : 'sem dados recentes'; ?>
                </small>
            </div>
        </div>
    </div>

    <?php if ($tableReady): ?>
        <div class="content-card section-card mb-3">
                <div class="content-card-header">
                    <div>
                        <h5>Registrar manutenção</h5>
                        <small>Atualize o marco de manutenção dos geradores.</small>
                    </div>
                </div>
                <form class="row g-3 align-items-end" method="post" action="<?= url_to('geradores.store'); ?>">
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

        <div class="content-card section-card">
                <div class="content-card-header">
                    <div>
                        <h5>Histórico de manutenções</h5>
                        <small><?= count($rows); ?> registro(s) encontrados</small>
                    </div>
                </div>
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
                    <table id="manutencoesTable" class="table table-striped table-hover align-middle app-table">
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
                                        <td data-label="Data" data-order="<?= esc((string)$item->data_manutencao); ?>" data-search="<?= esc((string)$item->data_manutencao); ?>"><?= br_date((string)$item->data_manutencao); ?></td>
                                        <td data-label="Gerador"><?= esc((string)$item->gerador); ?></td>
                                        <td data-label="Observação"><?= esc((string)($item->observacao ?? '')); ?></td>
                                        <td data-label="Registrado em"><?= !empty($item->created_at) ? esc(date('d/m/Y H:i', strtotime((string)$item->created_at))) : '-'; ?></td>
                                        <td data-label="Ações" class="text-center action-cell">
                                            <button type="button" class="btn btn-warning btn-sm text-white btn-icon" data-bs-toggle="modal" data-bs-target="#editManut<?= (int)$item->id; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm btn-delete-manut btn-icon" data-id="<?= (int)$item->id; ?>">
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

        <div class="content-card section-card mt-3">
                <div class="content-card-header">
                    <div>
                        <h5>Histórico de uso dos geradores</h5>
                        <small>Uso calculado a partir dos eventos operacionais.</small>
                    </div>
                </div>
                <?php if (!$usageColumnsReady): ?>
                    <div class="text-muted">Colunas de uso não encontradas no banco. Execute o SQL de atualização.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table id="usoGeradoresTable" class="table table-striped table-hover align-middle app-table">
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
                                            <td data-label="Data" data-order="<?= esc((string)$u->data); ?>"><?= br_date((string)$u->data); ?></td>
                                            <td data-label="Evento"><?= esc((string)($u->nome ?? '-')); ?></td>
                                            <td data-label="Uso G1"><?= esc($g1H); ?></td>
                                            <td data-label="Uso G2"><?= esc($g2H); ?></td>
                                            <td data-label="Total uso"><?= esc($ttH); ?></td>
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
      // Normaliza filtro de datas (YYYY-MM-DD) para comparação sem timezone externo.
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

        // Filtro combinado por gerador e intervalo de data.
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
