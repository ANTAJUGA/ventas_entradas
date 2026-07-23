<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Catalogo::index');
$routes->get('eventos/(:segment)', 'Catalogo::show/$1');
$routes->get('ingresar', 'ClienteAuth::login');
$routes->post('ingresar', 'ClienteAuth::authenticate');
$routes->get('registro', 'ClienteAuth::register');
$routes->post('registro', 'ClienteAuth::store');
$routes->post('salir', 'ClienteAuth::logout');
$routes->get('mi-cuenta/entradas', 'MiCuenta::entradas', ['filter' => 'clientAuth']);
$routes->get('comprar/(:num)', 'Compra::seleccionar/$1', ['filter' => 'clientAuth']);
$routes->post('comprar/(:num)/revisar', 'Compra::revisar/$1', ['filter' => 'clientAuth']);
$routes->post('comprar/confirmar', 'Compra::confirmar', ['filter' => 'clientAuth']);
$routes->post('comprar/reservar', 'Compra::reservar', ['filter' => 'clientAuth']);
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::authenticate');
$routes->post('logout', 'Auth::logout');
$routes->get('admin', 'Dashboard::index', ['filter' => 'adminAuth']);
$routes->get('admin/ejemplo-malos-olores', 'Dashboard::reporteVentasConMalosOlores', ['filter' => 'adminAuth:administrador']);

$routes->group('admin/ventas', ['filter' => 'adminAuth:administrador,vendedor'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Admin\Ventas::index');
    $routes->get('nueva', 'Admin\Ventas::new');
    $routes->post('/', 'Admin\Ventas::create');
    $routes->get('(:num)', 'Admin\Ventas::show/$1');
    $routes->post('(:num)/confirmar-pago', 'Admin\Ventas::confirmPayment/$1');
    $routes->get('entradas/(:num)/imprimir', 'Admin\Ventas::printTicket/$1');
});

$routes->group('admin/accesos', ['filter' => 'adminAuth:administrador,control-acceso'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Admin\Accesos::index');
    $routes->post('validar', 'Admin\Accesos::validateTicket');
});

$routes->group('admin/eventos', ['filter' => 'adminAuth:administrador,organizador'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Admin\Eventos::index');
    $routes->get('nuevo', 'Admin\Eventos::new');
    $routes->post('/', 'Admin\Eventos::create');
    $routes->get('(:num)/editar', 'Admin\Eventos::edit/$1');
    $routes->post('(:num)', 'Admin\Eventos::update/$1');
    $routes->post('(:num)/publicar', 'Admin\Eventos::publish/$1');
    $routes->post('(:num)/eliminar', 'Admin\Eventos::delete/$1');
    $routes->get('(:num)/funciones/nueva', 'Admin\Funciones::new/$1');
    $routes->post('(:num)/funciones', 'Admin\Funciones::create/$1');
});

$routes->group('admin/funciones', ['filter' => 'adminAuth:administrador,organizador'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Admin\Funciones::index');
    $routes->get('(:num)/editar', 'Admin\Funciones::edit/$1');
    $routes->post('(:num)', 'Admin\Funciones::update/$1');
    $routes->post('(:num)/eliminar', 'Admin\Funciones::delete/$1');
    $routes->get('(:num)/tipos-entrada/nuevo', 'Admin\TiposEntrada::new/$1');
    $routes->post('(:num)/tipos-entrada', 'Admin\TiposEntrada::create/$1');
});

$routes->group('admin/tipos-entrada', ['filter' => 'adminAuth:administrador,organizador'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Admin\TiposEntrada::index');
    $routes->get('(:num)/editar', 'Admin\TiposEntrada::edit/$1');
    $routes->post('(:num)', 'Admin\TiposEntrada::update/$1');
    $routes->post('(:num)/eliminar', 'Admin\TiposEntrada::delete/$1');
});

$routes->group('admin/descuentos', ['filter' => 'adminAuth:administrador,organizador'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Admin\Descuentos::index');
    $routes->get('nuevo', 'Admin\Descuentos::new');
    $routes->post('/', 'Admin\Descuentos::create');
    $routes->get('(:num)/editar', 'Admin\Descuentos::edit/$1');
    $routes->post('(:num)', 'Admin\Descuentos::update/$1');
    $routes->post('(:num)/eliminar', 'Admin\Descuentos::delete/$1');
});

$routes->group('admin/usuarios', ['filter' => 'adminAuth:administrador'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Admin\Usuarios::index');
    $routes->get('nuevo', 'Admin\Usuarios::new');
    $routes->post('/', 'Admin\Usuarios::create');
    $routes->get('(:num)/editar', 'Admin\Usuarios::edit/$1');
    $routes->post('(:num)', 'Admin\Usuarios::update/$1');
    $routes->get('(:num)/password', 'Admin\Usuarios::password/$1');
    $routes->post('(:num)/password', 'Admin\Usuarios::updatePassword/$1');
    $routes->post('(:num)/bloqueo', 'Admin\Usuarios::toggleBlock/$1');
    $routes->post('(:num)/eliminar', 'Admin\Usuarios::delete/$1');
});
