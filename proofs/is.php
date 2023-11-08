<?php
declare(strict_types = 1);

use Innmind\Validation\Is;
use Innmind\BlackBox\Set;

return static function() {
    yield proof(
        'Is::string()',
        given(
            Set\Strings::any(),
            Set\Either::any(
                Set\Integers::any(),
                Set\RealNumbers::any(),
                Set\Elements::of(
                    true,
                    false,
                    null,
                    new \stdClass,
                ),
                Set\Sequence::of(Set\Strings::any()),
            ),
        ),
        static function($assert, $string, $other) {
            $assert->true(
                Is::string()->asPredicate()($string),
            );
            $assert->same(
                $string,
                Is::string()($string)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert->false(
                Is::string()->asPredicate()($other),
            );
            $assert->same(
                [['$', 'Value is not of type string']],
                Is::string()($other)->match(
                    static fn() => null,
                    static fn($failures) => $failures
                        ->map(static fn($failure) => [
                            $failure->path()->toString(),
                            $failure->message(),
                        ])
                        ->toList(),
                ),
            );
        },
    );

    yield proof(
        'Is::int()',
        given(
            Set\Integers::any(),
            Set\Either::any(
                Set\Strings::any(),
                Set\RealNumbers::any()->map(static fn($value) => $value * 1.1), // force being floats
                Set\Elements::of(
                    true,
                    false,
                    null,
                    new \stdClass,
                ),
                Set\Sequence::of(Set\Strings::any()),
            ),
        ),
        static function($assert, $int, $other) {
            $assert->true(
                Is::int()->asPredicate()($int),
            );
            $assert->same(
                $int,
                Is::int()($int)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert->false(
                Is::int()->asPredicate()($other),
            );
            $assert->same(
                [['$', 'Value is not of type int']],
                Is::int()($other)->match(
                    static fn() => null,
                    static fn($failures) => $failures
                        ->map(static fn($failure) => [
                            $failure->path()->toString(),
                            $failure->message(),
                        ])
                        ->toList(),
                ),
            );
        },
    );

    yield proof(
        'Is::float()',
        given(
            Set\RealNumbers::any()->map(static fn($value) => $value * 1.1), // force being floats
            Set\Either::any(
                Set\Strings::any(),
                Set\Integers::any(),
                Set\Elements::of(
                    true,
                    false,
                    null,
                    new \stdClass,
                ),
                Set\Sequence::of(Set\Strings::any()),
            ),
        ),
        static function($assert, $float, $other) {
            $assert->true(
                Is::float()->asPredicate()($float),
            );
            $assert->same(
                $float,
                Is::float()($float)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert->false(
                Is::float()->asPredicate()($other),
            );
            $assert->same(
                [['$', 'Value is not of type float']],
                Is::float()($other)->match(
                    static fn() => null,
                    static fn($failures) => $failures
                        ->map(static fn($failure) => [
                            $failure->path()->toString(),
                            $failure->message(),
                        ])
                        ->toList(),
                ),
            );
        },
    );

    yield proof(
        'Is::array()',
        given(
            Set\Sequence::of(Set\Either::any(
                Set\Strings::any(),
                Set\Integers::any(),
                Set\RealNumbers::any(),
                Set\Elements::of(
                    true,
                    false,
                    null,
                    new \stdClass,
                ),
            )),
            Set\Either::any(
                Set\Strings::any(),
                Set\Integers::any(),
                Set\RealNumbers::any(),
                Set\Elements::of(
                    true,
                    false,
                    null,
                    new \stdClass,
                ),
            ),
        ),
        static function($assert, $array, $other) {
            $assert->true(
                Is::array()->asPredicate()($array),
            );
            $assert->same(
                $array,
                Is::array()($array)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert->false(
                Is::array()->asPredicate()($other),
            );
            $assert->same(
                [['$', 'Value is not of type array']],
                Is::array()($other)->match(
                    static fn() => null,
                    static fn($failures) => $failures
                        ->map(static fn($failure) => [
                            $failure->path()->toString(),
                            $failure->message(),
                        ])
                        ->toList(),
                ),
            );
        },
    );

    yield proof(
        'Is::bool()',
        given(
            Set\Elements::of(true, false),
            Set\Either::any(
                Set\Strings::any(),
                Set\Integers::any(),
                Set\RealNumbers::any(),
                Set\Elements::of(
                    null,
                    new \stdClass,
                ),
                Set\Sequence::of(Set\Either::any(
                    Set\Strings::any(),
                    Set\Integers::any(),
                    Set\RealNumbers::any(),
                    Set\Elements::of(
                        true,
                        false,
                        null,
                        new \stdClass,
                    ),
                )),
            ),
        ),
        static function($assert, $bool, $other) {
            $assert->true(
                Is::bool()->asPredicate()($bool),
            );
            $assert->same(
                $bool,
                Is::bool()($bool)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert->false(
                Is::bool()->asPredicate()($other),
            );
            $assert->same(
                [['$', 'Value is not of type bool']],
                Is::bool()($other)->match(
                    static fn() => null,
                    static fn($failures) => $failures
                        ->map(static fn($failure) => [
                            $failure->path()->toString(),
                            $failure->message(),
                        ])
                        ->toList(),
                ),
            );
        },
    );
};
