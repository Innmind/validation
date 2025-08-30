<?php
declare(strict_types = 1);

use Innmind\Validation\Is;
use Innmind\BlackBox\Set;

return static function() {
    yield proof(
        'Constraint::flatMap()',
        given(
            Set::integers(),
            Set::integers(),
        ),
        static function($assert, $initial, $new) {
            $assert->same(
                [$initial, $initial, $new],
                Is::int()
                    ->flatMap(static function($value) use ($assert, $initial, $new) {
                        $assert->same($initial, $value);

                        return Is::int()->map(static fn($same) => [$same, $value, $new]);
                    })($initial)
                    ->match(
                        static fn($value) => $value,
                        static fn() => null,
                    ),
            );

            $assert->same(
                [['$', 'Value is not of type string']],
                Is::int()
                    ->flatMap(static fn() => Is::string())($initial)
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
