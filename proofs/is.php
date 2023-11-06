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
            $assert->null(
                Is::string()($other)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
        },
    );
};
