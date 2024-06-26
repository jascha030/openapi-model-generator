<?php

declare(strict_types=1);

namespace Jascha030\OpenApi\V20;

class Info implements InfoInterface
{
    private string $version;

    private string $title;

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
