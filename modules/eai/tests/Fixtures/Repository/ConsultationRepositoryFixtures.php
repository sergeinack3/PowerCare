<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tests\Fixtures\Repository;

use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Patients\CPatient;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * @description Use for test algorithms used in interop to record the Patient
 */
class ConsultationRepositoryFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const REF_CONSULTATION = 'ref_primary_consultation_repository';

    /** @var string */
    public const CONSULTATION_DATE = "2022-01-20 10:00:00";

    /**
     * @inheritDoc
     * @throws FixturesException|CModelObjectException
     */
    public function load()
    {
        // Patient
        $patient = CPatient::getSampleObject();
        $this->store($patient);

        $sejour = SejourRepositoryFixtures::makePrimarySejour($patient->_id);
        $this->store($sejour);

        $plage_consult = self::makePrimaryPlageConsult();
        $this->store($plage_consult);

        $consultation = self::makePrimaryConsultation($plage_consult, $patient->_id);
        $consultation->sejour_id = $sejour->_id;
        $this->store($consultation, self::REF_CONSULTATION);
    }

    /**
     * @inheritDoc
     */
    public static function getGroup(): array
    {
        return ['eai-repository'];
    }

    /**
     * @param CPlageconsult $plageconsult
     * @param string        $patient_id
     *
     * @return CConsultation
     */
    public static function makePrimaryConsultation(CPlageconsult $plageconsult, string $patient_id): CConsultation
    {
        $consultation                  = new CConsultation();
        $consultation->patient_id      = $patient_id;
        $consultation->plageconsult_id = $plageconsult->_id;
        $consultation->heure           = CMbDT::format(self::CONSULTATION_DATE, CMbDT::ISO_TIME);
        $consultation->chrono          = CConsultation::PLANIFIE;

        return $consultation;
    }

    public static function makePrimaryPlageConsult(): CPlageconsult
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = SejourRepositoryFixtures::getMediusers()->_id;
        $plage_consult->date    = CMbDT::format(self::CONSULTATION_DATE, CMbDT::ISO_DATE);
        $plage_consult->debut   = "10:00:00";
        $plage_consult->fin     = "18:00:00";
        $plage_consult->freq    = '00:15:00';

        return $plage_consult;
    }
}
