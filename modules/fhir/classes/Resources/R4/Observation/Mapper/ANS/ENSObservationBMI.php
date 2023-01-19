<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Observation\Mapper\ANS;

use Exception;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeQuantity;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationBMIENS;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;
use Ox\Mediboard\Patients\Constants\CConstantException;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Description
 */
class ENSObservationBMI extends ENSObservation
{
    /** @var CAbstractConstant */
    protected $object;

    /** @var CFHIRResourceObservationBMIENS */
    protected CFHIRResource $resource;

    public function onlyRessources(): array
    {
        return [CFHIRResourceObservationBMIENS::class];
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

        return $spec->code === 'imc';
    }

    public function mapCode(): ?CFHIRDataTypeCodeableConcept
    {
        $system  = "http://loinc.org";
        $code    = "39156-5";
        $display = "BMICode";

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $text   = 'Indice de Masse Corporelle';

        return CFHIRDataTypeCodeableConcept::addCodeable($coding, $text);
    }

    /**
     * @throws CConstantException
     */
    public function mapValue(): ?CFHIRDataTypeChoice
    {
        // L'unité de l'IMC est généralement en kg/m2
        // Ici on a l'unité g/mm²
        $valueQuantity = [
            'value'  => $this->object->getValue(),
            'unit'   => "kg/m2", // todo Attention mauvaise unité
            'system' => new CFHIRDataTypeUri("http://unitsofmeasure.org"),
            'code'   => new CFHIRDataTypeCode("kg/m2"),
        ];

        return new CFHIRDataTypeChoice(CFHIRDataTypeQuantity::class, $valueQuantity);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function mapReferenceRange(): array
    {
        $system = "https://mos.esante.gouv.fr/NOS/TRE_R303-HL7v3AdministrativeGender/FHIR/TRE-R303-HL7v3AdministrativeGender";

        $patient = $this->object->loadRefPatient();
        $sexe    = $patient->sexe;

        $grossesse = $patient->loadLastGrossesse();

        if (!$grossesse) {
            return [];
        }

        $isPregnant = $grossesse->active;

        $code    = $sexe;
        $display = $sexe === 'M' ? 'Masculin' : 'Féminin';

        if ($isPregnant) {
            $system  = "https://mos.esante.gouv.fr/NOS/TRE_A04-Loinc/FHIR/TRE-A04-Loinc";
            $code    = "LA15173-0";
            $display = "Femme enceinte";
        }

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $text   = $display;

        $appliesTo = CFHIRDataTypeCodeableConcept::addCodeable($coding, $text);

        return [
            CFHIRDataTypeBackboneElement::build([
                                                    'appliesTo' => $appliesTo,
                                                ]),
        ];
    }
}
