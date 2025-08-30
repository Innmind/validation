<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\TimeContinuum\{
    Clock,
    Format,
    PointInTime as PointInTimeInterface,
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
     * @return Constraint<string, PointInTimeInterface>
     */
    #[\NoDiscard]
    public static function ofFormat(Clock $clock, Format $format): Constraint
    {
        return Constraint::pointInTime($clock)->format($format);
    }
}
