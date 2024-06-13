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

// $router->get('/', function () use ($router) {
//     return $router->app->version();
// });

// $router->group(['middleware' => 'cors'], function ($router) {
    
//     $router->post('/login', 'AuthController@login');
//     $router->get('/logout', 'AuthController@logout');
//     $router->get('/profile', 'AuthController@me');  
    
//     $router->get('/stuff', 'StuffController@index');
//     $router->post('/stuff', 'StuffController@store');
//     $router->get('/stuff/trash', 'StuffController@deleted');
//     $router->delete('/stuff/permanent', 'StuffController@permanentDeleteAll');
//     $router->delete('/stuff/permanent/{id}', 'StuffController@permanentDelete');
//     $router->put('/stuff/restore', 'StuffController@restoreAll');
//     $router->put('/stuff/restore/{id}', 'StuffController@restore');
//     $router->get('/stuff/{id}', 'StuffController@show');
//     $router->put('/stuff/{id}', 'StuffController@update');
//     $router->delete('/stuff/{id}', 'StuffController@destroy');
    
//     $router->get('/stuff-stock', 'StuffStockController@index');
//     $router->post('/stuff-stock', 'StuffStockController@store');
//     $router->get('/stuff-stock/trash', 'StuffStockController@deleted');
//     $router->delete('/stuff-stock/permanent', 'StuffStockController@permanentDeleteAll');
//     $router->delete('/stuff-stock/permanent/{id}', 'StuffStockController@permanentDelete');
//     $router->put('/stuff-stock/restore', 'StuffStockController@restoreAll');
//     $router->put('/stuff-stock/restore/{id}', 'StuffStockController@restore');
//     $router->get('/stuff-stock/{id}', 'StuffStockController@show');
//     $router->put('/stuff-stock/{id}', 'StuffStockController@update');
//     $router->delete('/stuff-stock/{id}', 'StuffStockController@destroy');
    
//     $router->get('/user', 'UserController@index');
//     $router->post('/user', 'UserController@store');
//     $router->get('/user/trash', 'UserController@deleted');
//     $router->delete('/user/permanent', 'UserController@permanentDeleteAll');
//     $router->delete('/user/permanent/{id}', 'UserController@permanentDelete');
//     $router->put('/user/restore', 'UserController@restoreAll');
//     $router->put('/user/restore/{id}', 'UserController@restore');
//     $router->get('/user/{id}', 'UserController@show');
//     $router->put('/user/{id}', 'UserController@update');
//     $router->delete('/user/{id}', 'UserController@destroy');
    
//     $router->get('/inboundStuff', 'InboundStuffController@index');
//     $router->post('/inboundStuff', 'InboundStuffController@store');
//     $router->get('/inboundStuff/trash', 'InboundStuffController@deleted');
//     $router->delete('/inboundStuff/permanent', 'InboundStuffController@permanentDeleteAll');
//     $router->delete('/inboundStuff/permanent/{id}', 'InboundStuffController@permanentDelete');
//     $router->put('/inboundStuff/restore', 'InboundStuffController@restoreAll');
//     $router->put('/inboundStuff/restore/{id}', 'InboundStuffController@restore');
//     $router->get('/inboundStuff/{id}', 'InboundStuffController@show');
//     $router->put('/inboundStuff/{id}', 'InboundStuffController@update');
//     $router->delete('/inboundStuff/{id}', 'InboundStuffController@destroy');
    
//     $router->get('/lending', 'LendingController@index');
//     $router->post('/lending', 'LendingController@store');
//     $router->get('/lending/trash', 'LendingController@deleted');
//     $router->delete('/lending/permanent', 'LendingController@permanentDeleteAll');
//     $router->delete('/lending/permanent/{id}', 'LendingController@permanentDelete');
//     $router->put('/lending/restore', 'LendingController@restoreAll');
//     $router->put('/lending/restore/{id}', 'LendingController@restore');
//     $router->get('/lending/{id}', 'LendingController@show');
//     $router->put('/lending/{id}', 'LendingController@update');
//     $router->delete('/lending/{id}', 'LendingController@destroy');
    
//     $router->post('/restorations/{lending_id}', 'RestorationController@store');
// });



$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => 'cors'], function ($router) {

    $router->group(['prefix' => 'lendings'], function() use ($router) {
        $router->get('/', 'LendingController@index');
        $router->post('/create', 'LendingController@store');
        $router->get('/{id}', 'LendingController@show');
    });
    
    $router->group(['prefix' => 'restorations'], function() use ($router) {
        $router->post('/{lending_id}', 'RestorationController@store');
    });
    
    $router->group(['prefix' => 'stuff'], function() use ($router) {
        $router->get('/', 'StuffController@index');
        $router->post('/create', 'StuffController@store');
        $router->get('/trash', 'StuffController@deleted');
    
        $router->get('/show/{id}', 'StuffController@show');
        $router->put('/update/{id}', 'StuffController@update');
        $router->delete('/destroy/{id}', 'StuffController@destroy');
        $router->put('/restore/{id}', 'StuffController@restore');
        $router->put('/restore', 'StuffController@restoreAll');
        $router->delete('/permanent/{id}', 'StuffController@permanentDelete');
        $router->delete('/permanent', 'StuffController@permanentDeleteAll');
    });
    
    $router->group(['prefix' => 'user'], function() use ($router) {
        $router->get('/', 'UserController@index');
        $router->post('/create', 'UserController@store');
        $router->get('/trash', 'UserController@deleted');
    
        $router->get('/show/{id}', 'UserController@show');
        $router->put('/update/{id}', 'UserController@update');
        $router->delete('/destroy/{id}', 'UserController@destroy');
        $router->put('/restore/{id}', 'UserController@restore');
        $router->put('/restore', 'UserController@restoreAll');
        $router->delete('/permanent/{id}', 'UserController@permanentDelete');
        $router->delete('/permanent', 'UserController@permanentDeleteAll');
    });
    
    $router->group(['prefix' => 'inbound'], function() use ($router) {
        $router->get('/', 'InboundStuffController@index');
        $router->post('/create', 'InboundStuffController@store');
        $router->get('/trash', 'InboundStuffController@deleted');
        $router->delete('/permanent', 'InboundStuffController@permanentDeleteAll');
        $router->delete('/permanent/{id}', 'InboundStuffController@permanentDelete');
        $router->delete('/destroy/{id}', 'InboundStuffController@destroy');
        
        $router->put('/restore/{id}', 'InboundStuffController@restore');
        $router->put('/restore', 'InboundStuffController@restoreAll');
    
        $router->get('/{id}', 'InboundStuffController@show');
        $router->put('/{id}', 'InboundStuffController@update');
        $router->delete('/{id}', 'InboundStuffController@destroy');
    });
    
    
    $router->get('/StuffStock', 'StuffStockController@index');
    $router->post('/StuffStock/create', 'StuffStockController@store');
    $router->get('/StuffStock/trash', 'StuffStockController@deleted');
    $router->delete('/StuffStock/permanent', 'StuffStockController@permanentDeleteAll');
    $router->delete('/StuffStock/permanent/{id}', 'StuffStockController@permanentDelete');
    
    $router->put('/StuffStock/restore/{id}', 'StuffStockController@restore');
    $router->put('/StuffStock/restore', 'StuffStockController@restoreAll');
    
    $router->get('/StuffStock/{id}', 'StuffStockController@show');
    $router->put('/StuffStock/{id}', 'StuffStockController@update');
    $router->delete('/StuffStock/{id}', 'StuffStockController@destroy');

    $router->post('/login', 'AuthController@login');
    $router->get('/profile', 'AuthController@me');
    $router->get('/logout', 'AuthController@logout');

    
});





