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
    
    $routes->get('/usuarios', 'Home::usuarios',['as' => 'usuarios', 'filter' => 'role:admin']);
    $routes->get('/calendario', 'Home::calendario');    
    $routes->get('/rider', 'Home::rider', ['as' => 'rider']);
    $routes->get('/logs', 'Home::logs', ['as' => 'logs', 'filter' => 'role:superuser']);
    
    $routes->get('/cadastro/usuario', 'Home::cadastroUsuario', ['as' => 'usuario', 'filter' => 'role:admin']);
    $routes->post('/cadastro/usuario', 'Home::usuarioStore', ['as' => 'usuario.store', 'filter' => ['csrf', 'role:admin']]);
    
    $routes->get('/', 'Home::operacional', ['as' => 'home']);
    $routes->get('/operacional/lista', 'Home::operacionalLista', ['as' => 'operacional.lista']);
    $routes->get('/operacional/calendario', 'Home::calendarioOperacional', ['as' => 'calendario.operacional']);
    $routes->get('/operacional/geradores', 'Home::geradoresManutencao', ['as' => 'geradores.index']);
    $routes->post('/operacional/geradores/store', 'Home::geradoresManutencaoStore', ['as' => 'geradores.store', 'filter' => 'csrf']);
    $routes->post('/operacional/geradores/update', 'Home::geradoresManutencaoUpdate', ['as' => 'geradores.update', 'filter' => 'csrf']);
    $routes->post('/operacional/geradores/delete', 'Home::geradoresManutencaoDelete', ['as' => 'geradores.delete', 'filter' => 'csrf']);
    $routes->get('/operacional/planejamento/cadastro', 'Home::cadastroPlanejamento');
    $routes->post('/operacional/planejamento/cadastro', 'Home::cadastroPlanejamentoStore', ['as' => 'cadastro.planejamento.store', 'filter' => 'csrf']);
    $routes->get('/operacional/planejamento/lista', 'Home::planejamentoLista', ['as' => 'planejamento.lista']);
    $routes->get('/cadastro/operacional', 'Home::cadastroOperacional', ['as' => 'cadastro.operacional']);
    $routes->post('/cadastro/operacional', 'Home::cadastroOperacionalStore', ['as' => 'cadastro.operacional.store', 'filter' => 'csrf']);
    $routes->post('/operacional/gerador/manutencao', 'Home::registrarManutencaoGerador', ['as' => 'gerador.manutencao.store', 'filter' => 'csrf']);
    $routes->post('/update/operacional', 'Home::updateOperacionalStore', ['as' => 'update.operacional.store', 'filter' => 'csrf']);
    $routes->get('/update/operacional/(:num)', 'Home::updateOperacional/$1');
    $routes->post('/delete/operacional', 'Home::deleteOperacional', ['filter' => 'csrf']);

    $routes->get('/update/usuario/(:num)', 'Home::updateUsuario/$1', ['filter' => 'role:admin']);
    $routes->post('/update/usuario', 'Home::updateUsuarioStore', ['as' => 'update.usuario.store', 'filter' => ['csrf', 'role:admin']]);
    
    $routes->post('/delete/usuario', 'Home::removeUsuario', ['filter' => ['csrf', 'role:admin']]);

    $routes->get('/search/operacional', 'Home::operacional', ['as' => 'search.operacional.get']);
    $routes->post('/search/operacional', 'Home::operacional', ['as' => 'search.operacional', 'filter' => 'csrf']);
    $routes->get('/uploads/(:segment)/(:segment)', 'Home::downloadUpload/$1/$2', ['as' => 'uploads.download']);
    $routes->get('/avatar/(:segment)', 'Home::avatar/$1', ['as' => 'avatar.show']);
    $routes->get('/admin/notifications', 'Home::notifications', ['as' => 'admin.notifications', 'filter' => 'role:superuser']);
    $routes->post('/admin/notifications', 'Home::notificationsUpdate', ['as' => 'admin.notifications.update', 'filter' => ['csrf', 'role:superuser']]);
    $routes->get('/admin/logs/cleanup/preview', 'Home::logsCleanupPreview', ['as' => 'admin.logs.cleanup.preview', 'filter' => 'role:superuser']);
    $routes->post('/admin/logs/cleanup', 'Home::logsCleanupDelete', ['as' => 'admin.logs.cleanup.delete', 'filter' => ['csrf', 'role:superuser']]);
    $routes->get('/password/force', 'Login::forcePasswordForm', ['as' => 'password.force']);
    $routes->post('/password/force', 'Login::forcePasswordStore', ['as' => 'password.force.store', 'filter' => ['csrf']]);
    $routes->get('/perfil', 'Home::perfil', ['as' => 'perfil']);
    $routes->post('/perfil', 'Home::perfilUpdate', ['as' => 'perfil.update', 'filter' => ['csrf']]);


    

});
