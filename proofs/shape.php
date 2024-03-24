<?php
declare(strict_types = 1);

use Innmind\Validation\{
    Shape,
    Is,
};
use Innmind\BlackBox\Set;

return static function() {
    yield test(
        'Shape::of()',
        static function($assert) {
            $assert->true(
                Shape::of('foo', Is::int())
                    ->with('bar', Is::bool())
                    ->asPredicate()([
                        'foo' => 42,
                        'bar' => true,
                    ]),
            );
            $assert->same(
                [
                    'foo' => 42,
                    'bar' => true,
                ],
                Shape::of('foo', Is::int())
                    ->with('bar', Is::bool())([
                        'foo' => 42,
                        'bar' => true,
                        'baz' => 'invalid',
                    ])
                    ->match(
                        static fn($value) => $value,
                        static fn() => null,
                    ),
            );

            $assert->false(
                Shape::of('foo', Is::int())->asPredicate()([]),
            );
            $assert->false(
                Shape::of('foo', Is::int())->asPredicate()(['foo' => true]),
            );
            $assert->same(
                [
                    ['$', 'The key foo is missing'],
                    ['bar', 'Value is not of type bool'],
                ],
                Shape::of('foo', Is::int())
                    ->with('bar', Is::bool())([
                        'bar' => 'string',
                    ])
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

    yield test(
        'Shape nesting',
        static function($assert) {
            $assert->same(
                [
                    'foo' => 42,
                    'bar' => ['baz' => true],
                ],
                Shape::of('foo', Is::int())
                    ->with('bar', Shape::of('baz', Is::bool()))([
                        'foo' => 42,
                        'bar' => ['baz' => true],
                    ])
                    ->match(
                        static fn($value) => $value,
                        static fn() => null,
                    ),
            );

            $assert->same(
                [
                    ['$', 'The key foo is missing'],
                    ['bar.baz', 'Value is not of type bool'],
                ],
                Shape::of('foo', Is::int())
                    ->with('bar', Shape::of('baz', Is::bool()))([
                        'bar' => ['baz' => 'string'],
                    ])
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

    yield test(
        'Shape with optional key',
        static function($assert) {
            $assert->true(
                Shape::of('foo', Is::int())
                    ->with('bar', Is::bool())
                    ->optional('bar')
                    ->asPredicate()([
                        'foo' => 42,
                    ]),
            );
            $assert->same(
                [
                    'foo' => 42,
                ],
                Shape::of('foo', Is::int())
                    ->with('bar', Is::bool())
                    ->optional('bar')([
                        'foo' => 42,
                        'baz' => 'invalid',
                    ])
                    ->match(
                        static fn($value) => $value,
                        static fn() => null,
                    ),
            );
        },
    );

    yield test(
        'Shape with optional key with constraint directly specified',
        static function($assert) {
            $assert->true(
                Shape::of('foo', Is::int())
                    ->optional('bar', Is::bool())
                    ->asPredicate()([
                        'foo' => 42,
                    ]),
            );
            $assert->same(
                [
                    'foo' => 42,
                ],
                Shape::of('foo', Is::int())
                    ->optional('bar', Is::bool())([
                        'foo' => 42,
                        'baz' => 'invalid',
                    ])
                    ->match(
                        static fn($value) => $value,
                        static fn() => null,
                    ),
            );
        },
    );

    yield test(
        'Shape with optional key constraint is applied',
        static function($assert) {
            $assert->null(
                Shape::of('bar', Is::int())
                    ->optional('bar')([
                        'bar' => 'invalid',
                    ])
                    ->match(
                        static fn($value) => $value,
                        static fn() => null,
                    ),
            );
        },
    );

    yield proof(
        'Shape validates non arrays',
        given(
            Set\Either::any(
                Set\Strings::any(),
                Set\Integers::any(),
                Set\RealNumbers::any(),
                Set\Elements::of(
                    true,
                    false,
                    new stdClass,
                ),
            ),
        ),
        static function($assert, $value) {
            $assert->same(
                [['$', 'Value is not of type array']],
                Shape::of('bar', Is::int())($value)
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
