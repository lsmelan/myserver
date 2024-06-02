<?php

$finder = (new PhpCsFixer\Finder())
    ->in(['src', 'tests'])
    ->exclude('vendor');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder);
