<?php
declare(strict_types = 1);

use Innmind\Validation\Is;
use Innmind\BlackBox\Set;

return static function() {
    yield proof(
        'Map::of()',
        given(
            Set\Integers::any(),
            Set\Integers::any(),
            Set\Strings::any(),
        ),
        static function($assert, $initial, $new, $string) {
            $assert->same(
                [$initial, $new],
                Is::int()
                    ->map(static fn($value) => [$value, $new])($initial)
                    ->match(
                        static fn($value) => $value,
                        static fn() => null,
                    ),
            );

            $assert->same(
                [['$', 'Value is not of type int']],
                Is::int()
                    ->map(static fn($value) => [$value, $new])($string)
                    ->match(
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
