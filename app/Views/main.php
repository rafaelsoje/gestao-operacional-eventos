<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="#">
    <meta name="author" content="Rafael Soje - rafaelsoje@gmail.com">
    <meta name="keywords" content="#">

    <!-- <meta name="robots" content="index, follow"> -->
    <meta property="og:title" content="Audiovisual">
    <meta property="og:description" content="#">
    <meta property="og:image" content="<?= base_url('/assets/images/logo.png');?>">
    <meta property="og:url" content="<?= base_url();?>">

    <!-- Favicons -->
    <link rel="apple-touch-icon" href="<?= base_url('/assets/images/logo.png') ?>" sizes="180x180">
    <link rel="icon" href="<?= base_url('/assets/images/logo.png'); ?>" sizes="32x32" type="image/png">
    <link rel="icon" href="<?= base_url('/assets/images/logo.png'); ?>" sizes="16x16" type="image/png">
    <link rel="icon" href="<?= base_url('/assets/images/logo.png'); ?>">

    <!-- jQuery-->
    <script src="<?= base_url('assets/vendor/jquery/jquery.min.js')?>"></script>

    <!-- Bootstrap -->
     <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css');?>" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?=base_url('assets/vendor/font-awesome/css/all.min.css')?>">

    <!-- DataTables CSS E JS -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/datatables/css/jquery.dataTables.min.css')?>">
    <script src="<?= base_url('assets/vendor/datatables/js/jquery.dataTables.min.js')?>"></script>

    <!-- Moment.js -->
    <script src="<?= base_url('assets/vendor/moment/moment.min.js')?>"></script>

    <!-- jQuery Mask Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <!-- Google charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="<?= base_url('assets/js/googleCharts.js')?>"></script>

    <!-- Chart.js -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->

    <!-- FullCalendar JS -->
    <script src="<?= base_url('assets/vendor/fullcalendar/fullcalendar.min.js')?>"></script>

    <!-- Pré-conexões para otimizar carregamento de fontes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fontes (minimize quantidade se possível) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    <!-- CSS Vendor -->
    <!-- <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="<?=base_url('assets/vendor/bootstrap-icons/bootstrap-icons.css')?>" rel="stylesheet">
    <link href="<?=base_url('assets/vendor/aos/aos.css')?>" rel="stylesheet">
    <link href="<?=base_url('assets/vendor/glightbox/css/glightbox.min.css')?>" rel="stylesheet">
    <link href="<?=base_url('assets/vendor/swiper/swiper-bundle.min.css')?>" rel="stylesheet">
    <link href="<?=base_url('assets/vendor/fullcalendar/fullcalendar.min.css')?>" rel="stylesheet">

    <!-- CSS Principal -->
    <link href=<?= base_url('assets/css/main.css')?> rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('./assets/css/style.css')?>" />

  <title>AudioVisual</title>
</head>

<body class="index-page">

  <header id="header" class="header dark-background d-flex flex-column">
    <i class="header-toggle d-xl-none bi bi-list"></i>

    <div class="profile-img">
        <?php $user = session()->get('user');?>
        <?php $logged = explode(' ', $user->name);?>
      <img src="<?=base_url('media/') . $user->avatar;?>" alt="avatar" class="img-fluid rounded-circle">
    </div>
    <a href="<?= base_url();?>" class="logo d-flex align-items-center justify-content-center">
      <!-- Uncomment the line below if you also wish to use an image logo -->
      <!-- <img src="assets/img/logo.png" alt=""> -->
      <h1 class="sitename"><?=$logged['0'];?></h1>
    </a>

    <!-- <div class="social-links text-center">
      <a href="https://x.com/Rafaelsoje" class="twitter" target="_blank"><i class="bi bi-twitter-x"></i></a>
      <a href="https://www.facebook.com/rafaelsoje/" class="facebook" target="_blank"><i class="bi bi-facebook"></i></a>
      <a href="https://www.instagram.com/rafaelsoje/" class="instagram" target="_blank"><i class="bi bi-instagram"></i></a>
      <a href="https://github.com/rafaelsoje" class="google-plus" target="_blank"><i class="bi bi-github"></i></a>
      <a href="https://www.linkedin.com/in/rafaelsoje/" class="linkedin" target="_blank"><i class="bi bi-linkedin"></i></a>
    </div> -->
    <?php $logged = session()->get('user');?>
    <!-- <p class="text-secondary">Seu ip: <?= " " . $logged->ip ;?></p> -->
    <nav id="navmenu" class="navmenu">
      <ul>
        <li><a href="<?= base_url()?>" class="<?=(isset($operacional)? "active" : "");?>"><i class="bi bi-house navicon"></i>Dados operaionais</a></li>
        <!-- <li><a href="<?= base_url()?>" class="<?=(isset($projacao)? "active" : "");?>"><i class="bi bi-graph-up-arrow navicon"></i>Planejamento futuro</a></li> -->
        <li><a href="<?=base_url('cadastro/operacional')?>" class="<?=(isset($cadastroOp)? "active" : "");?>"><i class="bi bi-check-square navicon"></i>Cadastro operacional</a></li>
        <!-- <li><a href="<?=base_url('operacional/planejamento/cadastro')?>" class="<?=(isset($cadastroProj)? "active" : "");?>"><i class="bi bi-check-square navicon"></i>Cadastro Planejamento</a></li> -->
        <li><a href="<?=base_url('operacional/lista')?>" class="<?=(isset($listaOp)? "active" : "");?>"><i class="bi bi-list-ol navicon"></i>Lista de eventos</a></li>
        <!-- <li><a href="<?=base_url('calendario')?>"class="<?=(isset($calendario)? "active" : "");?>"><i class="bi bi-calendar-check navicon"></i>Calendário</a></li> -->
        <li class="dropdown"><a href="<?= base_url('?id=0');?>" class="<?= (isset($planejado) ? "active" : "");?>"><i class="bi bi-graph-up-arrow navicon "></i> <span>Planejamento futuro</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
          <ul>
            <li><a href="<?= base_url('operacional/planejamento/cadastro')?>">Cadastro de custo</a></li>
            <!-- <li class="dropdown"><a href="#"><span>Deep Dropdown</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
              <ul>
                <li><a href="#">Deep Dropdown 1</a></li>
                <li><a href="#">Deep Dropdown 2</a></li>
                <li><a href="#">Deep Dropdown 3</a></li>
                <li><a href="#">Deep Dropdown 4</a></li>
                <li><a href="#">Deep Dropdown 5</a></li>
              </ul>
            </li> -->
            <li><a href="<?= base_url('operacional/planejamento/lista')?>">Lista de eventos futuros</a></li>
            <!-- <li><a href="#">Dropdown</a></li>
            <li><a href="#">Dropdown 4</a></li> -->
          </ul>
        </li>
        <li><a href="<?=base_url('audiovisual')?>" class="<?=(isset($botanic)? "active" : "");?>"><i class="bi bi-play-btn navicon"></i>Audiovisual / Botanic B</a></li>
        <li><a href="<?=base_url('cadastro/proposta')?>" class="<?=(isset($botanicCad)? "active" : "");?>"><i class="bi bi-file-check navicon"></i>Cadastro proposta</a></li>
        <li><a href="<?=base_url('proposta')?>" class="<?=(isset($botanicProp)? "active" : "");?>"><i class="bi bi-person-lines-fill navicon"></i>Lista de propostas</a></li>
        <li><a href="<?=base_url('usuarios')?>"class="<?=(isset($botanicUser)? "active" : "");?>"><i class="bi bi-person-fill-check navicon"></i>Usuarios cadastrados</a></li>
        <?php if($logged->email == 'rafaelsoje@gmail.com'):?>
            <li><a href="<?=base_url('logs')?>"><i class="bi bi-envelope navicon"></i>Log</a></li>
        <?php endif;?>
        <li><a href="<?=base_url('logout')?>"><i class="bi bi-box-arrow-left navicon"></i>Sair</a></li>
        <!-- <li><a href="http://blog.rafaelsoje.com" target="_blank"><i class="bi bi-bookmarks navicon"></i> Blog</a></li> -->
        </ul>
    </nav>
    <div class="container">
        <div class="copyright text-center ">
            <!-- <a href="https://rafaelsoje.com" target="_blank">
            <p><strong class="px-1 sitename">Rafael Soje</strong> - feito com ❤️ e ☕</p>
            </a> -->
            </div>
        <!-- <div class="credits navicon">Com ❤️ e ☕, Rafael Soje</div> -->
  </div>
  </header>
  <main class="main">
    <!-- Content page -->
    <?= $this->renderSection('content');?>
  </main>
<!-- <footer id="footer" class="footer position-relative dark-background"> -->
  <!-- <div class="container">
    <div class="copyright text-center "><a href="https://rafaelsoje.com" target="_blank">
      <p>© <span><?= date('Y');?></span> <strong class="px-1 sitename">Rafael Soje</strong> - feito com ❤️ e ☕</p>
      </a>
    </div>
    <div class="credits">Feito com ❤️ e ☕</div>
  </div> -->
<!-- </footer> -->
<!-- Scroll Top -->
<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<!-- Preloader -->
<div id="preloader"></div>
<!-- Vendor JS Files -->
<script src="<?=base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')?>"></script>
<script src="<?=base_url('assets/vendor/aos/aos.js')?>"></script>
<script src="<?=base_url('assets/vendor/typed.js/typed.umd.js')?>"></script>
<script src="<?=base_url('assets/vendor/purecounter/purecounter_vanilla.js')?>"></script>
<script src="<?=base_url('assets/vendor/waypoints/noframework.waypoints.js')?>"></script>
<script src="<?=base_url('assets/vendor/glightbox/js/glightbox.min.js')?>"></script>
<script src="<?=base_url('assets/vendor/imagesloaded/imagesloaded.pkgd.min.js')?>"></script>
<script src="<?=base_url('assets/vendor/isotope-layout/isotope.pkgd.min.js')?>"></script>
<script src="<?=base_url('assets/vendor/swiper/swiper-bundle.min.js')?>"></script>
<!-- Main JS File -->
<script src="<?=base_url('assets/js/main.js')?>"></script>
</body>
</html>