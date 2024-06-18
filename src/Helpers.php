<?php

declare(strict_types=1);

namespace Jascha\OpenApiModelGenerator;

use function array_reverse;

function last(iterable $iterable): mixed
{
    if (! \is_array($iterable)) {
        $iterable = iterator_to_array($iterable);
    }

    return array_reverse($iterable)[0];
}
