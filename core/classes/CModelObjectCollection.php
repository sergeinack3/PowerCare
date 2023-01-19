<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use ArrayAccess;
use Countable;
use Iterator;

class CModelObjectCollection implements ArrayAccess, Countable, Iterator
{
    use CItemsIteratorTrait;

    /** @var CModelObject[] */
    private $items = [];

    public function count(): int
    {
        return count($this->items);
    }

    public function add(CModelObject $object): void
    {
        $this->items[] = $object;
    }

    public function __construct(array $objects = [])
    {
        foreach ($objects as $object) {
            $this->add($object);
        }
    }
}
