<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\DataModels;

use Exception;
use Ox\Core\CMbObject;
use ReflectionClass;

/**
 * An abstract class containing the default constructor for the Data models
 */
abstract class CJfseDataModel extends CMbObject
{
    /**
     * CJfseDataModel constructor.
     * Initialize the nullable properties to null, to avoid the Php error
     * property must not be accessed before initialization, and to avoid setting a default to null for the property
     */
    public function __construct()
    {
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getProperties() as $property) {
            if ($property->hasType() && $property->getType()->allowsNull() && !$property->isInitialized($this)) {
                $this->{$property->getName()} = null;
            }
        }

        try {
            parent::__construct();
        } catch (Exception $e) {
        }
    }
}
