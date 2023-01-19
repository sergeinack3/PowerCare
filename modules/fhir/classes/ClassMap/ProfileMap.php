<?php

/**
 * @package Mediboard\Fhir\Resources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\ClassMap;

use Ox\Core\CMbArray;
use Ox\Interop\Fhir\Profiles\CFHIR;

class ProfileMap extends ObjectMap
{
    /** @var string */
    protected const KEY_MAP = 'profiles';

    /**
     * @return CFHIR[]|string[]
     */
    public function listProfiles(?string $resource_type = null): array
    {
        $version = $this->version;

        if ($resource_type) {
            $profiles = CMbArray::getRecursive($this->map, "$version resource_type $resource_type", []);
        } else {
            $profiles = array_keys(CMbArray::getRecursive($this->map, "$version profile_class", []));
        }

        return $this->out(array_unique($profiles));
    }

    /**
     * @param string      $profile_class
     * @param string|null $fhir_version
     *
     * @return string[]
     */
    public function getCanonicalsFromProfileClass(string $profile_class): array
    {
        $version = $this->version;

        return CMbArray::getRecursive($this->map, "$version profile_class $profile_class", []);
    }
}
