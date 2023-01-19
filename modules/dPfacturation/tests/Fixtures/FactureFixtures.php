<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;

class FactureFixtures extends Fixtures
{
    public const TAG_PATIENT = 'patient_facture';

    public const TAG_CONSULTATION = 'consultation_facture';

    public const TAG_SEJOUR = 'sejour_facture';

    public const TAG_EVENEMENT = 'evenement_facture';

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    public function load(): void
    {
        $patient   = $this->generatePatient(self::TAG_PATIENT);
        $praticien = $this->getUser();

        $this->generateConsultation(self::TAG_CONSULTATION, $patient, $praticien);
        $this->generateSejour(self::TAG_SEJOUR, $patient, $praticien);
        $this->generateEvenementPatient(self::TAG_EVENEMENT, $patient, $praticien);
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generatePatient(string $tag = null): CPatient
    {
        $patient = CPatient::getSampleObject();
        $this->store($patient, $tag);

        return $patient;
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generateConsultation(string $tag, CPatient $patient, CMediusers $praticien): void
    {
        /** @var CPlageconsult $plage */
        $plage          = new CPlageconsult();
        $plage->chir_id = $praticien->_id;
        $plage->date    = '2020-01-01';
        $plage->debut   = CMbDT::time("08:00:00");
        $plage->fin     = CMbDT::time("18:00:00");
        $plage->freq    = CMbDT::time("00:30:00");
        $this->store($plage);

        $consultation                  = CConsultation::getSampleObject();
        $consultation->patient_id      = $patient->_id;
        $consultation->plageconsult_id = $plage->_id;
        $consultation->heure           = $plage->debut;
        $this->store($consultation, $tag);
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generateSejour(string $tag, CPatient $patient, CMediusers $praticien): void
    {
        /** @var CSejour $sejour */
        $sejour               = CSejour::getSampleObject();
        $sejour->patient_id   = $patient->_id;
        $sejour->praticien_id = $praticien->_id;
        $sejour->group_id     = $praticien->loadRefFunction()->group_id;
        $this->store($sejour, $tag);
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generateEvenementPatient(string $tag, CPatient $patient, CMediusers $praticien): void
    {
        $evenement_patient                     = CEvenementPatient::getSampleObject();
        $evenement_patient->dossier_medical_id = CDossierMedical::dossierMedicalId($patient->_id, $patient->_class);
        $evenement_patient->owner_id           = $praticien->_id;

        $this->store($evenement_patient, $tag);
    }
}
