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
</div>

<?php $this->endSection('content');?>
