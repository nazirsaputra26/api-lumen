<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/login', 'AuthController@authenticate');

$router->get('/stuff', 'StuffController@index');
$router->post('/stuff', 'StuffController@store');
$router->get('/stuff/trash', 'StuffController@deleted');
$router->delete('/stuff/permanent', 'StuffController@permanentDeleteAll');
$router->delete('/stuff/permanent/{id}', 'StuffController@permanentDelete');
$router->put('/stuff/restore', 'StuffController@restoreAll');
$router->put('/stuff/restore/{id}', 'StuffController@restore');
$router->get('/stuff/{id}', 'StuffController@show');
$router->patch('/stuff/{id}', 'StuffController@update');
$router->delete('/stuff/{id}', 'StuffController@destroy');

$router->get('/stuff-stock', 'StuffStockController@index');
$router->post('/stuff-stock', 'StuffStockController@store');
$router->get('/stuff-stock/trash', 'StuffStockController@deleted');
$router->delete('/stuff-stock/permanent', 'StuffStockController@permanentDeleteAll');
$router->delete('/stuff-stock/permanent/{id}', 'StuffStockController@permanentDelete');
$router->put('/stuff-stock/restore', 'StuffStockController@restoreAll');
$router->put('/stuff-stock/restore/{id}', 'StuffStockController@restore');
$router->get('/stuff-stock/{id}', 'StuffStockController@show');
$router->patch('/stuff-stock/{id}', 'StuffStockController@update');
$router->delete('/stuff-stock/{id}', 'StuffStockController@destroy');

$router->get('/user', 'UserController@index');
$router->post('/user', 'UserController@store');
$router->get('/user/trash', 'UserController@deleted');
$router->delete('/user/permanent', 'UserController@permanentDeleteAll');
$router->delete('/user/permanent/{id}', 'UserController@permanentDelete');
$router->put('/user/restore', 'UserController@restoreAll');
$router->put('/user/restore/{id}', 'UserController@restore');
$router->get('/user/{id}', 'UserController@show');
$router->patch('/user/{id}', 'UserController@update');
$router->delete('/user/{id}', 'UserController@destroy');

$router->get('/inboundStuff', 'InboundStuffController@index');
$router->post('/inboundStuff', 'InboundStuffController@store');
$router->get('/inboundStuff/trash', 'InboundStuffController@deleted');
$router->delete('/inboundStuff/permanent', 'InboundStuffController@permanentDeleteAll');
$router->delete('/inboundStuff/permanent/{id}', 'InboundStuffController@permanentDelete');
$router->put('/inboundStuff/restore', 'InboundStuffController@restoreAll');
$router->put('/inboundStuff/restore/{id}', 'InboundStuffController@restore');
$router->get('/inboundStuff/{id}', 'InboundStuffController@show');
$router->patch('/inboundStuff/{id}', 'InboundStuffController@update');
$router->delete('/inboundStuff/{id}', 'InboundStuffController@destroy');

$router->get('/lending', 'LendingController@index');
$router->post('/lending', 'LendingController@store');
$router->get('/lending/trash', 'LendingController@deleted');
$router->delete('/lending/permanent', 'LendingController@permanentDeleteAll');
$router->delete('/lending/permanent/{id}', 'LendingController@permanentDelete');
$router->put('/lending/restore', 'LendingController@restoreAll');
$router->put('/lending/restore/{id}', 'LendingController@restore');
$router->get('/lending/{id}', 'LendingController@show');
$router->patch('/lending/{id}', 'LendingController@update');
$router->delete('/lending/{id}', 'LendingController@destroy');



// $router->get('/stuff/trash', 'StuffController@deleted');
// $router->delete('/stuff/permanent', 'StuffController@permanentDeleteAll');
// $router->delete('/stuff/permanent/{id}', 'StuffController@permanentDelete');
// $router->put('/stuff/restore', 'StuffController@restoreAll');
// $router->put('/stuff/restore/{id}', 'StuffController@restore');


