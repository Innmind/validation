<?php
declare(strict_types = 1);

use Innmind\Validation\Instance;
use Innmind\BlackBox\Set;

return static function() {
    yield proof(
        'Instance::of()',
        given(Set::type()),
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
};
