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
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeQuantity;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceTrait;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationBodyTemperatureENS;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;
use Ox\Mediboard\Patients\Constants\CConstantException;

/**
 * Description
 */
class ENSObservationBodyTemperature extends ENSObservation
{
    use CStoredObjectResourceTrait;

    /** @var CAbstractConstant */
    protected $object;

    /** @var CFHIRResourceObservationBodyTemperatureENS */
    protected CFHIRResource $resource;

    public function onlyRessources(): array
    {
        return [CFHIRResourceObservationBodyTemperatureENS::class];
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

        return $spec->code === 'temperature';
    }

    public function mapExtension(): array
    {
        //TODO Change the codeableconcept once the levelOfExertion is up
        $system  = "https://mos.esante.gouv.fr/NOS/TRE_R306-CLADIMED/FHIR/TRE-R306-CLADIMED";
        $code    = "K50BI02";
        $display = "Balance";

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $text   = "Balance";

        $data = [
            'url'       => 'http://interopsante.org/fhir/StructureDefinition/FrObservationLevelOfExertion',
            'extension' => 'levelOfExertion',
            'value'     => CFHIRDataTypeCodeableConcept::addCodeable($coding, $text),
        ];

        $extension = [new CFHIRDataTypeExtension($data)];

        $data = [
            'url'       => 'http://esante.gouv.fr/ci-sis/fhir/StructureDefinition/ENS_ReasonForMeasurement',
            'extension' => 'ENS_ReasonForMeasurement',
            'value'     => 'Ma nouvelle température !',
        ];

        $extension[] = new CFHIRDataTypeExtension($data);

        return $extension;
    }

    public function mapCode(): ?CFHIRDataTypeCodeableConcept
    {
        $system  = "http://loinc.org";
        $code    = "8310-5";
        $display = "BodyTempCode";

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $text   = 'Température corporelle';

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
            'code'   => new CFHIRDataTypeCode("Cel"),
        ];

        return new CFHIRDataTypeChoice(CFHIRDataTypeQuantity::class, $valueQuantity);
    }
}
