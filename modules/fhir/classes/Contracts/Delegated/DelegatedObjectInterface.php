<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Delegated;

/**
 * Description
 */
interface DelegatedObjectInterface
{
    /**
     * Filter delegated for only profiles list here.
     * You should return profile class (namespaced).
     * If array is empty, the delegated object will be associate with none profiles. (autoconfiguration disable)
     * If null is return no filter will be applied.
     *
     * @return string[]|null
     */
    public function onlyProfiles(): ?array;

    /**
     * Filter delegated for only resources list here.
     * You could return resource class (namespaced).
     * You could return resource type (fhir).
     * If array is empty, the delegated object will be associate with none resources (autoconfiguration disable)
     * If null is return no filter will be applied and all resources can be used by this delegated object
     *
     * @return string[]|null
     */
    public function onlyRessources(): ?array;
}
