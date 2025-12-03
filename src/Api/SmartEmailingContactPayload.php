<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Api;

/**
 * Class SmartEmailingContactPayload
 *
 * Konstruktor payloadu pro import kontaktů do SmartEmailing API.
 * Zajišťuje generování validní struktury pro endpoint /import.
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Payload
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */
final class SmartEmailingContactPayload
{
    public function __construct(
        private readonly string $email,
        private readonly string $listId
    ) {}

    /**
     * Vrací strukturu přesně v podobě, jak ji SmartEmailing očekává.
     */
    private function toArray(): array
    {
        return [
            'emailaddress' => $this->email,
            'language'     => 'cs_CZ',
            'contactlists' => [
                [
                    'id'     => $this->listId,
                    'status' => 'confirmed',
                ]
            ]
        ];
    }

    /**
     * Jednotný factory helper používaný při vytváření import payloadu.
     */
    public static function create(string $email, string $listId): array
    {
        return (new self($email, $listId))->toArray();
    }
}
