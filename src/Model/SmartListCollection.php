<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Model;

/**
 * Class SmartListCollection
 *
 * Kolekce objektů SmartList s podporou iterace.
 * Umožňuje vyhledávání seznamu dle ID a převod
 * interní kolekce na pole.
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Collection
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */
final class SmartListCollection implements \IteratorAggregate
{
    /** @var SmartList[] */
    private array $items = [];

    public function __construct(array $items)
    {
        foreach ($items as $item) {
            if ($item instanceof SmartList) {

                // ignorujeme objekty bez platného ID
                if ($item->getId() > 0) {
                    $this->items[] = $item;
                }
            }
        }
    }

    /** @return \Traversable<SmartList> */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Vrátí list podle ID nebo null
     */
    public function find(int|string $id): ?SmartList
    {
        $id = (int)$id;

        foreach ($this->items as $list) {
            if ($list->getId() === $id) {
                return $list;
            }
        }

        return null;
    }

    /**
     * Vrátí všechny SmartList objekty
     */
    public function toArray(): array
    {
        return $this->items;
    }
}
