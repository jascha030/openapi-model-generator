<?php

declare(strict_types=1);

namespace Jascha030\OpenApiModelGenerator\Iterator;

use IteratorAggregate;
use Traversable;

class LazyIterator implements IteratorAggregate
{
    private \Closure $iteratorFactory;

    public function __construct(callable $factory)
    {
        $this->iteratorFactory = $factory(...);
    }

    public function getIterator(): Traversable
    {
        yield from ($this->iteratorFactory)();
    }
}
