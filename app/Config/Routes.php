<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/login', 'Login::index',['as' => 'login']);
$routes->post('/login', 'Login::store', ['as' => 'login.store', 'filter' => 'csrf']);
$routes->get('/logout', 'Login::logout', ['as' => 'logout']);
$routes->get('recovery', 'Login::recovery', ['as' => 'recovery']);
$routes->post('recovery', 'Login::recoverySend', ['as' => 'recovery.send', 'filter' => 'csrf']);
$routes->post('recovery/store', 'Login::recoveryStore', ['as' => 'recovery.store', 'filter' => 'csrf']);

// Grupo de rotas que requer autenticação
$routes->group('', ['filter' => 'auth'], function($routes){
    
    $routes->get('/usuarios', 'AdminController::usuarios',['as' => 'usuarios', 'filter' => 'role:admin']);
    $routes->get('/calendario', 'Home::calendario');    
    $routes->get('/logs', 'AdminController::logs', ['as' => 'logs', 'filter' => 'role:superuser']);
    
    $routes->get('/cadastro/usuario', 'AdminController::cadastroUsuario', ['as' => 'usuario', 'filter' => 'role:admin']);
    $routes->post('/cadastro/usuario', 'AdminController::usuarioStore', ['as' => 'usuario.store', 'filter' => ['csrf', 'role:admin']]);
    
    $routes->get('/', 'OperacionalController::operacional', ['as' => 'home']);
    $routes->get('/operacional/lista', 'OperacionalController::operacionalLista', ['as' => 'operacional.lista']);
    $routes->get('/operacional/calendario', 'OperacionalController::calendarioOperacional', ['as' => 'calendario.operacional']);
    $routes->get('/operacional/geradores', 'OperacionalController::geradoresManutencao', ['as' => 'geradores.index']);
    $routes->get('/operacional/taxa-cozinha', 'OperacionalController::taxaCozinha', ['as' => 'taxa.cozinha']);
    $routes->post('/operacional/taxa-cozinha', 'OperacionalController::taxaCozinhaStore', ['as' => 'taxa.cozinha.store', 'filter' => 'csrf']);
    $routes->post('/operacional/taxa-cozinha/delete', 'OperacionalController::taxaCozinhaDelete', ['as' => 'taxa.cozinha.delete', 'filter' => 'csrf']);
    $routes->get('/operacional/taxa-cozinha/comprovante/(:segment)', 'OperacionalController::downloadTaxaCozinhaComprovante/$1', ['as' => 'taxa.cozinha.comprovante']);
    $routes->get('/visitas', 'VisitasController::visitas', ['as' => 'visitas']);
    $routes->post('/visitas', 'VisitasController::visitasStore', ['as' => 'visitas.store', 'filter' => 'csrf']);
    $routes->get('/update/visita/(:num)', 'VisitasController::updateVisita/$1', ['as' => 'visitas.update', 'filter' => 'role:admin']);
    $routes->post('/delete/visita', 'VisitasController::deleteVisita', ['as' => 'visitas.delete', 'filter' => ['csrf', 'role:admin']]);
    $routes->post('/operacional/geradores/store', 'OperacionalController::geradoresManutencaoStore', ['as' => 'geradores.store', 'filter' => 'csrf']);
    $routes->post('/operacional/geradores/update', 'OperacionalController::geradoresManutencaoUpdate', ['as' => 'geradores.update', 'filter' => 'csrf']);
    $routes->post('/operacional/geradores/delete', 'OperacionalController::geradoresManutencaoDelete', ['as' => 'geradores.delete', 'filter' => 'csrf']);
    $routes->get('/operacional/planejamento/cadastro', 'OperacionalController::cadastroPlanejamento');
    $routes->post('/operacional/planejamento/cadastro', 'OperacionalController::cadastroPlanejamentoStore', ['as' => 'cadastro.planejamento.store', 'filter' => 'csrf']);
    $routes->get('/operacional/planejamento/lista', 'OperacionalController::planejamentoLista', ['as' => 'planejamento.lista']);
    $routes->get('/cadastro/operacional', 'OperacionalController::cadastroOperacional', ['as' => 'cadastro.operacional']);
    $routes->post('/cadastro/operacional', 'OperacionalController::cadastroOperacionalStore', ['as' => 'cadastro.operacional.store', 'filter' => 'csrf']);
    $routes->post('/operacional/gerador/manutencao', 'OperacionalController::registrarManutencaoGerador', ['as' => 'gerador.manutencao.store', 'filter' => 'csrf']);
    $routes->get('/update/operacional/(:num)', 'OperacionalController::updateOperacional/$1', ['filter' => 'role:admin']);
    $routes->post('/delete/operacional', 'OperacionalController::deleteOperacional', ['filter' => ['csrf', 'role:admin']]);

    $routes->get('/update/usuario/(:num)', 'AdminController::updateUsuario/$1', ['filter' => 'role:admin']);
    $routes->post('/update/usuario', 'AdminController::updateUsuarioStore', ['as' => 'update.usuario.store', 'filter' => ['csrf', 'role:admin']]);
    
    $routes->post('/delete/usuario', 'AdminController::removeUsuario', ['filter' => ['csrf', 'role:admin']]);

    $routes->get('/search/operacional', 'OperacionalController::operacional', ['as' => 'search.operacional.get']);
    $routes->post('/search/operacional', 'OperacionalController::operacional', ['as' => 'search.operacional', 'filter' => 'csrf']);
    $routes->get('/uploads/(:segment)/(:segment)', 'OperacionalController::downloadUpload/$1/$2', ['as' => 'uploads.download']);
    $routes->get('/avatar/(:segment)', 'Home::avatar/$1', ['as' => 'avatar.show']);
    $routes->get('/admin/notifications', 'AdminController::notifications', ['as' => 'admin.notifications', 'filter' => 'role:superuser']);
    $routes->post('/admin/notifications', 'AdminController::notificationsUpdate', ['as' => 'admin.notifications.update', 'filter' => ['csrf', 'role:superuser']]);
    $routes->get('/admin/logs/cleanup/preview', 'AdminController::logsCleanupPreview', ['as' => 'admin.logs.cleanup.preview', 'filter' => 'role:superuser']);
    $routes->post('/admin/logs/cleanup', 'AdminController::logsCleanupDelete', ['as' => 'admin.logs.cleanup.delete', 'filter' => ['csrf', 'role:superuser']]);
    $routes->get('/password/force', 'Login::forcePasswordForm', ['as' => 'password.force']);
    $routes->post('/password/force', 'Login::forcePasswordStore', ['as' => 'password.force.store', 'filter' => ['csrf']]);
    $routes->get('/perfil', 'Home::perfil', ['as' => 'perfil']);
    $routes->post('/perfil', 'Home::perfilUpdate', ['as' => 'perfil.update', 'filter' => ['csrf']]);


    

});
