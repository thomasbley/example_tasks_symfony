<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Listener\ExceptionListener;
use App\Resolver\CustomerResolver;
use App\Resolver\ParameterResolver;
use App\Service\JwtManager;
use App\Service\PdoManager;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()      // Automatically injects dependencies in your services.
        ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc.
    ;

    $services->load('App\\Command\\', '../src/Command/*');

    $services->load('App\\Repository\\', '../src/Repository/*');
    $services->load('App\\Serializer\\', '../src/Serializer/*');
    $services->load('App\\Service\\', '../src/Service/*');

    $services->load('App\\Controller\\', '../src/Controller/')
        ->tag('controller.service_arguments');

    $services->set(PdoManager::class)
        ->args([
            '$dsn' => '%env(DATABASE_DSN)%',
            '$username' => '%env(DATABASE_USERNAME)%',
            '$password' => '%env(DATABASE_PASSWORD)%',
        ]);

    $services->set(JwtManager::class)
        ->args([
            '$publicKey' => '%env(JWT_PUBLIC_KEY)%',
            '$privateKey' => '%env(JWT_PRIVATE_KEY)%',
        ]);

    $services->set(ExceptionListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.exception']);

    $services->set(CustomerResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 50]);

    $services->set(ParameterResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 50]);
};
