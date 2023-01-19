<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Mediboard\Cabinet\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;

/**
 * Fixtures module Cabinet
 */
class CabinetFixtures extends Fixtures
{
    public const TAG_CONSULT_REMPL = "CONSULTATION_REMPLACANT";
    public const TAG_CONSULT_PRAT  = "CONSULTATION_PRATICIEN";

    /** @var CMediusers */
    private $medecin;
    /** @var CMediusers */
    private $medecin_2;
    /** @var CMediusers */
    private $remplacant;

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    public function load()
    {
        $this->generateMedecin();
        $plage_consult = $this->generatePlageConsultation($this->medecin);
        $this->generateConsultation(self::TAG_CONSULT_PRAT, $plage_consult);

        $plage_rempl = $this->generatePlageConsultation($this->medecin_2, $this->remplacant);
        $this->generateConsultation(self::TAG_CONSULT_REMPL, $plage_rempl);
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    public function generatePlageConsultation(CMediusers $praticien, CMediusers $remplacant = null): CPlageconsult
    {
        /** @var CPlageconsult $plageconsult */
        $plageconsult                = CPlageconsult::getSampleObject(CPlageconsult::class);
        $date                        = CMbDT::date();
        $plageconsult->date          = CMbDT::isHoliday($date) ? CMbDT::getNextWorkingDay($date) : $date;
        $plageconsult->chir_id       = $praticien->_id;
        $plageconsult->remplacant_id = $remplacant ? $remplacant->_id : null;
        $plageconsult->debut         = CMbDT::time("08:00:00");
        $plageconsult->fin           = CMbDT::time("18:00:00");
        $plageconsult->freq          = CMbDT::time("00:30:00");

        $this->store($plageconsult);

        return $plageconsult;
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    public function generateConsultation(string $tag, CPlageconsult $plage): void
    {
        /** @var CConsultation $consultation */
        $consultation                  = CStoredObject::getSampleObject(CConsultation::class);
        $consultation->plageconsult_id = $plage->_id;

        $this->store($consultation, $tag);
    }

    /**
     * @throws FixturesException
     */
    public function generateMedecin(): void
    {
        $users = $this->getUsers(3);

        $medecin             = array_pop($users);
        $medecin->_user_type = 13;
        $this->store($medecin);
        $this->medecin = $medecin;

        $medecin_2             = array_pop($users);
        $medecin_2->_user_type = 13;
        $this->store($medecin_2);
        $this->medecin_2 = $medecin_2;

        $remplacant             = array_pop($users);
        $remplacant->_user_type = 13;
        $this->store($remplacant);
        $this->remplacant = $remplacant;
    }
}
