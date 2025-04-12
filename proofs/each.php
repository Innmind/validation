<?php
declare(strict_types = 1);

use Innmind\Validation\Is;
use Innmind\BlackBox\Set;

return static function() {
    yield proof(
        'Each::of()',
        given(
            Set\Sequence::of(Set\Integers::any()),
            Set\Sequence::of(Set\Strings::any())->atLeast(1),
        ),
        static function($assert, $ints, $strings) {
            $assert->true(
                Is::list(Is::int())->asPredicate()($ints),
            );
            $assert->true(
                Is::list(Is::int()->or(Is::string()))->asPredicate()($strings),
            );
            $assert->true(
                Is::list(Is::int()->or(Is::string()))->asPredicate()(\array_merge(
                    $ints,
                    $strings,
                )),
            );
            $assert->same(
                $ints,
                Is::list(Is::int())($ints)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );

            $assert->false(
                Is::list(Is::int())->asPredicate()($strings),
            );
            $assert->same(
                [['$', 'Value is not of type int']],
                Is::list(Is::int())($strings)->match(
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
        'Each::of() returns the mapped content',
        given(
            Set\Sequence::of(Set\Integers::any()),
        ),
        static function($assert, $ints) {
            $doubles = \array_map(
                static fn($i) => $i * 2,
                $ints,
            );

            $assert->same(
                $doubles,
                Is::list(
                    Is::int()->map(static fn($i) => $i * 2),
                )($ints)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
        },
    );
};
