<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing;
use Lemonade\SmartEmailing\Api\SmartEmailingApi;
use Lemonade\SmartEmailing\Api\SmartEmailingSchema;
use Lemonade\SmartEmailing\Model\SmartList;
use Lemonade\SmartEmailing\Model\SmartListCollection;
use Lemonade\SmartEmailing\Model\SmartContact;
use Lemonade\SmartEmailing\Model\SmartContactCollection;
use Lemonade\SmartEmailing\Model\SmartListMeta;

/**
 * Class SmartEmailingClient
 *
 * Vysokourovňový klient pro práci se SmartEmailing API.
 * Zajišťuje ověřování přístupu, práci se seznamy, kontakty,
 * přidávání a aktualizaci dat kontaktů a diagnostické funkce.
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Client
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */
final class SmartEmailingClient
{
    public function __construct(
        private readonly SmartEmailingApi $api
    ) {}

    /**
     * Ověří přihlašovací údaje.
     */
    public function checkLogin(): SmartEmailingResponse
    {
        try {
            return $this->api->checkLogin()
                ? SmartEmailingResponse::ok(true)
                : SmartEmailingResponse::error('Přihlášení selhalo.');
        } catch (\Throwable $e) {
            return SmartEmailingResponse::error($e->getMessage());
        }
    }

    /**
     * Vrací podle API ping.
     */
    public function getPing(): SmartEmailingResponse
    {
        try {
            $ok = $this->api->getPing();

            return $ok
                ? SmartEmailingResponse::ok(true)
                : SmartEmailingResponse::error('Ping test selhal.');

        } catch (\Throwable $e) {
            return SmartEmailingResponse::error($e->getMessage());
        }
    }

    /**
     * Vrací účetní ID uživatele (užitečné do diagnostiky).
     */
    public function getAccountId(): SmartEmailingResponse
    {
        try {
            $id = $this->api->getAccountId();

            return SmartEmailingResponse::ok($id);

        } catch (\Throwable $e) {
            return SmartEmailingResponse::error($e->getMessage());
        }
    }

    /**
     * Ověří, že daný list existuje.
     */
    public function checkListId(int|string $listId): SmartEmailingResponse
    {
        try {
            $exists = $this->api->checkListId($listId);

            if ($exists) {
                return SmartEmailingResponse::ok(true);
            }

            return SmartEmailingResponse::error(sprintf(
                'Seznam s ID "%s" neexistuje.',
                (string)$listId
            ));

        } catch (\Throwable $e) {
            return SmartEmailingResponse::error($e->getMessage());
        }
    }

    /**
     * Vrací seznamy jako kolekci objektů.
     * Formatter → Model → Collection
     */
    public function getLists(): SmartListCollection
    {
        $raw = $this->api->getList();

        return new SmartListCollection(
            array_map(
                fn(array $item) => SmartList::fromApiResponse($item),
                $raw
            )
        );
    }

    /**
     * Vrátí kontakty jako objektovou kolekci.
     */
    public function getContacts(): SmartContactCollection
    {
        return new SmartContactCollection(
            $this->api->getContacts()
        );
    }

    /**
     * Vrátí kontakty v konkrétním seznamu.
     */
    public function getContactsByList(int|string $listId): SmartContactCollection
    {
        try {
            return new SmartContactCollection(
                $this->api->getListContact($listId)
            );
        } catch (\Throwable) {
            return new SmartContactCollection([]);
        }
    }

    public function importContact(string $email, int|string $listId, array $fields = []): SmartEmailingResponse
    {
        try {
            // 1) import (create/update + assign to list)
            $result = $this->api->importContact($email, $listId, $fields);

            if (!isset($result['contacts_map'][0]['contact_id'])) {
                return SmartEmailingResponse::error('SmartEmailing nevrátil contact_id.');
            }

            $contactId = (int) $result['contacts_map'][0]['contact_id'];

            // 2) load full detail
            $detail = $this->api->getContactDetail($contactId);
            $smart  = new SmartContactCollection($detail);

            return SmartEmailingResponse::ok($smart->first());
        }
        catch (\Throwable $e) {
            return SmartEmailingResponse::error($e->getMessage());
        }

    }

    /**
     * Kompletně smaže kontakt (SmartEmailing neumožňuje "odebrat z listu" bez smazání).
     */
    public function removeFromList(int|string $contactId): SmartEmailingResponse
    {
        try {
            $ok = $this->api->deleteContact($contactId);

            return $ok
                ? SmartEmailingResponse::ok(true)
                : SmartEmailingResponse::error('Kontakt se nepodařilo odstranit.');

        } catch (\Throwable $e) {
            return SmartEmailingResponse::error($e->getMessage());
        }
    }

    /**
     * Aktualizuje kontakt (jméno, jazyk, email atd.).
     */
    public function updateContact(int|string $contactId, array $fields): SmartEmailingResponse
    {
        try {
            $payload = $this->filterAllowedFields($fields);

            // API import v3 – vrací status=created a contacts_map
            $result = $this->api->updateContact((int)$contactId, $payload);

            if (!isset($result['contacts_map'][0]['contact_id'])) {
                return SmartEmailingResponse::error(
                    $result['message'] ?? 'SmartEmailing v3 nevrátil contact_id při aktualizaci.'
                );
            }

            // načteme detail (objektově)
            $detail = $this->api->getContactDetail($contactId);
            $smart  = new SmartContactCollection($detail);

            return SmartEmailingResponse::ok($smart->first());
        }
        catch (\Throwable $e) {
            return SmartEmailingResponse::error($e->getMessage());
        }
    }

    /**
     * Přidá kontaktu tagy (create/update kontaktu přes v3 /contacts).
     * SmartEmailing vykoná "upsert" – pokud kontakt existuje, aktualizuje se,
     * pokud neexistuje, vytvoří se.
     */
    public function addTagsToContact(string $email, array $tags): SmartEmailingResponse
    {
        try {
            if ($email === '') {
                return SmartEmailingResponse::error('Email nesmí být prázdný.');
            }

            if ($tags === []) {
                return SmartEmailingResponse::error('Seznam tagů je prázdný.');
            }

            $result = $this->api->upsertTags($email, $tags);

            // API v3 vrací: ["data" => [ ["id" => X, ...] ]]
            $contactId = $result['data'][0]['id'] ?? null;

            if ($contactId === null) {
                return SmartEmailingResponse::error('SmartEmailing nevrátil contact_id.');
            }

            return SmartEmailingResponse::ok([
                'contactId' => $contactId,
                'tags'      => $tags
            ]);

        } catch (\Throwable $e) {
            return SmartEmailingResponse::error($e->getMessage());
        }
    }

    /**
     * Debug info
     */
    public function debug(): array
    {
        $auth = $this->api->getAuth();

        return [
            'clientClass' => self::class,
            'apiClass'    => $this->api::class,
            'auth' => [
                'hasUser'  => $auth->getUser() !== '',
                'hasToken' => $auth->getToken() !== '',
            ],
            'ping' => $this->getPing()->toArray(),
            'accountId' => $this->getAccountId()->toArray(),
        ];
    }

    /**
     * Povolené změny dat kontaktu.
     */
    private function filterAllowedFields(array $fields): array
    {
        $allowed = ['name', 'surname', 'language'];

        $result = [];

        foreach ($fields as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

}
