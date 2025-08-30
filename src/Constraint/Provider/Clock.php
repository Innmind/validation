<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint\Provider;

use Innmind\Validation\{
    Constraint,
    Constraint\Implementation,
};
use Innmind\TimeContinuum\{
    Clock as Concrete,
    Format,
    PointInTime,
};

/**
 * @psalm-immutable
 */
final class Clock
{
    /**
     * @param pure-Closure(Implementation): Constraint $build
     */
    private function __construct(
        private \Closure $build,
        private Concrete $clock,
    ) {
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param pure-Closure(Implementation): Constraint $build
     */
    #[\NoDiscard]
    public static function of(\Closure $build, Concrete $clock): self
    {
        return new self($build, $clock);
    }

    /**
     * @template E
     *
     * @return Constraint<string, PointInTime>
     */
    #[\NoDiscard]
    public function format(Format $format): Constraint
    {
        /** @var Constraint<string, PointInTime> */
        return ($this->build)(Constraint\PointInTime::ofFormat(
            $this->clock,
            $format,
        ));
    }
}
