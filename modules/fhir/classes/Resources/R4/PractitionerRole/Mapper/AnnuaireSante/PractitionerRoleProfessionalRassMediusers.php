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
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;
use Ox\Interop\Fhir\Profiles\CFHIRAnnuaireSante;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\Mapper\PractitionerRoleMappingMediusers;
use Ox\Mediboard\Mediusers\CSpecCPAM;
use Ox\Mediboard\Patients\CMedecin;

class PractitionerRoleProfessionalRassMediusers extends PractitionerRoleMappingMediusers
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

        if ($discipline = $this->object->loadRefDiscipline()) {
            $extensions[] = CFHIRDataTypeExtension::addExtension(
                'https://apifhir.annuaire.sante.fr/ws-sync/exposed/structuredefinition/practitionerRole-name',
                [
                    'value' => new CFHIRDataTypeHumanName($discipline->text),
                ]
            );
        }

        //return $extensions;

        return [];
    }

    public function mapCode(): array
    {
        $codes = parent::mapCode();

        $medecin = new CMedecin();
        $medecin->loadFromRPPS($this->object->rpps);
        if ($medecin->_id) {
            return $codes;
        }

        $map_medecin = new PractitionerRoleProfessionalRassMedecin();
        $map_medecin->setResource($this->resource, $medecin);

        return array_merge($codes, $map_medecin->mapCode());
    }

    public function mapSpecialty(): array
    {
        $specialities = parent::mapSpecialty();
        $spec_CPAM    = new CSpecCPAM($this->object->discipline_id);
        $system       = 'urn:oid:1.2.250.1.213.2.28';

        $code = $spec_CPAM->getMatchingRPPSOfCPAM($spec_CPAM->_id);
        $display = $text = $spec_CPAM->text;

        $savoir_faire_R38_coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $codeable_concept        = CFHIRDataTypeCodeableConcept::addCodeable($savoir_faire_R38_coding, $text);

        $specialities[] = $codeable_concept;

        return $specialities;
    }
}
