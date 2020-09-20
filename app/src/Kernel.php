<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/' . $this->environment . '/*.yaml');

        $path = dirname(__DIR__) . '/config/services.php';

        $services = $this->require($path);
        $services($container->withPath($path));
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $path = dirname(__DIR__) . '/config/routes.php';

        $routing = $this->require($path);
        $routing($routes->withPath($path));
    }

    /**
     * @psalm-suppress UnresolvableInclude
     * @psalm-suppress PossiblyUnusedParam
     */
    protected function require(string $path): callable
    {
        return require $path;
    }
}
