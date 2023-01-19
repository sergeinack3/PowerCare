<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request\Content;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\CMbObject;
use Ox\Core\CModelObject;
use Ox\Core\CStoredObject;
use Ox\Core\Kernel\Exception\HttpException;

/**
 * Representation of an item build from json_api data.
 */
class JsonApiItem
{
    private const TYPE          = 'type';
    private const ID            = 'id';
    private const ATTRIBUTES    = 'attributes';
    private const RELATIONSHIPS = 'relationships';
    private const META          = 'meta';

    private const TOKEN_KEYWORD = 'token';

    // Should remove the id field from mandatory according to JSON:API specification
    private const MANDATORY_KEYS = [
        self::TYPE,
        self::ID,
    ];

    /** @var string */
    private $type;

    /** @var string */
    private $id;

    /** @var array */
    private $attributes = [];

    /** @var array */
    private $relationships = [];

    /** @var array */
    private $meta = [];

    /** @var CModelObject */
    private $model_object;

    /**
     * Create the item from an array (decoded from json api).
     *
     * @throws RequestContentException
     */
    public function __construct(array $data)
    {
        foreach (self::MANDATORY_KEYS as $key) {
            if (!array_key_exists($key, $data)) {
                throw RequestContentException::itemKeyIsMandatory($key);
            }
        }

        $this->type          = $data[self::TYPE];
        $this->id            = $data[self::ID];
        $this->attributes    = $data[self::ATTRIBUTES] ?? [];
        $this->relationships = $data[self::RELATIONSHIPS] ?? [];
        $this->meta          = $data[self::META] ?? [];
    }

    /**
     * Create a CModelObject of class $object_class.
     * If the request is in post the ID must no be provided.
     *
     * @throws RequestContentException
     */
    public function createModelObject(string $object_class, bool $create_new): self
    {
        if (!is_subclass_of($object_class, CModelObject::class)) {
            throw RequestContentException::requestedClassIsNotModelObject($object_class);
        }

        if ($this->type !== $object_class::RESOURCE_TYPE) {
            throw RequestContentException::requestedClassTypeIsNotTheSameAsResourceType(
                $this->type,
                $object_class::RESOURCE_TYPE
            );
        }

        // For POST, PUT and PATCH : create a new object.
        // If an UUID is provided in post we should handle it
        if ($create_new) {
            /** @var CStoredObject $object */
            $this->model_object = new $object_class();
        } else {
            /** @var CStoredObject $object */
            $this->model_object = ($object_class)::findOrFail($this->id);
        }

        return $this;
    }

    /**
     * Hydrate the CModelObject using the fieldsets and fields.
     *
     * @throws ApiException
     */
    public function hydrateObject(array $fieldsets = [], array $fields = []): self
    {
        // Remove the primary key from attributes to avoid binding it after loading the object
        $primary_key = $this->model_object->getSpec()->key;
        if (isset($this->attributes[$primary_key])) {
            unset($this->attributes[$primary_key]);
        }

        if ($fieldsets) {
            $fields = array_merge($fields, $this->model_object->getFieldsByFieldsets($fieldsets));
        }

        // Remove non allowed fields
        foreach ($this->attributes as $field_name => $value) {
            if (!in_array($field_name, $fields)) {
                unset($this->attributes[$field_name]);
            }
        }

        CMbObject::setProperties($this->attributes, $this->model_object);

        if ($this->relationships) {
            $this->hydrateRelationships();
        }

        return $this;
    }

    public function getModelObject(): ?CModelObject
    {
        return $this->model_object;
    }

    public function setModelObject(CModelObject $object): self
    {
        if ($this->type !== $object::RESOURCE_TYPE) {
            throw RequestContentException::requestedClassTypeIsNotTheSameAsResourceType(
                $this->type,
                $object::RESOURCE_TYPE
            );
        }

        $this->model_object = $object;

        return $this;
    }

    /**
     * Hydrate the relationships using $this->relationships.
     * Each relationship is transformed into a JsonApiItem or an array of JsonApiItem.
     * Each relationship is build using the CModelObject::setResource{relation_name} of $this->model_object.
     *
     * @throws RequestContentException|ApiException
     */
    private function hydrateRelationships(): void
    {
        foreach ($this->relationships as $relation_name => $relation) {
            $method_name = sprintf("setResource%s", ucfirst($relation_name));

            if (!method_exists($this->model_object, $method_name)) {
                throw new HttpException(
                    403,
                    "You cannot POST the relation '{$relation_name}' for '{$this->model_object->_class}'"

                );
            }

            // If the $relation['data'] is null the relation must be emptied
            // If the $relation['data'] array have a 'type' key it's a single item.
            if ($relation['data'] === null) {
                $this->model_object->{$method_name}(null);
            } elseif (array_key_exists('type', $relation['data'])) {
                $this->model_object->{$method_name}(new JsonApiItem($relation['data']));
            } else {
                $relations = [];
                foreach ($relation['data'] as $item) {
                    $relations[] = new JsonApiItem($item);
                }

                $this->model_object->{$method_name}($relations);
            }
        }
    }
}
