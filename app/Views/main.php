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
    </style>

  <title>Operacional</title>
</head>

<body class="index-page">

  <header id="header" class="header dark-background d-flex flex-column">
    <i class="header-toggle d-xl-none bi bi-list"></i>

    <div class="profile-img">
        <?php $user = session()->get('user');?>
        <?php $logged = explode(' ', $user->name);?>
      <img
        src="<?= url_to('avatar.show', $user->avatar ?? 'avatar.svg');?>"
        alt="avatar"
        class="img-fluid rounded-circle"
        style="width:120px;height:120px;object-fit:cover;object-position:center;"
      >
    </div>
    <a href="<?= base_url();?>" class="logo d-flex align-items-center justify-content-center">
      <!-- Uncomment the line below if you also wish to use an image logo -->
      <!-- <img src="assets/img/logo.png" alt=""> -->
      <h1 class="sitename"><?=$logged['0'];?></h1>
    </a>

    <?php $logged = session()->get('user');?>      
    <nav id="navmenu" class="navmenu">
      <ul>
        <li><a href="<?= base_url()?>" class="<?=(isset($operacional)? "active" : "");?>"><i class="bi bi-house navicon"></i>Dados operaionais</a></li>
        
        <li><a href="<?=base_url('cadastro/operacional')?>" class="<?=(isset($cadastroOp)? "active" : "");?>"><i class="bi bi-check-square navicon"></i>Cadastro operacional</a></li>
        
        <li><a href="<?=base_url('operacional/lista')?>" class="<?=(isset($listaOp)? "active" : "");?>"><i class="bi bi-list-ol navicon"></i>Lista de eventos</a></li>        
        <li><a href="<?=base_url('operacional/calendario')?>" class="<?=(isset($calendarioOp)? "active" : "");?>"><i class="bi bi-calendar-date navicon"></i>Calendário Operacional</a></li> 
        <li><a href="<?=base_url('operacional/geradores')?>" class="<?=(isset($geradoresPage)? "active" : "");?>"><i class="bi bi-tools navicon"></i>Geradores</a></li>
        <li><a href="<?=base_url('visitas')?>" class="<?=(isset($visitasPage)? "active" : "");?>"><i class="bi bi-person-lines-fill navicon"></i>Visitas</a></li>
        <?php $navRole = $logged->role ?? (($logged->admin ?? 0) == 1 ? 'admin' : 'user'); ?>
        <?php if($navRole === 'admin' || $navRole === 'superuser'):?>
            <li><a href="<?=base_url('usuarios')?>"class="<?=(isset($botanicUser)? "active" : "");?>"><i class="bi bi-person-fill-check navicon"></i>Usuarios cadastrados</a></li>
        <?php endif;?>
        <li><a href="<?=base_url('perfil')?>"><i class="bi bi-person-circle navicon"></i>Meu perfil</a></li>
        <?php if($logged->email == env('SUPERUSER_EMAIL', env('TELEGRAM_ADMIN_EMAIL', 'rafaelsoje@gmail.com'))):?>            
            <li><a href="<?=base_url('admin/notifications')?>"><i class="bi bi-bell navicon"></i>Notificações</a></li>
            <li><a href="<?=base_url('logs')?>"><i class="bi bi-envelope navicon"></i>Log</a></li>
        <?php endif;?>
        <li><a href="<?=base_url('logout')?>"><i class="bi bi-box-arrow-left navicon"></i>Sair</a></li>
        
        </ul>
    </nav>
    <small class="site-credit">
      Desenvolvido por <a href="https://rafaelsoje.com" target="_blank" rel="noopener noreferrer">Rafael Soje</a>
    </small>
   
  </header>
<main class="main">
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
