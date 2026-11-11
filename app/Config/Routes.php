<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
// $routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);


/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.

$routes->get('/', 'Auth::index');
$routes->get('login', 'Auth::index');
$routes->post('login', 'Auth::login');
$routes->get('logout', 'Auth::logout');

$routes->group('relatorios', ['filter' => 'login'], function ($routes) {
    $routes->get('', 'Relatorios::index');
    $routes->get('exportar', 'Relatorios::exportar');
});

$routes->group('home', ['filter' => 'login'], function ($routes) {
    $routes->get('/', 'Home::index');
});

$routes->group('licenciandos', ['filter' => 'login'], function ($routes) {
    $routes->get('', 'Licenciandos::index');
    $routes->get('adicionar', 'Licenciandos::adicionar');
    $routes->post('adicionar', 'Licenciandos::adicionar');
    $routes->get('importar', 'Licenciandos::importar');
    $routes->post('importar', 'Licenciandos::importar');
    $routes->get('importar/modelo', 'Licenciandos::modelo_importacao');
    $routes->post('importar/confirmar', 'Licenciandos::confirmar_importacao');
    $routes->get('import_results', 'Licenciandos::import_results');
    $routes->get('editar/(:num)/(:num)', 'Licenciandos::editar/$1/$2');
    $routes->post('editar/(:num)/(:num)', 'Licenciandos::editar/$1/$2');
    $routes->post('setor_select/(:num)', 'Licenciandos::setor_select/$1');
    $routes->post('salvar', 'Licenciandos::salvar');
    $routes->get('excluir/(:num)', 'Licenciandos::excluir/$1');
    $routes->post('excluir/(:num)', 'Licenciandos::excluir/$1');
});

$routes->group('documentos', ['filter' => 'login', 'filter' => 'admin'], function ($routes) {
    $routes->get('', 'Documentos::index');
    $routes->get('adicionar', 'Documentos::adicionar');
    $routes->post('adicionar', 'Documentos::adicionar');
    $routes->get('editar/(:num)', 'Documentos::editar/$1');
    $routes->post('editar/(:num)', 'Documentos::editar/$1');
    $routes->get('layout_documentos', 'Documentos::layout_documentos');
    $routes->post('salvar_layout', 'Documentos::salvar_layout');
    $routes->get('cracha/(:num)/(:num)', 'Documentos::cracha/$1/$2');
    $routes->get('emitir/(:num)/(:num)/(:num)', 'Documentos::emitir/$1/$2/$3');
    $routes->post('salvar', 'Documentos::salvar');
    $routes->get('excluir/(:num)', 'Documentos::excluir/$1');
    $routes->post('excluir/(:num)', 'Documentos::excluir/$1');
});

$routes->group('universidades', ['filter' => 'login', 'filter' => 'admin'], function ($routes) {
    $routes->get('', 'Universidades::index');
    $routes->get('adicionar', 'Universidades::adicionar');
    $routes->post('adicionar', 'Universidades::adicionar');
    $routes->get('editar/(:num)', 'Universidades::editar/$1');
    $routes->post('editar/(:num)', 'Universidades::editar/$1');
    $routes->post('salvar', 'Universidades::salvar');
    $routes->get('excluir/(:num)', 'Universidades::excluir/$1');
    $routes->post('excluir/(:num)', 'Universidades::excluir/$1');
});

$routes->group('setores', ['filter' => 'login', 'filter' => 'admin'], function ($routes) {
    $routes->get('', 'Setores::index');
    $routes->get('adicionar', 'Setores::adicionar');
    $routes->post('adicionar', 'Setores::adicionar');
    $routes->get('editar/(:num)', 'Setores::editar/$1');
    $routes->post('salvar', 'Setores::salvar');
    $routes->get('excluir/(:num)', 'Setores::excluir/$1');
});

$routes->group('usuarios', ['filter' => 'login', 'filter' => 'admin'], function ($routes) {
    $routes->get('', 'Usuarios::index');
    $routes->add('adicionar', 'Usuarios::adicionar');
    $routes->post('adicionar', 'Usuarios::adicionar');
    $routes->add('editar/(:num)', 'Usuarios::editar/$1');
    $routes->post('salvar', 'Usuarios::salvar');
    $routes->get('excluir/(:num)', 'Usuarios::excluir/$1');
    $routes->post('excluir/(:num)', 'Usuarios::excluir/$1');
});




/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
