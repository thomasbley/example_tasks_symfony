<?php

use App\Controller\TasksController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('createTask', '/v1/tasks')
        ->controller([TasksController::class, 'createTask'])
        ->methods(['POST']);
};
