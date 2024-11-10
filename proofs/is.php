<?php
declare(strict_types = 1);

use Innmind\Validation\Is;
use Innmind\Immutable\Str;
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
                    new stdClass,
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
        'Is::string()->withFailure()',
        given(
            Set\Strings::atLeast(1),
            Set\Either::any(
                Set\Integers::any(),
                Set\RealNumbers::any(),
                Set\Elements::of(
                    true,
                    false,
                    null,
                    new stdClass,
                ),
                Set\Sequence::of(Set\Strings::any()),
            ),
        ),
        static function($assert, $message, $other) {
            $assert->same(
                [['$', $message]],
                Is::string()->withFailure($message)($other)->match(
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
                    new stdClass,
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
                    new stdClass,
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
            Set\Either::any(
                Set\Sequence::of(Set\Either::any(
                    Set\Strings::any(),
                    Set\Integers::any(),
                    Set\RealNumbers::any(),
                    Set\Elements::of(
                        true,
                        false,
                        null,
                        new stdClass,
                    ),
                )),
                Set\Composite::immutable(
                    static fn($keys, $values) => \array_combine(
                        \array_slice($keys, 0, \min(\count($keys), \count($values))),
                        \array_slice($values, 0, \min(\count($keys), \count($values))),
                    ),
                    Set\Sequence::of(Set\Either::any(
                        Set\Integers::any(),
                        Set\Strings::any(),
                    )),
                    Set\Sequence::of(Set\Type::any()),
                ),
            ),
            Set\Either::any(
                Set\Strings::any(),
                Set\Integers::any(),
                Set\RealNumbers::any(),
                Set\Elements::of(
                    true,
                    false,
                    null,
                    new stdClass,
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
                    new stdClass,
                ),
                Set\Sequence::of(Set\Either::any(
                    Set\Strings::any(),
                    Set\Integers::any(),
                    Set\RealNumbers::any(),
                    Set\Elements::of(
                        true,
                        false,
                        null,
                        new stdClass,
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

    yield proof(
        'Is::null()',
        given(
            Set\Either::any(
                Set\Strings::any(),
                Set\Integers::any(),
                Set\RealNumbers::any(),
                Set\Elements::of(
                    true,
                    false,
                    new stdClass,
                ),
                Set\Sequence::of(Set\Either::any(
                    Set\Strings::any(),
                    Set\Integers::any(),
                    Set\RealNumbers::any(),
                    Set\Elements::of(
                        true,
                        false,
                        null,
                        new stdClass,
                    ),
                )),
            ),
        ),
        static function($assert, $other) {
            $assert->true(
                Is::null()->asPredicate()(null),
            );
            $assert->null(
                Is::null()(null)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert->false(
                Is::null()->asPredicate()($other),
            );
            $assert->same(
                [['$', 'Value is not of type null']],
                Is::null()($other)->match(
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
        'Is::list()',
        given(
            Set\Sequence::of(Set\Either::any(
                Set\Strings::any(),
                Set\Integers::any(),
                Set\RealNumbers::any(),
                Set\Elements::of(
                    true,
                    false,
                    null,
                    new stdClass,
                ),
            )),
            Set\Composite::immutable(
                static fn($keys, $values) => \array_combine(
                    \array_slice($keys, 0, \min(\count($keys), \count($values))),
                    \array_slice($values, 0, \min(\count($keys), \count($values))),
                ),
                Set\Sequence::of(Set\Either::any(
                    Set\Integers::any(),
                    Set\Strings::any(),
                ))->atLeast(1),
                Set\Sequence::of(Set\Type::any())->atLeast(1),
            ),
        ),
        static function($assert, $array, $other) {
            $assert->true(
                Is::list()->asPredicate()($array),
            );
            $assert->same(
                $array,
                Is::list()($array)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert->false(
                Is::list()->asPredicate()($other),
            );
            $assert->same(
                [['$', 'Value is not of type list']],
                Is::list()($other)->match(
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
        'Is::list() with inner type',
        given(
            Set\Sequence::of(
                Set\Strings::any(),
            )->atLeast(1),
            Set\Sequence::of(
                Set\Integers::any(),
            )->atLeast(1),
        ),
        static function($assert, $strings, $ints) {
            $assert->true(
                Is::list(Is::string())->asPredicate()($strings),
            );
            $assert->same(
                $strings,
                Is::list(Is::string())($strings)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert->false(
                Is::list(Is::string())->asPredicate()($ints),
            );
            $assert->same(
                [['$', 'Value is not of type string']],
                Is::list(Is::string())($ints)->match(
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
        'Is::shape()',
        given(
            Set\Composite::immutable(
                static fn($letter, $rest) => $letter.$rest,
                Set\Either::any(
                    Set\Chars::lowercaseLetter(),
                    Set\Chars::uppercaseLetter(),
                ),
                Set\Strings::madeOf(Set\Chars::alphanumerical()),
            ),
            Set\Type::any(),
            Set\Integers::any(),
        ),
        static function($assert, $key, $value, $int) {
            $assert->null(
                Is::shape($key, Is::int())($value)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert->same(
                [$key => $int],
                Is::shape($key, Is::int())([$key => $int])->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
        },
    );

    yield proof(
        'Is::shape()->withKeyFailure()',
        given(
            Set\Composite::immutable(
                static fn($letter, $rest) => $letter.$rest,
                Set\Either::any(
                    Set\Chars::lowercaseLetter(),
                    Set\Chars::uppercaseLetter(),
                ),
                Set\Strings::madeOf(Set\Chars::alphanumerical()),
            ),
            Set\Strings::atLeast(1),
        ),
        static function($assert, $key, $expected) {
            $assert->same(
                [['$', $expected]],
                Is::shape($key, Is::int())->withKeyFailure(static function($in) use ($assert, $key, $expected) {
                    $assert->same($key, $in);

                    return $expected;
                })([])->match(
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
        'Is::associativeArray()',
        given(
            Set\Sequence::of(Set\Strings::any()->filter(
                static fn($string) => !\is_numeric($string), // to avoid implicit convertions to ints
            ))->map(static fn($keys) => \array_unique($keys)),
            Set\Sequence::of(Set\Integers::any()),
            Set\Integers::any(),
            Set\Strings::atLeast(1)->filter(
                static fn($string) => !\is_numeric($string), // to avoid implicit convertions to ints
            ),
            Set\Either::any(
                Set\Integers::any(),
                Set\Strings::any(),
                Set\Elements::of(true, false, null, new stdClass),
                Set\RealNumbers::any(),
            ),
        ),
        static function($assert, $keys, $values, $integer, $string, $random) {
            // `->map`s allow to verify the constraint use the values parsed
            // from the constraint instead of the initial values from the array
            $validation = Is::associativeArray(
                Is::string()->map(Str::of(...)),
                Is::int()->map(static fn($i) => $i + 1),
            );
            $validArray = [];
            $validPairs = [];

            foreach ($keys as $index => $key) {
                $validArray[$key] = $values[$index] ?? 0;
                $validPairs[] = [$key, $values[$index] ?? 0];
            }

            $assert->same(
                $validPairs,
                $validation($validArray)->match(
                    static fn($map) => $map
                        ->toSequence()
                        ->map(static fn($pair) => [
                            $pair->key()->toString(),
                            $pair->value() - 1,
                        ])
                        ->toList(),
                    static fn() => null,
                ),
            );

            $invalidArray = $validArray;
            $invalidArray[$integer] = $integer;

            $assert->same(
                [["key($integer)", 'Value is not of type string']],
                $validation($invalidArray)->match(
                    static fn() => null,
                    static fn($failures) => $failures
                        ->map(static fn($failure) => [
                            $failure->path()->toString(),
                            $failure->message(),
                        ])
                        ->toList(),
                ),
            );

            unset($invalidArray[$integer]);
            $invalidArray[$string] = $string;

            $assert->same(
                [[$string, 'Value is not of type int']],
                $validation($invalidArray)->match(
                    static fn() => null,
                    static fn($failures) => $failures
                        ->map(static fn($failure) => [
                            $failure->path()->toString(),
                            $failure->message(),
                        ])
                        ->toList(),
                ),
            );

            unset($invalidArray[$string]);
            $invalidArray[''] = $string;

            $assert->same(
                [["''", 'Value is not of type int']],
                $validation($invalidArray)->match(
                    static fn() => null,
                    static fn($failures) => $failures
                        ->map(static fn($failure) => [
                            $failure->path()->toString(),
                            $failure->message(),
                        ])
                        ->toList(),
                ),
            );

            $assert->same(
                [['$', 'Value is not of type array']],
                $validation($random)->match(
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
