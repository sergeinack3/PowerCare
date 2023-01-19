<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Tests\JsonApi;

use Ox\Tests\TestsException;

/**
 * Representation of a JsonApi object.
 * It can be loaded from JSON:API using createFromArray.
 * It can be converted to JSON:API using json_encode.
 */
class Item extends AbstractObject
{
    public const TYPE          = 'type';
    public const ID            = 'id';
    public const ATTRIBUTES    = 'attributes';
    public const RELATIONSHIPS = 'relationships';

    private string  $type;
    private ?string $id;
    private array   $attributes    = [];
    private array   $relationships = [];

    private bool $in_collection = false;

    public function __construct(string $type, ?string $id = null)
    {
        $this->type = $type;
        $this->id   = $id;
    }

    public static function createFromArray(array $data, bool $in_collection = false): self
    {
        if (!$in_collection && !isset($data[static::DATA])) {
            throw new TestsException('Data must be the first key of the Item');
        }

        $data_node = $in_collection ? $data : $data[static::DATA];

        if (!isset($data_node[static::TYPE])) {
            throw new TestsException('Type is mandatory to create an Item');
        }

        if (!isset($data_node[static::ID])) {
            throw new TestsException('Id is mandatory to create an Item');
        }

        return (new static($data_node[static::TYPE], $data_node[static::ID]))
            ->setAttributes($data_node[static::ATTRIBUTES] ?? [])
            ->setRelationships($data_node[static::RELATIONSHIPS] ?? [])
            ->setLinks($data_node[static::LINKS] ?? [])
            ->setMeta($data[static::META] ?? [])
            ->setIncluded($data[static::INCLUDED] ?? [])
            ->setInCollection($in_collection);
    }

    public function jsonSerialize(): array
    {
        $data_node = [
            static::TYPE => mb_convert_encoding($this->type, 'UTF-8', 'ISO-8859-1'),
            static::ID   => $this->id,
        ];

        if ($this->attributes) {
            $data_node[static::ATTRIBUTES] = mb_convert_encoding($this->attributes, 'UTF-8', 'ISO-8859-1');
        }

        if ($this->relationships) {
            $relations = [];
            foreach ($this->relationships as $name => $relationship) {
                if ($relationship instanceof Collection) {
                    $relation = [];
                    foreach ($relationship->getItems() as $item) {
                        $array_item = $item->jsonSerialize();
                        $relation[] = $array_item[Item::DATA] ?? $array_item;
                    }

                    $relations[$name][Collection::DATA] = $relation;
                } else {
                    $relations[$name] = $relationship;
                }
            }
            $data_node[static::RELATIONSHIPS] = $relations;
        }

        if ($this->links) {
            $data_node[static::LINKS] = $this->links;
        }

        if ($this->in_collection) {
            $data = $data_node;
        } else {
            $data = [static::DATA => $data_node];
        }

        if ($this->meta) {
            $data[static::META] = mb_convert_encoding($this->meta, 'UTF-8', 'ISO-8859-1');
        }

        return $data;
    }


    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function setRelationships(array $relationships): self
    {
        $relationships_items = [];

        foreach ($relationships as $name => $data) {
            if ($data instanceof Item || $data instanceof Collection) {
                $relationships_items[$name] = $data;
            } elseif (is_null($data)) {
                $relationships_items[$name] = $data;
            } elseif (is_array($data) && empty($data)) {
                $relationships_items[$name] = [];
            } elseif (isset($data[Collection::DATA][0])) {
                $relationships_items[$name] = Collection::createFromArray($data);
            } else {
                $relationships_items[$name] = Item::createFromArray($data);
            }
        }

        $this->relationships = $relationships_items;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * @return mixed|null
     */
    public function getAttribute(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function getRelationships(): array
    {
        return $this->relationships;
    }

    public function hasRelationship(string $name): bool
    {
        return array_key_exists($name, $this->relationships);
    }

    /**
     * @return Item|Collection|null|array
     */
    public function getRelationship(string $name)
    {
        return $this->relationships[$name] ?? null;
    }

    public function setInCollection(bool $in_collection): self
    {
        $this->in_collection = $in_collection;

        return $this;
    }
}
