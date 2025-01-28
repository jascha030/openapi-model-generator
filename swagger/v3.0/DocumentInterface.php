<?php

declare(strict_types=1);

namespace Jascha030\OpenApi\V30;

interface DocumentInterface
{
    public function getOpenapi(): string;

    public function getInfo(): string;

    public function getPaths(): string;
}
