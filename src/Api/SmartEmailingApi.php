<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Api;

/**
 * SmartEmailingApi
 *
 * Hlavní wrapper kolem HTTP klienta + formatteru.
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

    public function updateContactFields(int|string $contactId, array $payload): array
    {
        return $this->http->sendRequest(
            SmartEmailingSchema::METHOD_PUT,
            SmartEmailingSchema::ACTION_CONTACTS . '/' . (string)$contactId,
            $payload
        );
    }

    /**
     * createContact – 1:1 zachování původního chování
     */
    public function createContact(string|int $listId, array $emails = []): array
    {
        $payload = [];

        if ($emails !== []) {
            $data = [];
            foreach ($emails as $email) {
                $data[] = SmartEmailingContactPayload::create(
                    (string)$email,
                    (string)$listId
                );
            }

            $payload = ['data' => $data];
        }

        $call = $this->http->sendRequest(
            SmartEmailingSchema::METHOD_POST,
            SmartEmailingSchema::ACTION_IMPORT,
            $payload
        );

        return $this->formatter->createContact($call['contacts_map'] ?? []);
    }

    /**
     * Pro SmartEmailingClient – poskytuje přístup k HTTP klientovi.
     */
    public function getHttpClient(): SmartEmailingHttpClient
    {
        return $this->http;
    }
}
