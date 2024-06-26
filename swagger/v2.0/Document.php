<?php

declare(strict_types=1);

namespace Jascha030\OpenApi\V20;

use Jascha030\OpenApi\V20\DocumentInterface;

class Document implements DocumentInterface
{
    private string $swagger;

    private Info $info;

    /**
     * @var array<string, mixed> $paths
     */
    private array $paths;

    public function setSwagger(string $swagger): self
    {
        $this->swagger = $swagger;

        return $this;
    }

    public function getSwagger(): string
    {
        return $this->swagger;
    }

    public function setInfo(Info $info): self
    {
        $this->info = $info;

        return $this;
    }

    /**
     * @return InfoInterface|Info
     */
    public function getInfo(): InfoInterface
    {
        return $this->info;
    }

    /**
     * @param array<string, mixed> $paths
     */
    public function setPaths(array $paths): self
    {
        $this->paths = $paths;

        return $this;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }
}
