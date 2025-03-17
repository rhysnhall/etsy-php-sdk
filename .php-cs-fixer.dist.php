<?php
$finder = Symfony\Component\Finder\Finder::create()
    ->in([
        __DIR__ . '/src',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);
return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder($finder);
