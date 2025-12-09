<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Api;

/**
 * Class SmartEmailingApi
 *
 * Hlavní API wrapper pro SmartEmailing.
 * Odpovídá za komunikaci přes HTTP klienta, odesílání requestů
 * a předzpracování dat pomocí formatteru. Poskytuje metody
 * pro práci s kontakty, seznamy, diagnostikou a správou účtu.
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Api
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */
final class SmartEmailingApi
{
    private readonly SmartEmailingHttpClient $http;
    private readonly SmartEmailingFormatter  $formatter;

    public function __construct(string $user, string $token)
    {
        $this->http      = new SmartEmailingHttpClient(
            new SmartEmailingAuth($user, $token)
        );
        $this->formatter = new SmartEmailingFormatter();
    }

    public function getAuth(): SmartEmailingAuth
    {
        return $this->http->getAuth();
    }

    public function getSchema(?string $schema = null): array
    {
        return SmartEmailingSchema::getSchema($schema ?? '');
    }

    public function getPing(): bool
    {
        $call = $this->http->sendRequest(
            SmartEmailingSchema::METHOD_GET,
            SmartEmailingSchema::ACTION_PING
        );

        return $this->formatter->getPing($call);
    }

    public function getAccountId(): string
    {
        $call = $this->http->sendRequest(
            SmartEmailingSchema::METHOD_GET,
            SmartEmailingSchema::ACTION_CHECK_CREDENTIALS
        );

        return $this->formatter->getAccountId($call);
    }

    public function checkLogin(): bool
    {
        $call = $this->http->sendRequest(
            SmartEmailingSchema::METHOD_GET,
            SmartEmailingSchema::ACTION_CHECK_CREDENTIALS
        );

        return $this->formatter->checkLogin($call);
    }

    public function getList(): array
    {
        $call = $this->http->sendRequest(
            SmartEmailingSchema::METHOD_GET,
            SmartEmailingSchema::ACTION_CONTACTLISTS
        );

        return $this->formatter->getList($call['data'] ?? []);
    }

    public function checkListId(string|int $listId): bool
    {
        $call = $this->http->sendRequest(
            SmartEmailingSchema::METHOD_GET,
            SmartEmailingSchema::ACTION_CONTACTLISTS
        );

        return $this->formatter->checkListId($listId, $call['data'] ?? []);
    }

    public function getListContact(string|int $listId): array
    {
        $call = $this->http->sendRequest(
            SmartEmailingSchema::METHOD_GET,
            SmartEmailingSchema::ACTION_CONTACTLISTS . '/' . (string)$listId . '/contacts'
        );

        return $this->formatter->getContact($call['data'] ?? []);
    }

    public function getContacts(): array
    {
        $call = $this->http->sendRequest(
            SmartEmailingSchema::METHOD_GET,
            SmartEmailingSchema::ACTION_CONTACTS
        );

        return $this->formatter->getContact($call['data'] ?? []);
    }

    public function getContactDetail(string|int $contactId): array
    {
        $call = $this->http->sendRequest(
            SmartEmailingSchema::METHOD_GET,
            SmartEmailingSchema::ACTION_CONTACTS . '/' . (string)$contactId
        );

        // API vrací jeden záznam, formatter očekává pole
        return $this->formatter->getContact([$call['data'] ?? []]);
    }

    public function deleteContact(string|int $contactId): bool
    {
        $call = $this->http->sendRequest(
            SmartEmailingSchema::METHOD_DELETE,
            SmartEmailingSchema::ACTION_CONTACT_FORGET . '/' . (string)$contactId
        );

        return $this->formatter->deleteContact($call);
    }

    public function addToList(int|string $contactId, int|string $listId): array
    {
        return $this->http->sendRequest(
            SmartEmailingSchema::METHOD_POST,
            SmartEmailingSchema::ACTION_CONTACTLISTS,
            [
                'contact_id'     => (int) $contactId,
                'contactlist_id' => (int) $listId,
                'status'         => 'confirmed',
            ]
        );
    }

    /**
     * Import kontakt(ů)
     */
    public function importContact(string $email, string|int $listId, array $fields = []): array
    {
        $payload = [
            'data' => [
                [
                    'emailaddress' => $email,
                    'name'         => $fields['name']    ?? null,
                    'surname'      => $fields['surname'] ?? null,
                    'language'     => $fields['language'] ?? 'cs_CZ',
                    'contactlists' => [
                        [
                            'id'     => (int)$listId,
                            'status' => 'confirmed'
                        ]
                    ]
                ]
            ]
        ];

        return $this->http->sendRequest(
            SmartEmailingSchema::METHOD_POST,
            SmartEmailingSchema::ACTION_IMPORT,
            $payload
        );
    }

    /**
     * Aktualizace kontaktu
     */
    public function updateContact(int $contactId, array $fields): array
    {
        // načteme detail → asociativní pole: [id => data]
        $detail = $this->getContactDetail($contactId);

        // reset() vrátí první hodnotu pole
        $contact = reset($detail);

        if (!is_array($contact)) {
            throw new \RuntimeException("Kontakt {$contactId} se nepodařilo načíst.");
        }

        $email = $contact['contactEmail'] ?? null;

        if (!$email) {
            throw new \RuntimeException('Kontakt nemá email – nelze aktualizovat.');
        }

        // Vytvořit payload podle import API
        $payload = [
            'data' => [
                [
                    'emailaddress' => $email,
                    'name'         => $fields['name']    ?? null,
                    'surname'      => $fields['surname'] ?? null,
                    'language'     => $fields['language'] ?? null,
                ]
            ]
        ];

        return $this->http->sendRequest(
            SmartEmailingSchema::METHOD_POST,
            SmartEmailingSchema::ACTION_IMPORT,
            $payload
        );
    }

    /**
     * Upsert kontaktu – přidání tagů.
     * SmartEmailing vytvoří nový kontakt nebo aktualizuje existující.
     *
     * @param array<int,string> $tags
     * @return array{
     *     data?: array<int, array<string, mixed>>,
     *     message?: string
     * }
     */
    public function upsertTags(string $email, array $tags): array
    {
        if ($email === '') {
            throw new \InvalidArgumentException('Email nesmí být prázdný.');
        }

        if ($tags === []) {
            throw new \InvalidArgumentException('Tagy nesmí být prázdné.');
        }

        // SmartEmailing tag payload: [ ["name" => "tag1"], ... ]
        $tagPayload = array_map(
            fn($t) => ['name' => (string)$t],
            array_values(array_unique($tags))
        );

        $payload = [
            'emailaddress' => $email,
            'tags'         => $tagPayload
        ];

        return $this->http->sendRequest(
            SmartEmailingSchema::METHOD_POST,
            SmartEmailingSchema::ACTION_CONTACTS,
            $payload
        );
    }

    /**
     * Pro SmartEmailingClient – poskytuje přístup k HTTP klientovi.
     */
    public function getHttpClient(): SmartEmailingHttpClient
    {
        return $this->http;
    }
}
