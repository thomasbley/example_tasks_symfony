<?php

use App\Controller\TasksController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('getTasks', '/v1/tasks')
        ->controller([TasksController::class, 'getTasks'])
        ->methods(['GET']);

    $routes->add('getTask', '/v1/tasks/{taskId}')
        ->controller([TasksController::class, 'getTask'])
        ->requirements(['taskId' => '\d+'])
        ->methods(['GET']);

    $routes->add('createTask', '/v1/tasks')
        ->controller([TasksController::class, 'createTask'])
        ->methods(['POST']);

    $routes->add('updateTask', '/v1/tasks/{taskId}')
        ->controller([TasksController::class, 'updateTask'])
        ->requirements(['taskId' => '\d+'])
        ->methods(['PUT']);

    $routes->add('deleteTask', '/v1/tasks/{taskId}')
        ->controller([TasksController::class, 'deleteTask'])
        ->requirements(['taskId' => '\d+'])
        ->methods(['DELETE']);
};
