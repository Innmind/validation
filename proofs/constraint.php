<?php
declare(strict_types = 1);

use Innmind\Validation\{
    Constraint,
    Is,
    Failure,
};
use Innmind\Immutable\Validation;
use Innmind\BlackBox\Set;

return static function() {
    yield proof(
        'Constraint::guard()',
        given(
            Set::type(),
            Set::type(),
            Set::strings(),
        ),
        static function($assert, $in, $out, $message) {
            $success = Constraint::of(static fn($value) => match ($value) {
                $in => Validation::success($out),
                default => Validation::success(Failure::of($message)),
            });
            $fail = Constraint::of(static fn() => Validation::fail(Failure::of($message)));

            $assert->same(
                $out,
                $success
                    ->guard(Is::value($out))($in)
                    ->match(
                        static fn($value) => $value,
                        static fn() => null,
                    ),
            );
            $assert->same(
                [['$', $message]],
                $success
                    ->guard($fail)
                    ->xor($success)($in)
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
            $assert->same(
                $out,
                $success
                    ->guard($fail)
                    ->or($success)($in)
                    ->match(
                        static fn($value) => $value,
                        static fn() => null,
                    ),
            );
        },
    );
};
