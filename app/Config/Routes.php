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
    
    $routes->get('/audiovisual', 'Home::index', ['as' => 'audiovisual']);    
    $routes->get('/usuarios', 'Home::usuarios',['as' => 'usuarios']);
    $routes->get('/calendario', 'Home::calendario');
    $routes->get('/proposta', 'Home::proposta', ['as' => 'proposta']);
    $routes->get('/rider', 'Home::rider', ['as' => 'rider']);
    $routes->get('/logs', 'Home::logs', ['as' => 'logs']);
    
    $routes->get('/cadastro/proposta', 'Home::cadastroProposta', ['as' => 'cadastro.proposta']);
    $routes->post('/cadastro/proposta', 'Home::cadastroPropostaStore', ['as' => 'cadastro.proposta.store']);
    $routes->get('/cadastro/usuario', 'Home::cadastroUsuario', ['as' => 'usuario']);
    $routes->post('/cadastro/usuario', 'Home::usuarioStore', ['as' => 'usuario.store', 'filter' => 'csrf']);
    
    $routes->get('/', 'Home::operacional', ['as' => 'home']);
    $routes->get('/operacional/lista', 'Home::operacionalLista', ['as' => 'operacional.lista']);
    $routes->get('/operacional/planejamento/cadastro', 'Home::cadastroPlanejamento');
    $routes->post('/operaqconal/planejamento/cadastro', 'Home::cadastroPLanejamentoStore', ['as' => 'cadastro.planejamento.store']);
    $routes->get('/operacional/planejamento/lista', 'Home::planejamentoLista', ['as' => 'planejamento.lista']);
    $routes->get('/cadastro/operacional', 'Home::cadastroOperacional', ['as' => 'cadastro.operacional']);
    $routes->post('/cadastro/operacional', 'Home::cadastroOperacionalStore', ['as' => 'cadastro.operacional.store']);
    $routes->post('/update/operacional', 'Home::updateOperacionalStore', ['as' => 'update.operacional.store']);
    $routes->post('/cadastro/evento', 'Home::cadastroEventoStore', ['as' => 'cadastro.evento.store']);
    $routes->get('/update/operacional/(:num)', 'Home::updateOperacional/$1');
    $routes->post('/delete/operacional', 'Home::deleteOperacional');

    $routes->get('/update/usuario/(:num)', 'Home::updateUsuario/$1');
    $routes->get('/update/proposta/(:num)', 'Home::updateProposta/$1');    
    $routes->post('/update/proposta', 'Home::updatePropostaStore', ['as' => 'update.proposta.store']);
    $routes->post('/update/usuario', 'Home::updateUsuarioStore', ['as' => 'update.usuario.store']);
    
    $routes->get('/delete/usuario/(:num)', 'Home::removeUsuario/$1');
    $routes->post('/delete/proposta', 'Home::removeProposta');

    $routes->post('/search/operacional', 'Home::operacional', ['as' => 'search.operacional']);

    

});