<?php
declare(strict_types = 1);

use Innmind\Validation\PointInTime;
use Innmind\TimeContinuum\Earth\{
    Clock,
    Format\ISO8601,
};
use Innmind\BlackBox\Set;
use Fixtures\Innmind\TimeContinuum\Earth\PointInTime as FPointInTime;

return static function() {
    yield proof(
        'PointInTime::ofFormat()',
        given(
            FPointInTime::any(),
            Set\Strings::any(),
        ),
        static function($assert, $point, $random) {
            $format = new ISO8601;
            $string = $point->format($format);
            $clock = new Clock;

            $assert->true(
                PointInTime::ofFormat($clock, $format)->asPredicate()($string),
                $string,
            );
            $assert->true(
                PointInTime::ofFormat($clock, $format)($string)->match(
                    static fn($value) => $value->equals($point),
                    static fn() => null,
                ),
            );
            $assert->false(
                PointInTime::ofFormat($clock, $format)->asPredicate()($random),
            );
            [[$path, $message]] = PointInTime::ofFormat($clock, $format)($random)->match(
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
                ->startsWith('Value is not a date of format ')
                ->endsWith($format->toString());
        },
    );
};
