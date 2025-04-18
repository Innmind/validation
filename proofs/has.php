<?php
declare(strict_types = 1);

use Innmind\Validation\Has;
use Innmind\BlackBox\Set;

return static function() {
    yield proof(
        'Has::key()',
        given(
            Set::compose(
                static fn($keys, $values) => \array_combine(
                    \array_slice($keys, 0, \min(\count($keys), \count($values))),
                    \array_slice($values, 0, \min(\count($keys), \count($values))),
                ),
                Set::sequence(Set::either(
                    Set::integers(),
                    Set::strings(),
                ))->atLeast(1),
                Set::sequence(Set::type())->atLeast(1),
            ),
            Set::strings()->atLeast(1),
            Set::type(),
        ),
        static function($assert, $array, $key, $value) {
            unset($array[$key]);
            $success = $array;
            $success[$key] = $value;

            $assert->true(
                Has::key($key)->asPredicate()($success),
            );
            $assert->same(
                $value,
                Has::key($key)($success)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );
            $assert->false(
                Has::key($key)->asPredicate()($array),
            );
            [[$path, $message]] = Has::key($key)($array)->match(
                static fn() => null,
                static fn($failures) => $failures
                    ->map(static fn($failure) => [
                        $failure->path()->toString(),
                        $failure->message(),
                    ])
                    ->toList(),
            );
            $assert->same('$', $path);
            $assert
                ->string($message)
                ->startsWith('The key ')
                ->contains($key)
                ->endsWith(' is missing');
        },
    );

    yield proof(
        'Has::key()->withFailure()',
        given(
            Set::compose(
                static fn($keys, $values) => \array_combine(
                    \array_slice($keys, 0, \min(\count($keys), \count($values))),
                    \array_slice($values, 0, \min(\count($keys), \count($values))),
                ),
                Set::sequence(Set::either(
                    Set::integers(),
                    Set::strings(),
                ))->atLeast(1),
                Set::sequence(Set::type())->atLeast(1),
            ),
            Set::strings()->atLeast(1),
            Set::strings()->atLeast(1),
        ),
        static function($assert, $array, $key, $expected) {
            unset($array[$key]);
            $validation = Has::key($key)->withFailure(
                static function($in) use ($assert, $key, $expected) {
                    $assert->same($key, $in);

                    return $expected;
                },
            );

            [[$path, $message]] = $validation($array)->match(
                static fn() => null,
                static fn($failures) => $failures
                    ->map(static fn($failure) => [
                        $failure->path()->toString(),
                        $failure->message(),
                    ])
                    ->toList(),
            );
            $assert->same($expected, $message);
        },
    );
};
