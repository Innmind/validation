<?php
declare(strict_types = 1);

use Innmind\Validation\{
    Of,
    Failure,
};
use Innmind\Immutable\Validation;
use Innmind\BlackBox\Set;

return static function() {
    yield proof(
        'Of::callable()',
        given(
            Set::type(),
            Set::type(),
            Set::strings(),
        ),
        static function($assert, $in, $out, $message) {
            $success = static fn($value) => match ($value) {
                $in => Validation::success($out),
                default => Validation::success(Failure::of($message)),
            };
            $fail = static fn() => Validation::fail(Failure::of($message));

            $assert->true(
                Of::callable($success)->asPredicate()($in),
            );
            $assert->same(
                $out,
                Of::callable($success)($in)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert->false(
                Of::callable($fail)->asPredicate()($in),
            );
            $assert->same(
                [['$', $message]],
                Of::callable($fail)($in)->match(
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
