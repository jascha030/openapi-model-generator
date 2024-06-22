<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Jascha030\OpenApiModelGenerator\Console\Command\Generate;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

return static function (ContainerBuilder $builder): ContainerBuilder {
    $builder->addDefinitions([
        'app.name' => static fn(): string => 'OpenAPI Model Generator',
        'app.version' => static fn(): string => '0.1.0',
        Application::class => static function (ContainerInterface $container): Application {
            $app = new Application($container->get('app.name'), $container->get('app.version'));

            $app->add($container->get(Generate::class));

            return $app;
        },
        Generate::class => static function (ContainerInterface $container): Generate {
            return new Generate();
        },
    ]);

    return $builder;
};
