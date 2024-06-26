<?php

declare(strict_types=1);

namespace Jascha030\OpenApi\V20;

interface DocumentInterface
{
    public function getSwagger(): string;

    public function getInfo(): InfoInterface;

    /**
     * @return array<string, mixed>
     */
    public function getPaths(): array;
}
