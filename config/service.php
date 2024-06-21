<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Jascha030\OpenApiModelGenerator\Parser;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Serializer;

return static function (ContainerBuilder $builder): ContainerBuilder {
    $builder->addDefinitions([
        Parser::class => static function (ContainerInterface $container): Parser {
            return new Parser($container->get(Serializer::class));
        },
    ]);

    return $builder;
};
