<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\Failure;
use Innmind\Immutable\Validation;

/**
 * @internal
 * @template T
 * @implements Implementation<list, list<T>>
 * @psalm-immutable
 */
final class Each implements Implementation
{
    /**
     * @param Implementation<mixed, T> $constraint
     */
    private function __construct(
        private Implementation $constraint,
    ) {
    }

    /**
     * @param list $value
     *
     * @return Validation<Failure, list<T>>
     */
    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        /** @var Validation<Failure, list<T>> */
        $validation = Validation::success([]);

        /** @var mixed $element */
        foreach ($value as $element) {
            $validation = $validation->flatMap(
                fn($carry) => ($this->constraint)($element)->map(
                    static fn($value) => \array_merge($carry, [$value]),
                ),
            );
        }

        return $validation;
    }

    /**
     * @internal
     * @template A
     * @psalm-pure
     *
     * @param Implementation<mixed, A> $constraint
     *
     * @return self<A>
     */
    public static function of(Implementation $constraint): self
    {
        return new self($constraint);
    }
}
