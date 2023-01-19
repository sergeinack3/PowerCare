<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Observation\Mapper\ANS;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeQuantity;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceTrait;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationStepsByDayENS;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;
use Ox\Mediboard\Patients\Constants\CConstantException;

/**
 * Description
 */
class ENSObservationStepByDay extends ENSObservation
{
    use CStoredObjectResourceTrait;

    /** @var CAbstractConstant */
    protected $object;

    /** @var CFHIRResourceObservationStepsByDayENS */
    protected CFHIRResource $resource;

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

        return $spec->code === 'dailyactivity';
    }

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceObservationStepsByDayENS::class];
    }

    public function mapExtension(): array
    {
        return [
            CFHIRDataTypeExtension::addExtension(
                "http://esante.gouv.fr/ci-sis/fhir/StructureDefinition/ENS_ReasonForMeasurement",
                [
                    'value' => new CFHIRDataTypeString("Ma nouvelle activité quotidienne !"),
                ]
            ),
        ];
    }

    public function mapCode(): ?CFHIRDataTypeCodeableConcept
    {
        $system  = "http://loinc.org";
        $code    = "41950-7";
        $display = "Number of steps in 24 hour Measured";

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $text   = "Nombre de pas mesuré(s) ces dernières 24h";

        return CFHIRDataTypeCodeableConcept::addCodeable($coding, $text);
    }

    /**
     * @throws CConstantException
     */
    public function mapValue(): ?CFHIRDataTypeChoice
    {
        $valueQuantity = [
            'value'  => $this->object->getValue(),
            'unit'   => 'steps/day',
            'system' => new CFHIRDataTypeUri("http://unitsofmeasure.org"),
            'code'   => new CFHIRDataTypeCode("1/(24.h)"),
        ];

        return new CFHIRDataTypeChoice(CFHIRDataTypeQuantity::class, $valueQuantity);
    }
}
