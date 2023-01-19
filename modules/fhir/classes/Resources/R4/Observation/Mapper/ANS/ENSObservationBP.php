<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Observation\Mapper\ANS;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Observation\CFHIRDataTypeObservationDiastolicBP;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Observation\CFHIRDataTypeObservationSystolicBP;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeQuantity;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceTrait;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationBPENS;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;
use Ox\Mediboard\Patients\Constants\CConstantException;
use ReflectionException;

/**
 * Description
 */
class ENSObservationBP extends ENSObservation
{
    use CStoredObjectResourceTrait;

    /** @var CAbstractConstant */
    protected $object;

    /** @var CFHIRResourceObservationBPENS */
    protected CFHIRResource $resource;

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceObservationBPENS::class];
    }

    /**
     * @param CFHIRResource     $resource
     * @param CAbstractConstant $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return false; // il faut que le object soit un tableau de deux constants [diastole, systole]

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
                    'value' => new CFHIRDataTypeString("Ma nouvelle pression artérielle !"),
                ]
            ),
        ];
    }

    public function mapCategory(): array
    {
        $system  = "http://terminology.hl7.org/CodeSystem/observation-category";
        $code    = "vital-signs";
        $display = "Signes vitaux";

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $text   = "Signes vitaux";

        return [CFHIRDataTypeCodeableConcept::addCodeable($coding, $text)];
    }

    public function mapCode(): ?CFHIRDataTypeCodeableConcept
    {
        $system  = "http://loinc.org";
        $code    = "85354-9";
        $display = "BPCode";

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $text   = 'Pression artérielle';

        return CFHIRDataTypeCodeableConcept::addCodeable($coding, $text);
    }

    /**
     * @throws CConstantException
     * @throws ReflectionException|\Psr\SimpleCache\InvalidArgumentException
     */
    public function mapComponent(): array
    {
        // todo soucis ici, il faut qu'on passe plusieurs AbstractConstant !!
        $system = 'http://loinc.org';

        $systole_coding = CFHIRDataTypeCoding::addCoding($system, '8480-6', 'Systolic blood pressure');

        $systole_valueQuantity = [
            'value'  => $this->object->getValue(),
            'unit'   => $this->object->getViewUnit(),
            'system' => new CFHIRDataTypeUri(
                "http://unitsofmeasure.org"
            ),
            'code'   => new CFHIRDataTypeCode("mm[Hg]"),
        ];

        $systole_value = new CFHIRDataTypeChoice(CFHIRDataTypeQuantity::class, $systole_valueQuantity);

        $systole_code = CFHIRDataTypeCodeableConcept::addCodeable($systole_coding, '');

        $systole_backbone = CFHIRDataTypeObservationSystolicBP::build([
                                                                          'code'  => $systole_code,
                                                                          'value' => $systole_value,
                                                                      ]);


        $diastole_coding = CFHIRDataTypeCoding::addCoding($system, '8462-4', 'Diastolic blood pressure');

        $diastole_valueQuantity = [
            'value'  => $this->object->getValue(),
            'unit'   => $this->object->getViewUnit(),
            'system' => new CFHIRDataTypeUri(
                "http://unitsofmeasure.org"
            ),
            'code'   => new CFHIRDataTypeCode("mm[Hg]"),
        ];

        $diastole_value = new CFHIRDataTypeChoice(CFHIRDataTypeQuantity::class, $diastole_valueQuantity);

        $diastole_code = CFHIRDataTypeCodeableConcept::addCodeable($diastole_coding, '');

        $diastole_backbone = CFHIRDataTypeObservationDiastolicBP::build([
                                                                            'code'  => $diastole_code,
                                                                            'value' => $diastole_value,
                                                                        ]);

        return [$systole_backbone, $diastole_backbone];
    }
}
