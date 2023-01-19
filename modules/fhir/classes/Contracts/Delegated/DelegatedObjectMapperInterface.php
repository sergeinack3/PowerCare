<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Delegated;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceMappingInterface;
use Ox\Interop\Fhir\Resources\CFHIRResource;

/**
 * Description
 */
interface DelegatedObjectMapperInterface extends DelegatedObjectInterface
{
    /**
     * Set resource on delegated object
     *
     * @param CFHIRResource $resource
     * @param mixed|object|array $object
     */
    public function setResource(CFHIRResource $resource, $object): void;

    /**
     * Know if this delegated can support the resource and object or data given
     *
     * @param CFHIRResource      $resource
     * @param mixed|object|array $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool;

    /**
     * Get object mapping
     *
     * @return ResourceMappingInterface
     */
    public function getMapping(): ResourceMappingInterface;
}
