<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setFinder(
        Finder::create()
            ->in(__DIR__)
            ->exclude([
                'vendor',
                'tools',
                '.var',
            ])
    )
    ->setRules([
        '@PER-CS'       => true,
        '@PER-CS:risky' => true,
    ]);
