<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain;

use ReflectionClass;

/**
 * Class AbstractEntity
 *
 * @package Ox\Mediboard\Jfse\Domain
 */
abstract class AbstractEntity
{
    /**
     * AbstractEntity constructor.
     */
    final public function __construct()
    {
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getProperties() as $property) {
            if ($property->hasType() && $property->getType()->allowsNull()) {
                $this->{$property->getName()} = null;
            }
        }
    }

    /**
     * Hydrate the entity from the given array
     *
     * @param array $data
     *
     * @return static
     */
    public static function hydrate(array $data)
    {
        $entity = new static();
        foreach ($data as $property => $value) {
            if (property_exists($entity, $property) && $value !== '' && $value !== null) {
                $setter = 'set' . str_replace('_', '', ucwords($property));
                if (method_exists($entity, $setter)) {
                    $entity->$setter($value);
                } else {
                    $entity->$property = $value;
                }
            }
        }

        return $entity;
    }
}
