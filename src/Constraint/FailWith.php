<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\Failure;
use Innmind\Immutable\Validation;

/**
 * @internal
 * @template-covariant I
 * @template-covariant O
 * @implements Implementation<I, O>
 * @psalm-immutable
 */
final class FailWith implements Implementation
{
    /**
     * @param Implementation<I, O> $constraint
     * @param non-empty-string $message
     */
    private function __construct(
        private Implementation $constraint,
        private string $message,
    ) {
    }

    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        $message = $this->message;

        /** @psalm-suppress ImpureFunctionCall */
        return ($this->constraint)($value)->otherwise(
            static fn() => Validation::fail(Failure::of($message)),
        );
    }

    /**
     * @internal
     * @template A
     * @template B
     * @psalm-pure
     *
     * @param Implementation<A, B> $constraint
     * @param non-empty-string $message
     *
     * @return self<A, B>
     */
    public static function of(Implementation $constraint, string $message): self
    {
        return new self($constraint, $message);
    }
}
