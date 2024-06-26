<?php

declare(strict_types=1);

namespace Jascha030\OpenApiModelGenerator\Helper;

use function array_reverse;

class Arr
{
    /**
     * @param iterable<int|string,mixed> $iterable
     *
     * @return mixed
     */
    public function last(iterable $iterable): mixed
    {
        if (!\is_array($iterable)) {
            $iterable = iterator_to_array($iterable);
        }

        return array_reverse($iterable)[0];
    }
}
