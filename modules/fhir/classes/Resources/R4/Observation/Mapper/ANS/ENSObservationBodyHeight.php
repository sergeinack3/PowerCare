<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Observation\Mapper\ANS;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeQuantity;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceTrait;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationBodyHeightENS;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;
use Ox\Mediboard\Patients\Constants\CConstantException;

/**
 * Description
 */
class ENSObservationBodyHeight extends ENSObservation
{
    use CStoredObjectResourceTrait;

    public const OBSERVATION_CODE = "8302-2";

    /** @var CAbstractConstant */
    protected $object;

    /** @var CFHIRResourceObservationBodyHeightENS */
    protected CFHIRResource $resource;

    public function onlyRessources(): array
    {
        return [CFHIRResourceObservationBodyHeightENS::class];
    }

    /**
     * @param CFHIRResource     $resource
     * @param CAbstractConstant $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        if (!parent::isSupported($resource, $object)) {
            return false;
        }

        $spec = $object->getRefSpec();

        return $spec->code === 'height';
    }

    public function mapCode(): ?CFHIRDataTypeCodeableConcept
    {
        $system  = "http://loinc.org";
        $code    = self::OBSERVATION_CODE;
        $display = "height";

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $text   = "Taille corporelle";

        return CFHIRDataTypeCodeableConcept::addCodeable($coding, $text);
    }

    /**
     * @throws CConstantException
     */
    public function mapValue(): ?CFHIRDataTypeChoice
    {
        $valueQuantity = [
            'value'  => $this->object->getValue(),
            'unit'   => $this->object->getViewUnit(),
            'system' => new CFHIRDataTypeUri("http://unitsofmeasure.org"),
            'code'   => new CFHIRDataTypeCode("cm"),
        ];

        return new CFHIRDataTypeChoice(CFHIRDataTypeQuantity::class, $valueQuantity);
    }
}
