<?php

/**
 * @package Mediboard\Fhir\Resources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\ClassMap;

use Ox\Core\CMbArray;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectHandleInterface;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectInterface;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectSearcherInterface;

class DelegatedMap extends ObjectMap
{
    /** @var string */
    public const TYPE_DELEGATED_MAPPER = "mapper";
    /** @var string */
    public const TYPE_DELEGATED_SEARCHER = "searcher";
    /** @var string */
    public const TYPE_DELEGATED_HANDLE = "handle";

    /** @var string */
    protected const KEY_MAP = 'delegated';

    /**
     * @param string $type
     * @param string $canonical_or_type
     *
     * @return DelegatedObjectInterface[]|string[]
     */
    public function listDelegated(string $type, string $canonical_or_type = null): array
    {
        switch ($type) {
            case self::TYPE_DELEGATED_HANDLE:
                return $this->listDelegatedHandle($canonical_or_type);
            case self::TYPE_DELEGATED_MAPPER:
                return $this->listDelegatedMapper($canonical_or_type);
            case self::TYPE_DELEGATED_SEARCHER:
                return $this->listDelegatedSearcher($canonical_or_type);
            default:
                return [];
        }
    }

    /**
     * @param string $canonical_or_type
     *
     * @return DelegatedObjectMapperInterface[]|string[]
     */
    public function listDelegatedMapper(string $canonical_or_type = null): array
    {
        return $this->listSpecificDelegated('mapper', $canonical_or_type);
    }

    /**
     * @param string $key
     * @param string $canonical_or_type
     *
     * @return DelegatedObjectInterface[]|string[]
     */
    private function listSpecificDelegated(string $key, ?string $canonical_or_type): array
    {
        $version = $this->version;
        if (!$canonical_or_type) {
            return $this->out(CMbArray::getRecursive($this->map, "$version $key all", []));
        }

        $is_canonical = strrpos($canonical_or_type, '/') !== false;

        if ($is_canonical) {
            return $this->out(CMbArray::getRecursive($this->map, "$version $key canonical $canonical_or_type", []));
        } else {
            return $this->out(CMbArray::getRecursive($this->map, "$version $key type $canonical_or_type", []));
        }
    }

    /**
     * @param string $canonical_or_type
     *
     * @return DelegatedObjectSearcherInterface[]|string[]
     */
    public function listDelegatedSearcher(string $canonical_or_type = null): array
    {
        return $this->listSpecificDelegated('searcher', $canonical_or_type);
    }

    /**
     * @param string $canonical_or_type
     *
     * @return DelegatedObjectHandleInterface[]|string[]
     */
    public function listDelegatedHandle(string $canonical_or_type = null): array
    {
        return $this->listSpecificDelegated('handle', $canonical_or_type);
    }

    /**
     * @param string $short_name
     *
     * @return DelegatedObjectHandleInterface|string|null
     */
    public function getDelegatedMapperFromShortName(string $short_name)
    {
        return $this->getDelegatedFromShortName('mapper', $short_name);
    }

    public function getDelegatedSearcherFromShortName(string $short_name)
    {
        return $this->getDelegatedFromShortName('searcher', $short_name);
    }

    public function getDelegatedHandleFromShortName(string $short_name)
    {
        return $this->getDelegatedFromShortName('handle', $short_name);
    }

    /**
     * @param string $key
     * @param string $short_name
     *
     * @return DelegatedObjectHandleInterface|string
     */
    public function getDelegatedFromShortName(string $key, string $short_name)
    {
        $version = $this->version;
        $all_delegated = CMbArray::getRecursive($this->map, "$version $key all", []);

        $filtered_delegated = array_filter(
            $all_delegated,
            function ($class_name) use ($short_name) {
                return str_ends_with($class_name, "\\$short_name");
            }
        );

        if (count($filtered_delegated) !== 1) {
            return null;
        }

        return $this->out(reset($filtered_delegated));
    }
}
