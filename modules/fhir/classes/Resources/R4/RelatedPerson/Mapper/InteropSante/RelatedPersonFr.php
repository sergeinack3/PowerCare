<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\RelatedPerson\Mapper\InteropSante;

use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Patient\Profiles\InteropSante\CFHIRResourcePatientFR;
use Ox\Interop\Fhir\Resources\R4\RelatedPerson\Mapper\RelatedPerson;
use Ox\Interop\Fhir\Resources\R4\RelatedPerson\Profiles\InteropSante\CFHIRResourceRelatedPersonFR;
use Ox\Mediboard\Patients\CCorrespondantPatient;

/**
 * Description
 */
class RelatedPersonFr extends RelatedPerson
{
    /** @var CFHIRResourceRelatedPersonFR */
    protected CFHIRResource $resource;

    public function onlyProfiles(): array
    {
        return [CFHIRInteropSante::class];
    }

    /**
     * @inheritDoc
     */
    public function mapPatient(): ?CFHIRDataTypeReference
    {
        $patient = $this->object->loadRefPatient();

        return $this->resource->addReference(CFHIRResourcePatientFR::class, $patient);
    }

    /**
     * @inheritDoc
     */
    public function mapRelationship(): array
    {
        $codeable = [];
        if ($coding = $this->getRolePerson()) {
            $codeable[] = $coding;
        }

        if ($coding = self::getRelatedPerson($this->object)) {
            $codeable[] = $coding;
        }

        return $codeable;
    }


    private function getRolePerson(): ?CFHIRDataTypeCodeableConcept
    {
        // todo mapping personne confiance ...

        return null;
        /*return CFHIRDataTypeCodeableConcept::fromValues(
            [
                "code"        => $code,
                "codeSystem"  => "https://mos.esante.gouv.fr/NOS/TRE_R260-HL7RoleClass/FHIR/TRE-R260-HL7RoleClass",
                "displayName" => $display,
            ]
        );*/
    }

    public static function getRelatedPerson(CCorrespondantPatient $correspondant_patient): ?CFHIRDataTypeCodeableConcept
    {
        $relation = $correspondant_patient->parente;
        switch ($relation) {
            case 'mere':
                $code    = 'MTH';
                $display = 'Mère';
                break;
            case 'pere':
                $code    = 'FTH';
                $display = 'Père';
                break;
            case 'grand_parent':
                $code    = 'GRPRN';
                $display = 'Grand-parent';
                break;
            case 'enfant':
                if ($correspondant_patient->sex === 'm') {
                    $code    = 'SONC';
                    $display = 'Fils';
                } elseif ($correspondant_patient->sex === 'f') {
                    $code    = 'DAUC';
                    $display = "Fille";
                } else {
                    return null;
                }
                break;
            default:
                return null;
        }

        return (new CFHIRDataTypeCodeableConcept())
            ->setCoding(
                (new CFHIRDataTypeCoding())
                    ->setCode($code)
                    ->setSystem("https://mos.esante.gouv.fr/NOS/TRE_R216-HL7RoleCode/FHIR/TRE-R216-HL7RoleCode")
                    ->setDisplay($display)
            );
    }
}
