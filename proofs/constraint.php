<?php
declare(strict_types = 1);

use Innmind\Validation\{
    Constraint,
    Is,
    Failure,
};
use Innmind\Immutable\{
    Validation,
    Str,
    Monoid\Concat,
};
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

    yield proof(
        'Constraint::mapFailures()',
        given(
            Set::type(),
            Set::strings(),
            Set::strings(),
        ),
        static function($assert, $value, $message, $path) {
            $success = Validation::success(...);
            $fail = static fn() => Validation::fail(Failure::of($message));

            $assert->same(
                $value,
                Constraint::of($success)
                    ->mapFailures(static fn($failure) => $failure->under($path))($value)
                    ->match(
                        static fn($value) => $value,
                        static fn() => null,
                    ),
            );
            $assert->same(
                [[$path, $message]],
                Constraint::of($fail)
                    ->mapFailures(static fn($failure) => $failure->under($path))($value)
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

    yield proof(
        'Constraint::try()',
        given(
            Set::type(),
            Set::type(),
            Set::of(DomainException::class, LogicException::class),
            Set::strings(),
        ),
        static function($assert, $in, $value, $exception, $message) {
            $assert->same(
                [$in, $value],
                Constraint::try(static fn($in) => [$in, $value])($in)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert
                ->string(
                    Constraint::try(static fn() => throw new $exception($message))($in)->match(
                        static fn() => null,
                        static fn($failures) => $failures
                            ->map(static fn($failure) => $failure->message())
                            ->map(Str::of(...))
                            ->fold(Concat::monoid)
                            ->toString(),
                    ),
                )
                ->contains($exception)
                ->contains($message);
        },
    );
};
