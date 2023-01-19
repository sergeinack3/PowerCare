<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Observation\Mapper\ANS;

use Exception;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIRMES;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Device\Profiles\PHD\CFHIRResourceDevicePHD;
use Ox\Interop\Fhir\Resources\R4\Observation\Mapper\Observation;
use Ox\Interop\Mes\Device;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Description
 */
class ENSObservation extends Observation
{
    /**
     * @inheritDoc
     */
    public function onlyProfiles(): array
    {
        return [CFHIRMES::class];
    }

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CAbstractConstant && $object->_id;
    }

    public function mapCategory(): array
    {
        $system  = "http://terminology.hl7.org/CodeSystem/observation-category";
        $code    = "vital-signs";
        $display = "Vital Signs";

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $text   = "Signes vitaux";

        return [CFHIRDataTypeCodeableConcept::addCodeable($coding, $text)];
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function mapDevice(): ?CFHIRDataTypeReference
    {
        $device       = new Device();
        $device->name = 'Relevé manuel';
        $device->loadMatchingObject();
        $this->object->_uuid = 'urn:uuid:' . $device->getUuid();

        return $this->resource->addReference(CFHIRResourceDevicePHD::class, $this->object);
    }
}
