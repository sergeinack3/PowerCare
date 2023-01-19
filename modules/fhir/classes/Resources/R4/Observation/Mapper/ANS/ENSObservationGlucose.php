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
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationBodyWeightENS;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;
use Ox\Mediboard\Patients\Constants\CConstantException;

/**
 * Description
 */
class ENSObservationGlucose extends ENSObservation
{
    /** @var CAbstractConstant */
    protected $object;

    /** @var CFHIRResourceObservationBodyWeightENS */
    protected CFHIRResource $resource;

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceObservationBodyWeightENS::class];
    }

    /**
     * @param CFHIRResource     $resource
     * @param CAbstractConstant $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return false; // pas de mapping actuellement

        if (!parent::isSupported($resource, $object)) {
            return false;
        }

        $spec = $object->getRefSpec();

        return $spec->code === '';
    }

    public function mapExtension(): array
    {
        return [
            CFHIRDataTypeExtension::addExtension(
                "http://esante.gouv.fr/ci-sis/fhir/StructureDefinition/ENS_ReasonForMeasurement",
                [
                    'value' => new CFHIRDataTypeString("Ma nouvelle glyc�mie !"),
                ]
            ),
        ];
    }

    public function mapCode(): ?CFHIRDataTypeCodeableConcept
    {
        $system  = "http://loinc.org";
        $code    = "2345-7";
        $display = "glucose";

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $text   = "Glyc�mie";

        return CFHIRDataTypeCodeableConcept::addCodeable($coding, $text);
    }

    /**
     * @throws CConstantException
     */
    public function mapValue(): ?CFHIRDataTypeChoice
    {
        $valueQuantity = [
            'value'  => $this->object->getValue(),
            'unit'   => 'mg/dL',
            'system' => new CFHIRDataTypeUri("http://unitsofmeasure.org"),
            'code'   => new CFHIRDataTypeCode("mg/dL"),
        ];

        return new CFHIRDataTypeChoice(CFHIRDataTypeQuantity::class, $valueQuantity);
    }

    public function mapMethod(): ?CFHIRDataTypeCodeableConcept
    {
        $system  = "https://mos.esante.gouv.fr/NOS/TRE_R306-CLADIMED/FHIR/TRE-R306-CLADIMED";
        $code    = "K50BI02";
        $display = "Balance";

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $text   = "Balance";

        return CFHIRDataTypeCodeableConcept::addCodeable($coding, $text);
    }
}
