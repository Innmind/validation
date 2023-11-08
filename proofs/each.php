<?php
declare(strict_types = 1);

use Innmind\Validation\{
    Each,
    Is,
};
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
                Each::of(Is::int())->asPredicate()($ints),
            );
            $assert->true(
                Each::of(Is::int()->or(Is::string()))->asPredicate()($strings),
            );
            $assert->true(
                Each::of(Is::int()->or(Is::string()))->asPredicate()(\array_merge(
                    $ints,
                    $strings,
                )),
            );
            $assert->same(
                $ints,
                Each::of(Is::int())($ints)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );

            $assert->false(
                Each::of(Is::int())->asPredicate()($strings),
            );
            $assert->same(
                [['$', 'Value is not of type int']],
                Each::of(Is::int())($strings)->match(
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
