<?php
declare(strict_types = 1);

use Innmind\Validation\PointInTime;
use Innmind\TimeContinuum\{
    Earth,
    Clock,
    Format,
};
use Innmind\BlackBox\Set;
use Fixtures\Innmind\TimeContinuum\PointInTime as FPointInTime;

return static function() {
    yield proof(
        'PointInTime::ofFormat()',
        given(
            FPointInTime::any(),
            Set\Strings::any(),
        ),
        static function($assert, $point, $random) {
            $format = match (true) {
                \class_exists(Format\ISO8601::class) => new Format\ISO8601,
                default => Format::of('Y-m-d\TH:i:s.uP'), // to support microseconds
            };
            $string = $point->format($format);
            $clock = match (true) {
                \class_exists(Earth\Clock::class) => new Earth\Clock,
                default => Clock::live(),
            };

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
            Set\Strings::atLeast(1),
            Set\Strings::any(),
        ),
        static function($assert, $expected, $random) {
            $format = match (true) {
                \class_exists(Format\ISO8601::class) => new Format\ISO8601,
                default => Format::iso8601(),
            };
            $clock = match (true) {
                \class_exists(Earth\Clock::class) => new Earth\Clock,
                default => Clock::live(),
            };

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
};
