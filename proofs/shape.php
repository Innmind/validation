<?php
declare(strict_types = 1);

use Innmind\Validation\{
    Shape,
    Is,
};

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
                    ['$', 'Unknown key foo'],
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
                    ['$', 'Unknown key foo'],
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
};
