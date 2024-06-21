<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

/**
 * Cache dir and file location.
 */
$cacheDirectory = __DIR__ . '/.var/cache';
$cacheFile      = "{$cacheDirectory}/.php-cs-fixer.cache";

/**
 * Create a .cache dir if not already present.
 */
if (
    ! file_exists($cacheDirectory)
    && ! mkdir($cacheDirectory, 0700, true)
    && ! is_dir($cacheDirectory)
) {
    throw new RuntimeException(
        sprintf('Directory "%s" was not created', $cacheDirectory)
    );
}

return (new Config())
    ->setRiskyAllowed(true)
    ->setCacheFile($cacheFile)
    ->setRules([
        '@PER-CS'       => true,
        '@PER-CS:risky' => true,
    ])
    ->setFinder(
        Finder::create()
            ->in(__DIR__)
            ->exclude([
                'vendor',
                'tools',
                '.var',
            ])
    );
