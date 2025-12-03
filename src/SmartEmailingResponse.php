<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing;

/**
 * Class SmartEmailingResponse
 *
 * Reprezentuje standardizovanou odpověď SmartEmailing klienta.
 * Obsahuje informaci o úspěchu/selhání, data a případnou chybovou zprávu.
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Response
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */
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

    /**
     * Serializace pro debug/logy
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data'    => $this->data,
            'message' => $this->message,
        ];
    }
}
