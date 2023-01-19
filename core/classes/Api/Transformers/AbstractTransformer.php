<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Transformers;

use Ox\Core\Api\Resources\Item;

/**
 * Class AbstractTransformer
 * Transform resource item to data
 */
abstract class AbstractTransformer
{
    /** @var int */
    public const RECURSION_LIMIT = 1; // start at 0

    /** @var Item $item */
    protected $item;

    /** @var string */
    protected $type;

    /** @var mixed */
    protected $id;

    /** @var array */
    protected $attributes = [];

    /** @var array */
    protected $links = [];

    protected $meta = [];

    /** @var array */
    protected $relationships = [];

    /**
     * AbstractTransformer constructor.
     *
     * @param Item $item
     */
    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    /**
     * @return array
     */
    abstract public function createDatas(): array;

    /**
     * @return array
     */
    public function render(): array
    {
        $datas_transformed = [];

        // datas
        $datas_transformed['datas'] = array_merge(
            [
                '_type' => $this->type, // underscore preserve collision with attributes
                '_id'   => $this->id,
            ],
            $this->attributes
        );

        // links
        if (!empty($this->links)) {
            $datas_transformed['links'] = $this->links;
        }

        // relationships
        if (!empty($this->relationships)) {
            $datas_transformed['relationships'] = $this->relationships;
        }

        if (!empty($this->meta)) {
            $datas_transformed['meta'] = $this->meta;
        }

        return $datas_transformed;
    }


    /**
     * Necessary for json:api spec
     *
     * @param mixed $data
     *
     * @return string
     */
    protected function createIdFromData($data): string
    {
        return md5(serialize($data));
    }
}
