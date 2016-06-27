<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::plugin(
    'BitmessagePlugin',
    ['path' => '/bit-message-plugin'],
    function (RouteBuilder $routes) {
        $routes->fallbacks('DashedRoute');
    }
);
