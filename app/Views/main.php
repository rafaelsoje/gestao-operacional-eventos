<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
    <?php $isCalendarPage = isset($calendario) || isset($calendarioOp); ?>
    <?php $calendarBundleReady = is_file(FCPATH . 'assets/build/calendar.js'); ?>
    <?php $appCssV = is_file(FCPATH . 'assets/build/app.css') ? filemtime(FCPATH . 'assets/build/app.css') : time(); ?>
    <?php $appJsV = is_file(FCPATH . 'assets/build/app.js') ? filemtime(FCPATH . 'assets/build/app.js') : time(); ?>
    <?php $calendarJsV = is_file(FCPATH . 'assets/build/calendar.js') ? filemtime(FCPATH . 'assets/build/calendar.js') : time(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="#">
    <meta name="author" content="Rafael Soje - rafaelsoje@gmail.com">
    <meta name="keywords" content="#">

    <!-- <meta name="robots" content="index, follow"> -->
    <meta property="og:title" content="Audiovisual">
    <meta property="og:description" content="#">
    <meta property="og:image" content="<?= base_url('/assets/images/logo.png');?>">
    <meta property="og:url" content="<?= base_url();?>">
    <meta name="theme-color" content="#3197da">

    <!-- Favicons -->
    <link rel="icon" href="<?= base_url('/favicon.ico'); ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('/assets/images/logo.png') ?>" sizes="180x180">
    <link rel="icon" href="<?= base_url('/assets/images/logo.png'); ?>" sizes="32x32" type="image/png">
    <link rel="icon" href="<?= base_url('/assets/images/logo.png'); ?>" sizes="16x16" type="image/png">
    <link rel="icon" href="<?= base_url('/assets/images/logo.png'); ?>">
    <link rel="manifest" href="<?= base_url('/manifest.webmanifest'); ?>">

    <!-- Google charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <!-- Pré-conexões para otimizar carregamento de fontes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fontes -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= base_url('assets/build/app.css?v=' . $appCssV) ?>" />
    <script src="<?= base_url('assets/build/app.js?v=' . $appJsV) ?>"></script>
    <?php if ($isCalendarPage && $calendarBundleReady): ?>
    <script src="<?= base_url('assets/build/calendar.js?v=' . $calendarJsV) ?>"></script>
    <script>
      // Fallback: se o bundle local não expor FullCalendar, usa CDN.
      if (!window.FullCalendar || !window.FullCalendar.Calendar) {
        document.write('<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"><\/script>');
      }
    </script>
    <?php elseif ($isCalendarPage): ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
    <?php endif; ?>
    <style>
      .site-credit {
        margin-top: auto;
        padding: 10px 8px 14px;
        font-size: 11px;
        line-height: 1.2;
        color: rgba(168, 169, 180, 0.78);
        text-align: center;
      }

      .site-credit a {
        color: rgba(220, 234, 255, 0.96);
        text-decoration: none;
      }

      .site-credit a:hover {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: underline;
      }

      @media (max-width: 768px) {
        .site-credit {
          font-size: 10px;
          padding-bottom: 10px;
        }
      }

      .top-account-menu {
        display: flex;
        justify-content: flex-end;
        padding: 12px 18px 4px;
        position: relative;
        z-index: 20;
      }

      .top-account-menu .dropdown-toggle {
        min-height: 46px;
        max-width: 220px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.12);
        color: #27313f;
        text-align: left;
      }

      .top-account-menu .dropdown-toggle:hover,
      .top-account-menu .dropdown-toggle:focus {
        background: #fff;
        color: #111827;
      }

      .top-account-menu .account-avatar {
        width: 34px;
        height: 34px;
        object-fit: cover;
        object-position: center;
        border: 2px solid rgba(49, 151, 218, 0.32);
      }

      .top-account-menu .account-name {
        display: block;
        max-width: 124px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-weight: 700;
        line-height: 1.1;
      }

      .top-account-menu .account-role {
        display: block;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.2;
      }

      .top-account-menu .dropdown-menu {
        min-width: 220px;
        border-radius: 6px;
      }

      .top-account-menu .dropdown-item {
        display: flex;
        align-items: center;
        gap: 8px;
      }

      @media (max-width: 480px) {
        .top-account-menu {
          padding-right: 12px;
        }

        .top-account-menu .account-text {
          display: none;
        }

        .top-account-menu .dropdown-toggle {
          min-width: 48px;
          padding-left: 6px;
          padding-right: 6px;
        }
      }
    </style>

  <title>Operacional</title>
</head>

<body class="index-page">

  <header id="header" class="header dark-background d-flex flex-column">
    <i class="header-toggle d-xl-none bi bi-list"></i>

    <?php $logged = session()->get('user');?>
    <?php $loggedName = trim((string)($logged->name ?? 'Usuário')); ?>
    <?php $loggedFirstName = explode(' ', $loggedName)[0] ?? 'Usuário'; ?>
    <?php $navRole = $logged->role ?? (($logged->admin ?? 0) == 1 ? 'admin' : 'user'); ?>
    <?php $isSuperuser = ($logged->email ?? '') == env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', 'rafaelsoje@gmail.com')); ?>
    <?php $roleLabel = match ($navRole) {
        'superuser' => 'Superusuário',
        'admin' => 'Administrador',
        default => 'Usuário',
    }; ?>

    <div class="profile-img">
      <img
        src="<?= url_to('avatar.show', $logged->avatar ?? 'avatar.svg');?>"
        alt="avatar"
        class="img-fluid rounded-circle"
        style="width:120px;height:120px;object-fit:cover;object-position:center;"
      >
    </div>

    <a href="<?= base_url();?>" class="logo d-flex align-items-center justify-content-center">
      <!-- Uncomment the line below if you also wish to use an image logo -->
      <!-- <img src="assets/img/logo.png" alt=""> -->
      <h1 class="sitename"><?= esc($loggedFirstName); ?></h1>
    </a>

    <nav id="navmenu" class="navmenu">
      <ul>
        <li><a href="<?= base_url()?>" class="<?=(isset($operacional)? "active" : "");?>"><i class="bi bi-house navicon"></i>Dados operaionais</a></li>
        
        <li><a href="<?=base_url('cadastro/operacional')?>" class="<?=(isset($cadastroOp)? "active" : "");?>"><i class="bi bi-check-square navicon"></i>Cadastro operacional</a></li>
        
        <li><a href="<?=base_url('operacional/lista')?>" class="<?=(isset($listaOp)? "active" : "");?>"><i class="bi bi-list-ol navicon"></i>Lista de eventos</a></li>        
        <li><a href="<?=base_url('operacional/calendario')?>" class="<?=(isset($calendarioOp)? "active" : "");?>"><i class="bi bi-calendar-date navicon"></i>Calendário Operacional</a></li> 
        <li><a href="<?=base_url('operacional/geradores')?>" class="<?=(isset($geradoresPage)? "active" : "");?>"><i class="bi bi-tools navicon"></i>Geradores</a></li>
        <li><a href="<?=base_url('visitas')?>" class="<?=(isset($visitasPage)? "active" : "");?>"><i class="bi bi-person-lines-fill navicon"></i>Visitas</a></li>
        <li><a href="<?=base_url('operacional/taxa-cozinha')?>" class="<?=(isset($taxaCozinhaPage)? "active" : "");?>"><i class="bi bi-cash-coin navicon"></i>Taxa de cozinha</a></li>
        
        </ul>
    </nav>
    <small class="site-credit">
      Desenvolvido por <a href="https://rafaelsoje.com" target="_blank" rel="noopener noreferrer">Rafael Soje</a>
    </small>
   
  </header>
<main class="main">
    <div class="top-account-menu dropdown">
      <button class="btn dropdown-toggle d-flex align-items-center gap-2" type="button" id="topAccountMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
        <img
          src="<?= url_to('avatar.show', $logged->avatar ?? 'avatar.svg');?>"
          alt="avatar"
          class="account-avatar rounded-circle"
        >
        <span class="account-text flex-grow-1 min-w-0">
          <span class="account-name"><?= esc($loggedFirstName); ?></span>
          <span class="account-role"><?= esc($roleLabel); ?></span>
        </span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="topAccountMenuButton">
        <li><a class="dropdown-item" href="<?=base_url('perfil')?>"><i class="bi bi-person-circle"></i>Meu perfil</a></li>
        <li><a class="dropdown-item" href="<?=base_url('assinatura')?>"><i class="bi bi-credit-card"></i>Assinatura</a></li>
        <?php if($navRole === 'admin' || $navRole === 'superuser'):?>
          <li><a class="dropdown-item" href="<?=base_url('usuarios')?>"><i class="bi bi-person-fill-check"></i>Usuários</a></li>
        <?php endif;?>
        <?php if($isSuperuser):?>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="<?=base_url('admin/notifications')?>"><i class="bi bi-bell"></i>Notificações</a></li>
          <li><a class="dropdown-item" href="<?=base_url('logs')?>"><i class="bi bi-envelope"></i>Logs</a></li>
        <?php endif;?>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="<?=base_url('logout')?>"><i class="bi bi-box-arrow-left"></i>Sair</a></li>
      </ul>
    </div>
    <?php
      $subscriptionState = null;
      if (session()->get('auth')) {
          try {
              $subscriptionState = (new \App\Services\SystemSubscriptionService())->subscriptionState();
          } catch (\Throwable $e) {
              $subscriptionState = null;
          }
      }
    ?>
    <?php if (!empty($subscriptionState['enabled']) && !empty($subscriptionState['warning']) && empty($subscriptionPage)): ?>
      <?php
        $subscriptionBlocked = !empty($subscriptionState['blocked']);
        $daysUntilBlock = (int)($subscriptionState['days_until_block'] ?? 0);
        $bannerTitle = $subscriptionBlocked ? 'Sistema bloqueado por mensalidade vencida' : 'Mensalidade do sistema pendente';
        $bannerDetail = $subscriptionBlocked
          ? 'Regularize o pagamento para liberar as funcionalidades.'
          : ($daysUntilBlock >= 0
              ? 'Bloqueio previsto em ' . $daysUntilBlock . ' dia(s), caso o pagamento não seja identificado.'
              : 'A tolerância terminou. Regularize para evitar bloqueio.');
      ?>
      <div class="subscription-banner app-banner alert-dismissible fade show <?= $subscriptionBlocked ? 'app-banner-danger' : 'app-banner-warning'; ?>" role="alert">
        <div class="subscription-banner-content d-flex align-items-start gap-2">
          <i class="bi <?= $subscriptionBlocked ? 'bi-lock-fill text-danger' : 'bi-exclamation-triangle-fill text-warning'; ?> mt-1"></i>
          <div>
            <strong><?= esc($bannerTitle); ?></strong>
            <p><?= esc((string)($subscriptionState['message'] ?? 'Mensalidade pendente.')); ?> <?= esc($bannerDetail); ?></p>
          </div>
        </div>
        <a href="<?= base_url('assinatura'); ?>" class="subscription-banner-action btn <?= $subscriptionBlocked ? 'btn-outline-danger' : 'btn-outline-warning'; ?>">
          <i class="bi bi-credit-card"></i>
          Regularizar
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
      </div>
      <div class="modal fade" id="subscriptionPendingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Mensalidade pendente</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
              <?= esc((string)($subscriptionState['message'] ?? 'Mensalidade pendente.')); ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Depois</button>
              <a href="<?= base_url('assinatura'); ?>" class="btn btn-primary">Ver pagamento</a>
            </div>
          </div>
        </div>
      </div>
      <script>
        window.addEventListener('load', function () {
          if (!window.bootstrap || !bootstrap.Modal || sessionStorage.getItem('subscriptionPendingShown') === '1') {
            return;
          }
          sessionStorage.setItem('subscriptionPendingShown', '1');
          new bootstrap.Modal(document.getElementById('subscriptionPendingModal')).show();
        });
      </script>
    <?php endif; ?>
    <!-- Content page -->
    <?= $this->renderSection('content');?>
  </main>

<!-- Scroll Top -->
<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<!-- Preloader -->
<div id="preloader"></div>
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('<?= base_url('/service-worker.js'); ?>');
    });
  }
</script>

</body>
</html>
