<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Time\{
    Clock,
    Format,
    Point,
};

/**
 * @psalm-immutable
 */
final class PointInTime
{
    private function __construct()
    {
    }

    /**
     * @psalm-pure
     *
     * @return Constraint<string, Point>
     */
    #[\NoDiscard]
    public static function ofFormat(Clock $clock, Format $format): Constraint
    {
        return Constraint::pointInTime($clock)->format($format);
    }
}
