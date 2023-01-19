<?php

/**
 * @package Mediboard\Fhir\Resources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\ClassMap;

abstract class ObjectMap
{
    /** @var string */
    protected const KEY_MAP = '';

    /** @var array */
    protected $map;

    /** @var string */
    protected $version;

    /** @var bool */
    protected $return_class = false;

    /** @var array */
    protected $class_map;

    /**
     * @param array  $map
     * @param string $version
     */
    public function __construct(FHIRClassMap $class_map, string $version)
    {
        $this->class_map = $class_map->getOrBuildClassMap();
        $this->map       = $this->class_map[$this::KEY_MAP];
        $this->version   = $version;
    }

    /**
     * @param string $version
     *
     * @return void
     */
    public function selectVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @param bool $return_class
     *
     * @return ObjectMap
     */
    public function setReturnClass(bool $return_class): ObjectMap
    {
        $this->return_class = $return_class;

        return $this;
    }

    /**
     * @param string|string[] $data
     *
     * @return array|mixed
     */
    protected function out($data)
    {
        if (!$data) {
            return $data;
        }

        if (!is_array($data)) {
            return $this->return_class ? $data : new $data();
        }

        // sanitize
        $data = array_filter($data);

        return array_map(
            function ($element) {
                return $this->return_class ? $element : new $element();
            },
            $data
        );
    }
}
