<?= $this->extend('main')?>;

<?php $this->section('content');?>

<div class="page-shell mt-3">
  <div class="page-hero">
    <div>
      <h2>Administração</h2>
      <p>Gerencie notificações, limpeza de logs e usuários online.</p>
    </div>
  </div>

  <?php if (session()->has('error')) : ?>
    <div class="flash text-center alert alert-danger text-danger"><?php echo session()->get('error') ?? ''; ?></div>
  <?php endif; ?>
  <?php if (session()->has('success')) : ?>
    <div class="flash text-center alert alert-success text-success"><?php echo session()->get('success') ?? ''; ?></div>
  <?php endif; ?>

  <div class="content-card p-3 mb-4">
    <div class="content-card-header">
      <div>
        <h5>Notificações Telegram</h5>
        <small>Preferência global para alertas operacionais.</small>
      </div>
    </div>
    <form method="post" action="<?= url_to('admin.notifications.update');?>">
      <?= csrf_field(); ?>
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch" id="telegram_enabled" name="telegram_enabled" value="1" <?= !empty($enabled) ? 'checked' : ''; ?>>
        <label class="form-check-label" for="telegram_enabled">Ativar notificações Telegram</label>
      </div>
      <div class="mt-3">
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div>
    </form>
  </div>

  <!-- Bloco administrativo: pré-visualiza impacto antes de remover logs antigos. -->
  <div class="content-card mb-4">
    <div class="card-body">
      <div class="content-card-header">
        <div>
          <h5>Limpeza de logs</h5>
          <small>Pré-visualize o impacto antes de remover registros antigos.</small>
        </div>
      </div>
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label for="logs_cleanup_days" class="form-label">Remover logs com mais de</label>
          <select id="logs_cleanup_days" class="form-select">
            <option value="30">30 dias</option>
            <option value="60">60 dias</option>
            <option value="90">90 dias</option>
            <option value="all">Todos os logs</option>
          </select>
        </div>
        <div class="col-md-5">
          <div id="logs_cleanup_preview" class="alert alert-secondary mb-0">
            Selecione um período para ver quantos registros serão removidos.
          </div>
        </div>
        <div class="col-md-3 d-grid">
          <button type="button" id="logs_cleanup_trigger" class="btn btn-danger" disabled>
            Remover logs
          </button>
        </div>
      </div>

      <form id="logs_cleanup_form" method="post" action="<?= url_to('admin.logs.cleanup.delete');?>" class="d-none">
        <?= csrf_field(); ?>
        <input type="hidden" name="days" id="logs_cleanup_days_input" value="30">
      </form>
    </div>
  </div>

  <?php $onlineUsers = $onlineUsers ?? []; ?>
  <?php $onlineWindowSeconds = $onlineWindowSeconds ?? 300; ?>

  <div class="content-card p-3">
    <div class="content-card-header">
      <div>
        <h5>Usuários online agora</h5>
        <small>Atividade nos últimos <?= esc((string) $onlineWindowSeconds); ?> segundos.</small>
      </div>
    </div>
  <?php if (!empty($onlineUsers)): ?>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle app-table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Última atividade</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($onlineUsers as $online): ?>
            <?php
              $lastSeen = (int) ($online['last_seen'] ?? 0);
              $secondsAgo = max(0, time() - $lastSeen);
            ?>
            <tr>
              <td><?= esc($online['name'] ?? ''); ?></td>
              <td><?= esc($online['email'] ?? ''); ?></td>
              <td><?= esc(date('d/m/Y H:i:s', $lastSeen)); ?> (há <?= esc((string) $secondsAgo); ?>s)</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="alert alert-info mb-0">Nenhum usuário online no momento.</div>
  <?php endif; ?>
  </div>
</div>

<div class="modal fade" id="logsCleanupModal" tabindex="-1" aria-labelledby="logsCleanupModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logsCleanupModalLabel">Confirmar remoção de logs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">Você está prestes a remover logs antigos.</p>
        <p class="mb-0">
          Período selecionado: <strong id="logs_cleanup_modal_days">30 dias</strong><br>
          Registros que serão removidos: <strong id="logs_cleanup_modal_count">0</strong>
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="logs_cleanup_confirm_btn">Confirmar e remover</button>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    // Fluxo client-side da limpeza: prévia assíncrona + confirmação em modal.
    const selectDays = document.getElementById('logs_cleanup_days');
    const previewBox = document.getElementById('logs_cleanup_preview');
    const triggerBtn = document.getElementById('logs_cleanup_trigger');
    const form = document.getElementById('logs_cleanup_form');
    const formDays = document.getElementById('logs_cleanup_days_input');
    const modalDays = document.getElementById('logs_cleanup_modal_days');
    const modalCount = document.getElementById('logs_cleanup_modal_count');
    const confirmBtn = document.getElementById('logs_cleanup_confirm_btn');
    const previewUrlBase = "<?= url_to('admin.logs.cleanup.preview'); ?>";
    let removableCount = 0;

    function setPreviewMessage(message, alertClass) {
      previewBox.className = 'alert mb-0 ' + alertClass;
      previewBox.textContent = message;
    }

    function updateButtonState() {
      triggerBtn.disabled = removableCount < 1;
    }

    function selectedLabel() {
      return selectDays.options[selectDays.selectedIndex].text;
    }

    async function loadPreview() {
      const days = selectDays.value;
      const label = selectedLabel();
      formDays.value = days;
      removableCount = 0;
      updateButtonState();
      setPreviewMessage('Consultando quantidade de registros...', 'alert-secondary');

      try {
        const response = await fetch(previewUrlBase + '?days=' + encodeURIComponent(days), {
          credentials: 'same-origin'
        });
        const payload = await response.json();

        if (!response.ok || typeof payload.count === 'undefined') {
          throw new Error(payload.error || 'Falha ao consultar os logs.');
        }

        removableCount = Number(payload.count) || 0;
        if (removableCount > 0) {
          if (days === 'all') {
            setPreviewMessage('Serão removidos ' + removableCount + ' registros (todos os logs).', 'alert-warning');
          } else {
            setPreviewMessage('Serão removidos ' + removableCount + ' registros com mais de ' + days + ' dias.', 'alert-warning');
          }
        } else {
          if (days === 'all') {
            setPreviewMessage('Nenhum registro encontrado para remover.', 'alert-success');
          } else {
            setPreviewMessage('Nenhum registro encontrado para remover com mais de ' + days + ' dias.', 'alert-success');
          }
        }
        updateButtonState();
      } catch (error) {
        setPreviewMessage(error.message || 'Não foi possível consultar os registros.', 'alert-danger');
      }
    }

    selectDays.addEventListener('change', loadPreview);
    triggerBtn.addEventListener('click', function () {
      const days = selectDays.value;
      const label = selectedLabel();
      modalDays.textContent = label;
      modalCount.textContent = String(removableCount);

      if (window.bootstrap && bootstrap.Modal) {
        const modal = new bootstrap.Modal(document.getElementById('logsCleanupModal'));
        modal.show();
        return;
      }

      const confirmText = days === 'all'
        ? 'Confirmar remoção de ' + removableCount + ' registros (todos os logs)?'
        : 'Confirmar remoção de ' + removableCount + ' registros com mais de ' + days + ' dias?';

      if (confirm(confirmText)) {
        form.submit();
      }
    });

    confirmBtn.addEventListener('click', function () {
      form.submit();
    });

    loadPreview();
  })();
</script>

<?php $this->endSection('content');?>
