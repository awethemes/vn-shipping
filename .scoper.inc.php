<?php
/**
 * PHP-Scoper configuration file.
 *
 * @see https://github.com/humbug/php-scoper#configuration
 *
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

use Isolated\Symfony\Component\Finder\Finder;

return [
    'prefix' => 'VNShipping\\Vendor',

    // See: https://github.com/humbug/php-scoper#finders-and-paths.
    'finders' => [
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->ignoreDotFiles(true)
	        ->name(['*.php', 'LICENSE', 'composer.json'])
            ->in('vendor/symfony/options-resolver'),

        // Main composer.json file so that we can build a classmap.
        Finder::create()
            ->append(['composer.json']),
    ],

    // See: https://github.com/humbug/php-scoper#patchers.
    'patchers' => [],

    // See https://github.com/humbug/php-scoper#whitelist.
    'whitelist' => [],

    // See https://github.com/humbug/php-scoper#constants--classes--functions-from-the-global-namespace.
    'whitelist-global-classes' => false,
    'whitelist-global-functions' => false,
    'whitelist-global-constants' => false,
];
