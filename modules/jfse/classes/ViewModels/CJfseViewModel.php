<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels;

use DateTimeInterface;
use Exception;
use Ox\Core\CModelObject;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\JfseEnum;

/**
 * A skeleton class for the ViewModels
 *
 * @package Ox\Mediboard\Jfse\ViewModels
 */
abstract class CJfseViewModel extends CModelObject
{
    /**
     * CJfseViewModel constructor.
     * @throws Exception
     */
    final public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a new view model and sets its properties from the given entity
     *
     * @param AbstractEntity $entity
     *
     * @return static|null
     */
    public static function getFromEntity(AbstractEntity $entity): ?self
    {
        $view_model = new static();
        $props      = $view_model->getProps();
        foreach ($props as $name => $prop) {
            $getter = self::guessGetterName($name);
            if (method_exists($entity, $getter)) {
                $value = $entity->$getter();
                if ($value instanceof DateTimeInterface) {
                    $value = $value->format(strpos($prop, 'dateTime') !== false ? 'Y-m-d H:i:s' : 'Y-m-d');
                } elseif ($value instanceof JfseEnum) {
                    $value = (string)$value->getValue();
                } elseif (strpos($prop, 'bool') !== false) {
                    /* Important to not set null fields to 0 */
                    $value = $value === true ? '1' : ($value === false ? '0' : null);
                }

                $view_model->$name = $value;
            }
        }

        return $view_model;
    }

    /**
     * Returns the getter method name from the property name
     *
     * @param string $property
     *
     * @return string
     */
    public static function guessGetterName(string $property): string
    {
        return 'get' . str_replace('_', '', ucwords($property, '_'));
    }
}
