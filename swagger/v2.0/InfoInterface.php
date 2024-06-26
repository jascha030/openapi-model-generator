<?php

declare(strict_types=1);

namespace Jascha030\OpenApi\V20;

interface InfoInterface
{
    public function getVersion(): string;

    public function getTitle(): string;
}
