<?php
declare(strict_types = 1);

use Innmind\Validation\{
    Instance,
    Constraint,
};
use Innmind\BlackBox\Set;

return static function() {
    yield proof(
        'Instance::of()',
        given(Set\Type::any()),
        static function($assert, $other) {
            $std = new stdClass;
            $assert->true(
                Instance::of(stdClass::class)->asPredicate()($std),
            );
            $assert->same(
                $std,
                Instance::of(stdClass::class)($std)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert->false(
                Instance::of(stdClass::class)->asPredicate()($other),
            );
            $assert->same(
                [['$', 'Value is not an instance of stdClass']],
                Instance::of(stdClass::class)($other)->match(
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
        'Constraint::object()',
        given(Set\Either::any(
            Set\Integers::any(),
            Set\Strings::any(),
        )),
        static function($assert, $other) {
            $std = new stdClass;
            $assert->true(
                Constraint::object()->asPredicate()($std),
            );
            $assert->same(
                $std,
                Constraint::object()($std)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert->false(
                Constraint::object()->asPredicate()($other),
            );
            $assert->same(
                [['$', 'Value is not of type object']],
                Constraint::object()($other)->match(
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
