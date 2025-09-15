<?php
declare(strict_types = 1);

use Innmind\Validation\Failure;
use Innmind\Immutable\Set as ISet;
use Innmind\BlackBox\Set;

enum Tags
{
    case a;
    case b;
    case c;
    case d;
    case e;
}

return static function() {
    yield proof(
        'Failure::tag()',
        given(
            Set::strings()->atLeast(1),
            Set::sequence(Set::of(...Tags::cases())),
        ),
        static function($assert, $message, $tags) {
            $failure = Failure::of($message);

            foreach ($tags as $tag) {
                $failure = $failure->tag($tag);
            }

            $assert->true(
                $failure->tags()->equals(ISet::of(...$tags)),
            );
        },
    );
};
