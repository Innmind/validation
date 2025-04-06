<?php
declare(strict_types = 1);

use Innmind\Validation\{
    Constraint,
    Failure,
};
use Innmind\Immutable\Validation;
use Innmind\BlackBox\Set;

return static function() {
    yield proof(
        'Constraint::of()',
        given(
            Set\Type::any(),
            Set\Type::any(),
            Set\Strings::any(),
        ),
        static function($assert, $in, $out, $message) {
            $success = static fn($value) => match ($value) {
                $in => Validation::success($out),
                default => Validation::success(Failure::of($message)),
            };
            $fail = static fn() => Validation::fail(Failure::of($message));

            $assert->true(
                Constraint::of($success)->asPredicate()($in),
            );
            $assert->same(
                $out,
                Constraint::of($success)($in)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert->false(
                Constraint::of($fail)->asPredicate()($in),
            );
            $assert->same(
                [['$', $message]],
                Constraint::of($fail)($in)->match(
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
