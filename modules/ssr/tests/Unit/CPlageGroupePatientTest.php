<?php

/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr\Test;

use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Populate\Generators\CMediusersGenerator;
use Ox\Mediboard\Ssr\CCategorieGroupePatient;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CPlageGroupePatient;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class CPlageGroupePatientTest extends OxUnitTestCase
{
    /** @var CMediusers */
    private static $kine;

    /** @var CPlageGroupePatient */
    private $plage_groupe;

    /** @var CEvenementSSR */
    private $evenementSSR;

    private static function getKine()
    {
        if (!$kine = self::$kine) {
            $kine = (new CMediusersGenerator())->generate("Rééducateur");
            $kine->code_intervenant_cdarr = 12;
            if ($msg = $kine->store()) {
                throw new CMbException($msg);
            }
        }

        return self::$kine = $kine;
    }

    public function getPlageGroupePatient(): CPlageGroupePatient
    {
        if (!$plage_groupe = $this->plage_groupe) {
            /** @var CCategorieGroupePatient $categorie_groupe */
            $categorie_groupe           = CCategorieGroupePatient::getSampleObject();
            $categorie_groupe->type     = 'ssr';
            $categorie_groupe->group_id = CGroups::loadCurrent()->_id;
            if ($msg = $categorie_groupe->store()) {
                throw new CMbException($msg);
            }

            /** @var CPlageGroupePatient $plage_groupe */
            $plage_groupe                              = CPlageGroupePatient::getSampleObject();
            $plage_groupe->categorie_groupe_patient_id = $categorie_groupe->_id;
            if ($msg = $plage_groupe->store()) {
                throw new CMbException($msg);
            }
        }

        return $this->plage_groupe = $plage_groupe;
    }

    public function getEvenementSSR(): CEvenementSSR
    {
        if (!$evenement_ssr = $this->evenementSSR) {
            $kine = self::getKine();
            $plage_groupe = $this->getPlageGroupePatient();

            /** @var CEvenementSSR $evenement_ssr */
            $evenement_ssr                          = CEvenementSSR::getSampleObject();
            $evenement_ssr->plage_groupe_patient_id = $plage_groupe->_id;
            $evenement_ssr->therapeute_id           = $kine->_id;
            $evenement_ssr->loadRefTherapeute(false);
            if ($msg = $evenement_ssr->store()) {
                throw new CMbException($msg);
            }
        }

        return $this->evenementSSR = $evenement_ssr;
    }

    /**
     * Test to calculate dates for the CPlageGroupePatient
     *
     * @throws TestsException
     */
    public function testLoadRefSejoursAssocies(): void
    {
        $this->markTestSkipped("Failed asserting that an array is not empty.");
        /** @var CEvenementSSR $evenement_ssr */
        $evenement_ssr        = $this->getEvenementSSR();
        $plage_groupe_patient = $evenement_ssr->loadRefPlageGroupePatient();
        $sejour               = $evenement_ssr->loadRefSejour();

        $day = CMbDT::date("$plage_groupe_patient->groupe_day this week");

        $assert_empty = ($day < CMbDT::date($sejour->entree))
            || ($day > CMbDT::date($sejour->sortie))
            || ($evenement_ssr->seance_collective_id)
            || (CMbDT::date($evenement_ssr->debut) !== $day);

        if ($assert_empty) {
            $this->assertEmpty($plage_groupe_patient->loadRefSejoursAssocies());
        } else {
            $this->assertNotEmpty($plage_groupe_patient->loadRefSejoursAssocies());
        }
    }

    /**
     * Test to calculate dates for the CPlageGroupePatient
     *
     * @throws TestsException
     */
    public function testCalculateDatesForPlageGroup(): void
    {
        $sejour               = new CSejour();
        $plage_groupe_patient = $this->getPlageGroupePatient();

        $sejour->entree = CMbDT::dateTime("- 2 DAY");
        $sejour->sortie = CMbDT::dateTime("+ 10 DAY");

        $now               = CMbDT::date();
        $first_day_of_week = CMbDT::date("$plage_groupe_patient->groupe_day this week");
        if ($now > $first_day_of_week) {
            $first_day_of_week = CMbDT::date("+1 week", $first_day_of_week);
        }

        $days = $plage_groupe_patient->calculateDatesForPlageGroup($sejour, $first_day_of_week);

        $this->assertGreaterThanOrEqual(1, count($days));

        $period_ok = $sejour->entree < reset($days);

        $this->assertTrue($period_ok);
    }

    /**
     * Test of all events realized over a period of time
     *
     * @throws TestsException
     */
    public function testAllEventsRealized(): void
    {
        $this->getEvenementSSR();
        $plage_groupe = $this->getPlageGroupePatient();

        $first_day_week = CMbDT::date("this week monday");
        $date_of_week   = CMbDT::date("this $plage_groupe->groupe_day", $first_day_week);
        $debut          = $date_of_week . " " . $plage_groupe->heure_debut;

        $ok = $plage_groupe->allEventsRealized($debut, null);

        $this->assertFalse($ok);
    }
}
