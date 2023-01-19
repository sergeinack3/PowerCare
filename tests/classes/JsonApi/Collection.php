<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Tests\JsonApi;

use ArrayAccess;
use Countable;
use Iterator;
use Ox\Core\CItemsIteratorTrait;
use Ox\Tests\TestsException;

/**
 * Representation of a JsonApi collection.
 * It can be loaded from JSON:API using createFromArray.
 * It can be converted to JSON:API using json_encode.
 */
class Collection extends AbstractObject implements ArrayAccess, Countable, Iterator
{
    use CItemsIteratorTrait;

    /** @var Item[] */
    private array $items = [];

    public function __construct(array $items)
    {
        foreach ($items as $item) {
            if (!is_object($item) || !$item instanceof Item) {
                throw new TestsException('Only Ox\\Tests\\JsonApi\\Item objects can be add to the collection');
            }

            $item->setInCollection(true);
        }

        $this->items = $items;
    }

    public static function createFromArray(array $data): self
    {
        if (!isset($data[static::DATA])) {
            throw new TestsException('Data must be the first key of the collection');
        }

        $items = [];
        foreach ($data[static::DATA] as $datum) {
            $items[] = Item::createFromArray($datum, true);
        }

        return (new static($items))
            ->setLinks($data[static::LINKS] ?? [])
            ->setMeta($data[static::META] ?? [])
            ->setIncluded($data[static::INCLUDED] ?? []);
    }


    public function jsonSerialize(): array
    {
        $data = [
            static::DATA => $this->items,
        ];

        if ($this->links) {
            $data[static::LINKS] = $this->links;
        }

        if ($this->meta) {
            $data[static::META] = $this->meta;
        }

        return $data;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getFirstItem(): Item
    {
        return $this->items[0];
    }

    public function getLastItem(): Item
    {
        return end($this->items);
    }


    public function count(): int
    {
        return count($this->items);
    }
}
