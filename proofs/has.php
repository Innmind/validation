<?php
declare(strict_types = 1);

use Innmind\Validation\Has;
use Innmind\BlackBox\Set;

return static function() {
    yield proof(
        'Has::key()',
        given(
            Set\Composite::immutable(
                static fn($keys, $values) => \array_combine(
                    \array_slice($keys, 0, \min(\count($keys), \count($values))),
                    \array_slice($values, 0, \min(\count($keys), \count($values))),
                ),
                Set\Sequence::of(Set\Either::any(
                    Set\Integers::any(),
                    Set\Strings::any(),
                ))->atLeast(1),
                Set\Sequence::of(Set\Type::any())->atLeast(1),
            ),
            Set\Strings::atLeast(1),
            Set\Type::any(),
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
                ->startsWith('Unknown key ')
                ->endsWith($key);
        },
    );
};
