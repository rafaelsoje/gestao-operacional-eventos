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

    <!-- jQuery CDN-->
    <script src="<?= base_url('./vendor/jquery/jquery.min.js')?>"></script>

    <!-- Bootstrap -->
     <link href="<?= base_url('./vendor/bootstrap/css/bootstrap.min.css');?>" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?=base_url('./vendor/font-awesome/css/all.min.css')?>">

    <!-- DataTables CSS E JS -->
    <link rel="stylesheet" href="<?= base_url('./vendor/datatables/css/jquery.dataTables.min.css')?>">
    <script src="<?= base_url('./vendor/datatables/js/jquery.dataTables.min.js')?>"></script>

    <!-- Moment.js -->
    <script src="<?= base_url('./vendor/moment/moment.min.js')?>"></script>

    <!-- jQuery Mask Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <!-- Google charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="<?= base_url('./assets/js/googleCharts.js')?>"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- FullCalendar CSS -->
    <link href="<?= base_url('./vendor/fullcalendar/fullcalendar.min.css')?>" rel='stylesheet' />

    <!-- FullCalendar JS -->
    <script src="<?= base_url('./vendor/fullcalendar/fullcalendar.min.js')?>"></script>


    <link rel="stylesheet" href="<?= base_url('./assets/css/style.css')?>" />

    <title>AudioVisual</title>
</head>
<body>
    <!-- <header class="container-fluid">
        <div class="row top-bar">
            <div class="col-2 logo d-flex align-items-center">
                <img class="w-100" src="<?= base_url('./media/logo.png');?>" />
            </div>
            <div class="col right d-flex align-items-center ">
                ...
            </div>
        </div>
    </header> -->
    <!-- <div class="hr"></div> -->
    <main class="row container-fluid d-flex min-vh-100 vh-auto m-0 p-0">
        <section class="col-2 side-bar vh-auto">
            <div class="logged-user ms-2  mt-1 mb-1 d-flex align-items-center">
                <?php $user = session()->get('user');?>
                <img class="w-25" src="<?= url_to('avatar.show', $user->avatar ?? 'avatar.svg');?>" data-bs-toggle="modal" data-bs-target="#exampleModal5"/>
                <?php
                    $logged = explode(' ', $user->name);
                ?>
                <div class="ms-3 name"><?=$logged['0'];?></div>
            </div>
            <?php $logged = session()->get('user');?>
            <div class="hr"></div>
            <nav class="navbar">
                <ul class="navbar-nav w-100">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('/');?>"><i class="bi bi-bar-chart-line pe-2 ps-2"></i>Dados Operaionais</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('/cadastro/operacional');?>"><i class="bi bi-file-earmark-text pe-2 ps-2"></i>Cadastro Operacional</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('/operacional/lista');?>"><i class="bi bi-list-check pe-2 ps-2"></i>Listagem de eventos</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('/audiovisual');?>"><i class="bi bi-play-fill pe-2 ps-2"></i>Audiovisual / Botanic B</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('/cadastro/proposta');?>"><i class="bi bi-folder2-open pe-2 ps-2"></i>Cadastro Proposta</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('/proposta');?>"><i class="bi bi-list-ul pe-2 ps-2"></i>Listagem de Propostas</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('/usuarios');?>"><i class="bi bi-person pe-2 ps-2"></i>Usuários cadastrados</a></li>
                    <?php if($logged->email == 'rafaelsoje@gmail.com'):?>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('/logs');?>"><i class="bi bi-journal-text pe-2 ps-2"></i>Logs</a></li>
                    <?php endif;?>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('/logout');?>"><i class="bi bi-box-arrow-right pe-2 ps-2"></i>Sair</a></li>
                </ul>
            </nav>
            <!-- <div class="fixed-bottom d-flex align-items-center ms-3 text-white">Com <i class="bi bi-heart-fill ms-1 me-1 text-danger"></i> e <i class="bi bi-cup-hot ms-1 me-1 text-info"></i>, <a href="/" class="ms-1 text-white">Rafael Soje</a></div> -->
            
        </section>
        <section class="col-10 content">


            <!-- Modal avatar-->
            <div class="modal fade" id="exampleModal5" tabindex="-1" aria-labelledby="exampleModalLabel5" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel"><?= $user->name;?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img class="w-50" src="<?= url_to('avatar.show', $user->avatar ?? 'avatar.svg');?>"/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
                </div>
                </div>
            </div>
            </div>

            <?php $this->renderSection('content');?>
        </section>
    </main>
    <!-- <footer class="d-flex justify-content-center align-items-center">
        Feito com carinho por <a class="ms-1" href="#">Rafael Soje</a>
        
    </footer> -->
    <script src="<?= base_url('./vendor/bootstrap/js/bootstrap.bundle.min.js')?>"></script>
    <!-- <script src="<?= base_url('./assets/js/script.js')?>"></script> -->    
</body>
</html>
