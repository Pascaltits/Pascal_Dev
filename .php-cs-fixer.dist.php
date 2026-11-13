<?php
/**
 * Style de code aligné sur celui du cœur Dotclear (PSR-12 + alignement des => et =).
 * `make cs` vérifie, `make cs-fix` corrige.
 */
declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/plugins')
    ->name('*.php')
    ->exclude(['vendor', 'node_modules']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@PSR12'                      => true,
        '@PHP82Migration'             => true,
        'array_syntax'                => ['syntax' => 'short'],
        'binary_operator_spaces'      => ['default' => 'align_single_space_minimal'],
        'blank_line_after_opening_tag' => false,
        'concat_space'                => ['spacing' => 'one'],
        'declare_strict_types'        => true,
        'no_unused_imports'           => true,
        'ordered_imports'             => ['sort_algorithm' => 'alpha'],
        'single_quote'                => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'yoda_style'                  => false,
    ])
    ->setFinder($finder);
