<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\CapabilityStatement\Mapper;

use Ox\Interop\Fhir\Actors\CSenderFHIR;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\CapabilityStatementMappingInterface;
use Ox\Interop\Fhir\Contracts\Mapping\ResourceMappingInterface;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\CapabilityStatement\CFHIRResourceCapabilityStatement;
use Ox\Mediboard\System\CSenderHTTP;

/**
 * FIHR CapabilityStatement resource
 */
class CapabilityStatementFromSender implements DelegatedObjectMapperInterface
{
    private CFHIRResourceCapabilityStatement $capability;
    private CSenderFHIR $sender_FHIR;

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceCapabilityStatement::RESOURCE_TYPE];
    }

    /**
     * @return array
     */
    public function onlyProfiles(): ?array
    {
        return null; // for all profiles
    }

    /**
     * @param CFHIRResource $resource
     * @param mixed $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        if ($object === null && ($sender = $resource->getSender())) {
            $object = $sender;
        }

        if (!$object instanceof CSenderHTTP && !$object instanceof CSenderFHIR) {
            return false;
        }

        if ($object instanceof CSenderHTTP && (!$object->_id || !$object->user_id)) {
            return false;
        }

        return true;
    }

    /**
     * @param CFHIRResource           $resource
     * @param CSenderFHIR|CSenderHTTP $object
     *
     * @return void
     */
    public function setResource(CFHIRResource $resource, $object): void
    {
        $this->resource = $resource;

        if ($object === null) {
            $object = $resource->getSender();
        }

        if ($object instanceof CSenderHTTP) {
            $object = new CSenderFHIR($object);
        }

        $this->sender_FHIR = $object;
    }

    /**
     * @return CapabilityStatementMappingInterface
     */
    public function getMapping(): ResourceMappingInterface
    {
        return new CapabilityStatementFromSenderMapping();
    }
}
