<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Model;

/**
 * Class SmartContactCollection
 *
 * Kolekce objektů SmartContact s podporou iterace.
 * Umožňuje vyhledávání, získání prvního kontaktu
 * a převod na interní pole objektů.
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Collection
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */

final class SmartContactCollection implements \IteratorAggregate
{
    /** @var SmartContact[] */
    private array $items = [];

    public function __construct(array $rawContacts)
    {
        foreach ($rawContacts as $item) {
            if (is_array($item)) {
                $contact = SmartContact::fromArray($item);

                // ignorujeme prázdné objekty bez ID
                if ($contact->getId() !== '') {
                    $this->items[] = $contact;
                }
            }
        }
    }

    /** @return \Traversable<SmartContact> */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function findByEmail(string $email): ?SmartContact
    {
        foreach ($this->items as $c) {
            if (strcasecmp($c->getEmail(), $email) === 0) {
                return $c;
            }
        }

        return null;
    }

    public function first(): ?SmartContact
    {
        return $this->items[0] ?? null;
    }

    /** Vrátí interní pole objektů */
    public function toArray(): array
    {
        return $this->items;
    }
}
