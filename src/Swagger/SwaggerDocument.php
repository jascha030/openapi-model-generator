<?php

declare(strict_types=1);

namespace Jascha030\OpenApiModelGenerator\Swagger;

use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

#[DiscriminatorMap(typeProperty: 'swagger', mapping: [
    '2.0' => SwaggerDocumentV2::class,
])]
abstract class SwaggerDocument {}
