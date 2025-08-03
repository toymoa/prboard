<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->in(__DIR__ . '/config')
    ->in(__DIR__ . '/public')
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PHP82Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,

        // PSR-12 엄격 준수
        'declare_strict_types' => true,
        'strict_param' => true,
        'strict_comparison' => true,

        // 타입 선언 강화
        'native_function_type_declaration_casing' => true,
        'native_constant_invocation' => true,
        'self_static_accessor' => true,
        'static_lambda' => true,

        // 코드 품질
        'final_class' => true,
        'final_public_method_for_abstract_class' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],

        // 배열
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],

        // 문서화
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_separation' => true,
        'phpdoc_summary' => true,
        'phpdoc_to_comment' => false,
        'phpdoc_var_annotation_correct_order' => true,

        // 보안
        'escape_implicit_backslashes' => true,
        'explicit_indirect_variable' => true,
        'explicit_string_variable' => true,

        // 성능
        'dir_constant' => true,
        'ereg_to_preg' => true,
        'error_suppression' => true,
        'fopen_flag_order' => true,
        'function_to_constant' => true,
        'is_null' => true,
        'modernize_types_casting' => true,
        'no_alias_functions' => true,
        'no_homoglyph_names' => true,
        'pow_to_exponentiation' => true,
        'random_api_migration' => true,
        'set_type_to_cast' => true,

        // 가독성
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'compact_nullable_typehint' => true,
        'logical_operators' => true,
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'simplified_if_return' => true,
        'ternary_to_null_coalescing' => true,

        // 예외 비활성화
        'php_unit_internal_class' => false,
        'php_unit_test_class_requires_covers' => false,
        'single_line_comment_style' => false,
    ])
    ->setFinder($finder);
