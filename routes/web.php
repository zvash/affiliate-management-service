<?php

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

$router->get('/who', function () use ($router) {
    return 'Affiliate Management Service';
});

$router->get('/clicks/token/{aff_click_id}/order/{adv_sub}/amount/{sale_amount}', 'Api\V1\ClickController@registerResponse');

$router->group(['prefix' => 'api/v1'], function ($router) {

    $router->group(['namespace' => 'Api\V1'], function ($router) {

        $router->group(['middleware' => 'auth'], function ($router) {


        });

        $router->group(['middleware' => 'admin'], function ($router) {
            $router->post('admin/services/register', 'ServiceController@register');
            $router->get('admin/services/all', 'ServiceController@getAll');
        });

        $router->group(['middleware' => 'trusted'], function ($router) {
            $router->post('clicks/create', 'ClickController@create');
            $router->get('clicks/all', 'ClickController@all');
            $router->post('clicks/claim', 'ClickController@claim');

            $router->post('claims/create', 'ClaimController@create');
            $router->get('claims/all', 'ClaimController@all');
            $router->post('claims/accept/referral', 'ClaimController@acceptReferral');
            $router->post('claims/claim', 'ClaimController@claim');
        });
    });

});