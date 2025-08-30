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
    public static function of(string $message): self
    {
        return new self(KeyPath::root(), $message);
    }

    /**
     * @internal
     *
     * @param non-empty-string $path
     */
    public function under(string $path): self
    {
        return new self($this->path->under($path), $this->message);
    }

    public function path(): KeyPath
    {
        return $this->path;
    }

    /**
     * @return non-empty-string
     */
    public function message(): string
    {
        return $this->message;
    }
}
