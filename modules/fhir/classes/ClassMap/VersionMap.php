<?php

/**
 * @package Mediboard\Fhir\Resources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\ClassMap;

use Exception;
use Ox\Core\CMbArray;

class VersionMap extends ObjectMap
{
    /** @var string */
    protected const KEY_MAP = 'versions';

    /**
     * @param string|null $resource_type
     *
     * @return string[]
     */
    public function getSupportedFhirVersions(?string $canonical = null): array
    {
        if ($canonical) {
            return CMbArray::getRecursive($this->map, "canonical $canonical fhir_version", []);
        }

        return CMbArray::getRecursive($this->map, 'fhir_version', []);
    }

    /**
     * @param string $canonical
     *
     * @return string[]
     */
    public function getResourceVersions(string $canonical): array
    {
        return array_values($this->map['canonical'][$canonical] ?? []);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getLastSupportedFhirVersion(): string
    {
        $versions = $this->getSupportedFhirVersions();

        return end($versions);
    }
}
