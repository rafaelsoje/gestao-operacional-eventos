<?= $this->extend('main')?>;

<?php $this->section('content');?>

<div class="container mt-3">
  <h3 class="mb-3">Notificações Telegram</h3>

  <?php if (session()->has('error')) : ?>
    <div class="flash text-center alert alert-danger text-danger"><?php echo session()->get('error') ?? ''; ?></div>
  <?php endif; ?>
  <?php if (session()->has('success')) : ?>
    <div class="flash text-center alert alert-success text-success"><?php echo session()->get('success') ?? ''; ?></div>
  <?php endif; ?>

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

  <hr class="my-4">

  <h4 class="mb-3">Usuários online agora</h4>
  <?php $onlineUsers = $onlineUsers ?? []; ?>
  <?php $onlineWindowSeconds = $onlineWindowSeconds ?? 300; ?>

  <?php if (!empty($onlineUsers)): ?>
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
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
    <small class="text-muted d-block mt-2">Considera online quem teve atividade nos últimos <?= esc((string) $onlineWindowSeconds); ?> segundos.</small>
  <?php else: ?>
    <div class="alert alert-info mb-0">Nenhum usuário online no momento.</div>
  <?php endif; ?>
</div>

<?php $this->endSection('content');?>
