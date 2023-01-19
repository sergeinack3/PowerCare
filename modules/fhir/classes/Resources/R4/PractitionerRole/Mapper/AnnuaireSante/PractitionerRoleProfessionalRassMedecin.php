<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\PractitionerRole\Mapper\AnnuaireSante;

use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIRAnnuaireSante;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\InteropSante\CFHIRResourcePractitionerFR;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\Mapper\PractitionerRoleMappingMedecin;
use Ox\Mediboard\Mediusers\CSpecCPAM;

class PractitionerRoleProfessionalRassMedecin extends PractitionerRoleMappingMedecin
{
    /**
     * @inheritDoc
     */
    public function onlyProfiles(): array
    {
        return [CFHIRAnnuaireSante::class];
    }

    public function mapExtension(): array
    {
        $extensions = parent::mapExtension();

        if ($discipline = $this->object->disciplines) {
            $extensions[] = CFHIRDataTypeExtension::addExtension(
                'https://apifhir.annuaire.sante.fr/ws-sync/exposed/structuredefinition/practitionerRole-name',
                [
                    'valueHumanName' => $discipline
                ]
            );
        }

        return $extensions;
    }


    public function mapCode(): array
    {
        $codes = parent::mapCode();

        $medecin = $this->object;
        // todo gestion des valueSet FHIR
        switch ($medecin->type) {
            case 'medecin':
                $code    = '10';
                $display = $text = 'Médecin';
                break;

            case 'pharmacie':
                $code    = '21';
                $display = $text = 'Pharmacien';
                break;

            case 'dentiste':
                $code    = '40';
                $display = $text ='Chirurgien-Dentiste';
                break;

            case 'infirmier':
                $code    = '60';
                $display = $text = 'Infirmier';
                break;
            default:
                return $codes;
        }
        $system  = 'urn:oid:1.2.250.1.71.1.2.7';
        $profession_G15_coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);

        $codes[] = CFHIRDataTypeCodeableConcept::addCodeable(
            $profession_G15_coding,
            $text
        );

        return $codes;
    }

    public function mapPractitioner(): ?CFHIRDataTypeReference
    {
        return $this->resource->addReference(CFHIRResourcePractitionerFR::class, $this->object);
    }

    public function mapSpecialty(): array
    {
        $specialities = parent::mapSpecialty();

        if (!$this->object->spec_cpam_id) {
            return $specialities;
        }

        $spec_CPAM = new CSpecCPAM($this->object->spec_cpam_id);

        $system  = 'urn:oid:1.2.250.1.213.2.28';
        $code    = $spec_CPAM->getMatchingRPPSOfCPAM($spec_CPAM->_id);
        $display = $text = $spec_CPAM->text;

        $savoir_faire_R38_coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $specialities[]          = CFHIRDataTypeCodeableConcept::addCodeable($savoir_faire_R38_coding, $text);

        return $specialities;
    }
}
