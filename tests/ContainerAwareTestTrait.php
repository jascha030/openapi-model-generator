<?php

declare(strict_types=1);

namespace Jascha030\OpenApiModelGenerator;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;


/**
 * Trait ContainerAwareTestTrait
 *
 * @internal
 */
trait ContainerAwareTestTrait
{
    private function getContainer(): ContainerInterface
    {
        $path = dirname(__DIR__) . '/config';

        $builder = new ContainerBuilder();
        $dir = new DirectoryIterator($path);

        foreach ($dir as $fileinfo) {
            $builder = (require_once $fileinfo->getRealpath())($builder);
        }

        return $builder->build();
    }
}
