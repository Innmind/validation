<?php
declare(strict_types = 1);

use Innmind\Validation\PointInTime;
use Innmind\Time\{
    Clock,
    Format,
};
use Innmind\BlackBox\Set;
use Fixtures\Innmind\Time\Point as FPoint;

return static function() {
    yield proof(
        'PointInTime::ofFormat()',
        given(
            FPoint::any(),
            Set::strings(),
        ),
        static function($assert, $point, $random) {
            $format = Format::of('Y-m-d\TH:i:s.uP'); // to support microseconds
            $string = $point->format($format);
            $clock = Clock::live();

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

    yield proof(
        'PointInTime::ofFormat()->withFailure()',
        given(
            Set::strings()->atLeast(1),
            Set::strings(),
        ),
        static function($assert, $expected, $random) {
            $format = Format::iso8601();
            $clock = Clock::live();

            [[$path, $message]] = PointInTime::ofFormat($clock, $format)->withFailure($expected)($random)->match(
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

    yield test(
        'PointInTime::ofFormat() with empty string fails',
        static function($assert) {
            $format = Format::iso8601();
            $clock = Clock::live();

            [[$path, $message]] = PointInTime::ofFormat($clock, $format)('')->match(
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
