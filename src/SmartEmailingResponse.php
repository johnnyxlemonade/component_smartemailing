<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing;

final class SmartEmailingResponse
{
    public function __construct(
        private readonly bool    $success,
        private readonly mixed   $data = null,
        private readonly ?string $message = null
    ) {}

    public static function ok(mixed $data = null): self
    {
        return new self(true, $data, null);
    }

    public static function error(string $message, mixed $data = null): self
    {
        return new self(false, $data, $message);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function hasError(): bool
    {
        return !$this->success;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
