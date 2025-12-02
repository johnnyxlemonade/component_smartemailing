<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing;
use Lemonade\SmartEmailing\Api\SmartEmailingApi;
use Lemonade\SmartEmailing\Api\SmartEmailingSchema;
use Lemonade\SmartEmailing\Model\SmartList;
use Lemonade\SmartEmailing\Model\SmartListCollection;
use Lemonade\SmartEmailing\Model\SmartContact;
use Lemonade\SmartEmailing\Model\SmartContactCollection;
use Lemonade\SmartEmailing\Model\SmartListMeta;

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

    /**
     * addOrUpdate:
     * 1) vytvoří kontakt nebo ho přidá do listu
     * 2) pokud má $fields, provede následný update (PUT)
     */
    public function addOrUpdate(string $email, int|string $listId, array $fields = []): SmartEmailingResponse
    {
        try {
            // 1) vytvořit/přidat do listu
            $created = $this->api->createContact($listId, [$email]);

            // 2) aktualizace kontaktu
            if ($fields !== []) {
                $payload = $this->filterAllowedFields($fields);

                foreach ($created as $contactId => $_email) {
                    $result = $this->api->updateContactFields($contactId, $payload);

                    if (($result['status'] ?? '') !== 'ok') {
                        return SmartEmailingResponse::error(
                            $result['message'] ?? 'Nepodařilo se aktualizovat kontakt.'
                        );
                    }
                }
            }

            // vrátíme kolekci SmartContactCollection
            if ($created === []) {
                return SmartEmailingResponse::ok(null);
            }

            $firstId = array_key_first($created);

            if ($firstId === null) {
                return SmartEmailingResponse::ok(null);
            }

            $detail = $this->api->getContactDetail($firstId);
            $smart  = new SmartContactCollection($detail);

            return SmartEmailingResponse::ok($smart);

        } catch (\Throwable $e) {
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

            $result = $this->api->updateContactFields($contactId, $payload);

            if (($result['status'] ?? '') !== 'ok') {
                return SmartEmailingResponse::error(
                    $result['message'] ?? 'Nepodařilo se aktualizovat kontakt.'
                );
            }

            // vrátíme SmartContact
            $detail = $this->api->getContactDetail($contactId);
            $smart = new SmartContactCollection($detail);

            // jelikož jde o detail, kolekce obsahuje 1 kontakt
            return SmartEmailingResponse::ok($smart->first());

        } catch (\Throwable $e) {
            return SmartEmailingResponse::error($e->getMessage());
        }
    }

    /**
     * Povolené změny dat kontaktu.
     */
    private function filterAllowedFields(array $fields): array
    {
        $allowed = ['name', 'surname', 'emailaddress', 'language'];

        return array_intersect_key(
            $fields,
            array_flip($allowed)
        );
    }
}
