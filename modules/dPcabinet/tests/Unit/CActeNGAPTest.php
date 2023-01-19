<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Populate\Generators\CMediusersGenerator;
use Ox\Mediboard\Populate\Generators\CPatientGenerator;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CActeNGAPTest
 * @package Unit
 */
class CActeNGAPTest extends OxUnitTestCase
{
    /**
     * Vérifie que le complément de Nuit est bien ajouté automatiquement
     */
    public function testComplementNuitAuto(): void
    {
        $date = CMbDT::date();
        /* Ensure that the date is not a sunday or an holyday, for ensuring that the test does not fail */
        while (!CMbDT::isWorkingDay($date)) {
            $date = CMbDT::date('+1 day', $date);
        }

        $act = $this->createActe('CS', 41, "{$date} 21:15:00");

        $this->assertEquals('N', $act->complement);
    }

    /**
     * Vérifie que le complément de Nuit est bien supprimé si l'heure d'exécution est modifiée
     */
    public function testRemoveComplementNuitAuto(): void
    {

        $date = CMbDT::date();
        /* Ensure that the date is not a sunday or an holyday, for ensuring that the test does not fail */
        while (!CMbDT::isWorkingDay($date)) {
            $date = CMbDT::date('+1 day', $date);
        }

        $act = self::createActe('CS', 41, "{$date} 21:15:00");

        $act->execution = "{$date} 18:15:00";
        if ($msg = $act->store()) {
            $this->fail($msg);
        }

        $this->assertEquals('', $act->complement);
    }

    /**
     * Vérifie que le complément Férié est bien appliqué si la date d'exécution tombe un jour férié
     *
     * @config ref_pays 1
     */
    public function testComplementFerieAuto(): void
    {
        $act = $this->createActe('CS', 41, CMbDT::format(CMbDT::date(), '%Y-08-15 18:00:00'));

        $this->assertEquals('F', $act->complement);
    }

    /**
     * Vérifie que le complément Férié est bien appliqué si la date d'exécution tombe un dimanche
     */
    public function testComplementFerieSundayAuto(): void
    {
        $act = $this->createActe('CS', 41, CMbDT::date('next sunday') . ' 18:00:00');

        $this->assertEquals('F', $act->complement);
    }

    /**
    * Vérifie que le complément Férié est bien supprimé si la date d'exécution est modifiée
    */
    public function testRemoveComplementFerieAuto(): void
    {
        $act = $this->createActe('CS', 41, CMbDT::date('next sunday') . ' 18:00:00');

        $act->execution = CMbDT::date('next monday') . ' 18:00:00';
        while (!CMbDT::isWorkingDay(CMbDT::date($act->execution))) {
            $act->execution = CMbDT::dateTime('+1 day', $act->execution);
        }

        if ($msg = $act->store()) {
            $this->fail($msg);
        }

        $this->assertEquals('', $act->complement);
    }

    /**
     * Vérifie que le complément Férié est prioritaire sur le Nuit
     */
    public function testPrioriteComplementNuitAuto(): void
    {
        $act = $this->createActe('CS', 41, CMbDT::date('next sunday') . ' 21:00:00');

        $this->assertEquals('N', $act->complement);
    }

    /**
     * Vérifie que les compléments ne sont pas appliqués si ils ne sont pas autorisés pour l'acte
     */
    public function testComplementNonAutorise(): void
    {
        $act = $this->createActe('MPC', 41, CMbDT::date('next sunday') . ' 21:00:00');

        $this->assertEquals('', $act->complement);
    }

    public function testComplementsFeriesKinesOnDifferentPeriod(): void
    {
        $date = CMbDT::date('next sunday');
        $user = $this->generateUser(26);
        $consultation = $this->generateConsultation($user, $date);

        $act1 = new CActeNGAP();
        $act1->object_class = $consultation->_class;
        $act1->object_id    = $consultation->_id;
        $act1->executant_id = $user->_id;
        $act1->coefficient  = 1;
        $act1->quantite     = 1;
        $act1->code         = 'AMK';
        $act1->execution    = "{$date} 10:00:00";
        if ($msg = $act1->store()) {
            $this->fail($msg);
        }

        $this->assertEquals('F', $act1->complement);

        $act2 = new CActeNGAP();
        $act2->object_class = $consultation->_class;
        $act2->object_id    = $consultation->_id;
        $act2->executant_id = $user->_id;
        $act2->coefficient  = 1;
        $act2->quantite     = 1;
        $act2->code         = 'AMK';
        $act2->execution    = "{$date} 14:00:00";
        if ($msg = $act2->store()) {
            $this->fail($msg);
        }

        $this->assertEquals('F', $act2->complement);
    }

    public function testComplementsFeriesKinesOnSamePeriod(): void
    {
        $date = CMbDT::date('next sunday');
        $user = self::generateUser(26);
        $consultation = self::generateConsultation($user);

        $act1 = new CActeNGAP();
        $act1->object_class = $consultation->_class;
        $act1->object_id    = $consultation->_id;
        $act1->executant_id = $user->_id;
        $act1->coefficient  = 1;
        $act1->quantite     = 1;
        $act1->code         = 'AMK';
        $act1->execution    = "{$date} 10:00:00";
        if ($msg = $act1->store()) {
            $this->fail($msg);
        }

        $this->assertEquals('F', $act1->complement);

        $act2 = new CActeNGAP();
        $act2->object_class = $consultation->_class;
        $act2->object_id    = $consultation->_id;
        $act2->executant_id = $user->_id;
        $act2->coefficient  = 1;
        $act2->quantite     = 1;
        $act2->code         = 'AMK';
        $act2->execution    = "{$date} 11:00:00";
        if ($msg = $act2->store()) {
            $this->fail($msg);
        }

        $this->assertEquals('', $act2->complement);
    }

    /**
     * Generate a user with the given CPAM speciality
     *
     * @param int $spec_cpam_id
     *
     * @return CMediusers
     * @throws \Exception
     */
    private function generateUser(int $spec_cpam_id): CMediusers
    {
        $user               = (new CMediusersGenerator())->generate('Médecin', $spec_cpam_id);
        $user->spec_cpam_id = $spec_cpam_id;
        if ($msg = $user->store()) {
            $this->fail($msg);
        }

        return $user;
    }

    /**
     * Generate a consultation for the given user
     *
     * @param CMediusers $user
     *
     * @return CConsultation
     * @throws \Exception
     */
    private function generateConsultation(CMediusers $user, string $date = null): CConsultation
    {
        $plage = new CPlageconsult();
        $plage->chir_id = $user->_id;
        $plage->date = $date ?? CMbDT::date();
        $plage->debut = '08:00:00';
        $plage->fin = '18:00:00';
        $plage->freq = '00:15:00';
        $plage->_immediate_plage = true;
        $plage->loadMatchingObject();

        if (!$plage->_id) {
            if ($msg = $plage->store()) {
                $this->fail($msg);
            }
        }

        $consultation = new CConsultation();
        $patient = (new CPatientGenerator())->generate();
        if (!$patient) {
            $this->fail('Error in the generation of the CPatient');
        }

        $consultation->patient_id = $patient->_id;
        $consultation->plageconsult_id = $plage->_id;

        /* Set the time of the consultation */
        $plage->loadRefsConsultations();
        foreach ($plage->loadDisponibilities() as $time => $status) {
            if ($status === 0) {
                $consultation->heure = $time;
                break;
            }
        }

        $consultation->chrono = 16;
        if ($msg = $consultation->store()) {
            $this->fail($msg);
        }

        return $consultation;
    }

    /**
     * Create a CActeNGAP with the given code, for a user of the given CPAM speciality, and set the necessary data
     *
     * @param string $code
     * @param int    $spec_cpam_executant
     *
     * @return CActeNGAP
     * @throws \Exception
     */
    private function createActe(string $code = 'C', int $spec_cpam_executant = 1, string $execution = null): CActeNGAP
    {
        if (!$execution) {
            $execution = CMbDT::dateTime();
        }

        $user = $this->generateUser($spec_cpam_executant);
        $consultation = $this->generateConsultation($user, CMbDT::date($execution));

        $act               = new CActeNGAP();
        $act->object_class = $consultation->_class;
        $act->object_id    = $consultation->_id;
        $act->executant_id = $user->_id;
        $act->coefficient  = 1;
        $act->quantite     = 1;
        $act->code         = $code;
        $act->execution    = $execution;
        if ($msg = $act->store()) {
            $this->fail($msg);
        }

        return $act;
    }
}
