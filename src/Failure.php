<?php
declare(strict_types = 1);

namespace Innmind\Validation;

/**
 * @psalm-immutable
 */
final class Failure
{
    /**
     * @param non-empty-string $message
     */
    private function __construct(
        private KeyPath $path,
        private string $message,
    ) {
    }

    /**
     * @psalm-pure
     *
     * @param non-empty-string $message
     */
    #[\NoDiscard]
    public static function of(string $message): self
    {
        return new self(KeyPath::root(), $message);
    }

    /**
     * @internal
     *
     * @param non-empty-string $path
     */
    #[\NoDiscard]
    public function under(string $path): self
    {
        return new self($this->path->under($path), $this->message);
    }

    #[\NoDiscard]
    public function path(): KeyPath
    {
        return $this->path;
    }

    /**
     * @return non-empty-string
     */
    #[\NoDiscard]
    public function message(): string
    {
        return $this->message;
    }
}
