<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return new Config()
    ->setUnsupportedPhpVersionAllowed(true) // <-- Allows running on newer/unsupported PHP versions
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'braces_position' => [
            'anonymous_classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
        ],
        'concat_space' => ['spacing' => 'none'],
        'function_declaration' => ['closure_fn_spacing' => 'one'],
        'new_with_parentheses' => ['anonymous_class' => false, 'named_class' => false],
        'no_unused_imports' => true,
        'not_operator_with_successor_space' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'php_unit_method_casing' => ['case' => 'snake_case'],
        'single_line_empty_body' => true,
        'single_quote' => true,
        'trailing_comma_in_multiline' => true,
        'unary_operator_spaces' => true,
    ])
    // ?? by default, Fixer looks for `*.php` files excluding `./vendor/` - here, you can groom this config
    ->setFinder(
        new Finder()
            // ?? root folder to check
            ->in(__DIR__)
            ->exclude(['bootstrap/cache', 'node_modules', 'public', 'storage', 'vendor'])
    )
;
