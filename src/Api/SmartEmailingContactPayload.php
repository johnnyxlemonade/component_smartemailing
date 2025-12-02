<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Api;

/**
 * SmartEmailingContactPayload
 *
 * Vytváří datovou strukturu požadovanou SmartEmailing API
 * při importu kontaktů (POST /import).
 *
 * Runtime chování zachováno 1:1.
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
