<?php
/**
 * PAM - ITI-31 - Tests
 *
 * @category IHE
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @link     http://www.mediboard.org
 */

namespace Ox\Interop\Ihe\Tests;

use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Interop\Connectathon\CCnStep;
use Ox\Interop\Connectathon\Tests\Fixtures\ITI31Fixtures;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CITI31Test
 * PAM - ITI-31 - Tests
 */
class CITI31Test extends CIHETestCase
{
    /**
     * Test A01 - Admit inpatient
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA01(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A01_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A02 - Transfer the patient to a new room
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA02(): void
    {
        /** @var CAffectation $affectation */
        $affectation = parent::getReference(
            new CAffectation(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A02_AFFECTATION
        );

        self::storeObject($affectation);
    }

    /**
     * Test A03 - Discharge patient
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA03(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A03_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A04 - Admit outpatient
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA04(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A04_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A05 - Pre-admit the inpatient
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA05(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A05_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A06 - Change patient's class from outpatient (PV1-2 = O) to inpatient (PV1-2 = I)
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA06(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A06_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A07 - Change patient's class from inpatient (PV1-2 = I) to outpatient (PV1-2 = O)
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA07(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A07_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A08 - Update last name
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA08(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A08_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A11 - Cancel visit
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA11(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A11_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A12 - Cancel the previous transfer
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA12(): void
    {
        /** @var CAffectation $affectation */
        $affectation = parent::getReference(
            new CAffectation(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A02_AFFECTATION
        );

        self::deleteObject($affectation);
    }

    /**
     * Test A13 - Cancel discharge
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA13(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A13_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A14 - Pending Admit
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA14(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A14_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A15 - Pending Transfer
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA15(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A15_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A16 - Pending Discharge
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA16(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A16_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A21 - Gone on a leave of absence
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA21(): void
    {
        /** @var CAffectation $affectation */
        $affectation = parent::getReference(
            new CAffectation(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A21_AFFECTATION
        );

        self::storeObject($affectation);
    }

    /**
     * Test A22 - Returned from its leave of absence
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA22(): void
    {
        /** @var CAffectation $affectation */
        $affectation = parent::getReference(
            new CAffectation(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A22_AFFECTATION
        );

        self::storeObject($affectation);
    }

    /**
     * Test A25 - Cancel Pending Discharge
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA25(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A25_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A26 - Cancel Pending Transfer
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA26(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A26_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A27 - Cancel Pending Admit
     *
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function testA27(): void
    {
        /** @var CSejour $sejour */
        $sejour = parent::getReference(
            new CSejour(),
            ITI31Fixtures::class,
            ITI31Fixtures::REF_SCENARIO_HL7_STEP_A27_SEJOUR
        );

        self::storeObject($sejour);
    }

    /**
     * Test A38 - Cancel the pre-admission
     *
     * @param CCnStep $step Step
     *
     * @return void
     * @throws CMbException
     *
     */
    static function testA38(CCnStep $step)
    {
        // PES-PAM_Encounter_Management_Basic
        $patient = self::loadPatientPES($step, 20);
        $sejour  = self::loadAdmitPES($patient);

        $sejour->annule = 1;

        self::storeObject($sejour);
    }

    /**
     * Test A40 - Merge the two patients
     *
     * @param CCnStep $step Step
     *
     * @return void
     * @throws CMbException
     *
     */
    static function testA40(CCnStep $step)
    {
        CITI30Test::testA40();
    }

    /**
     * Test A44 - Moves the account of patient#1 to patient#2
     *
     * @param CCnStep $step Step
     *
     * @return void
     * @throws CMbException
     *
     */
    static function testA44(CCnStep $step)
    {
        // PES-PAM_Encounter_Management_ADVANCE
        $patient_1 = self::loadPatientPES($step, 20);
        $patient_2 = self::loadPatientPES($step, 30);
        $sejour    = self::loadAdmitPES($patient_2);

        $sejour->patient_id = $patient_1->_id;

        self::storeObject($sejour);
    }

    /**
     * Test A52 - Cancel the leave of absence
     *
     * @param CCnStep $step Step
     *
     * @return void
     * @throws CMbException
     *
     */
    static function testA52(CCnStep $step)
    {
        // PES-PAM_Encounter_Management_ADVANCE
        $patient     = self::loadPatientPES($step, 20);
        $sejour      = self::loadAdmitPES($patient);
        $affectation = self::loadLeaveOfAbsence($step, $sejour);

        self::deleteObject($affectation);
    }

    /**
     * Test A53 - Cancel the return from leave of absence
     *
     * @param CCnStep $step Step
     *
     * @return void
     * @throws CMbException
     *
     */
    static function testA53(CCnStep $step)
    {
        // PES-PAM_Encounter_Management_ADVANCE
        $patient     = self::loadPatientPES($step, 30);
        $sejour      = self::loadAdmitPES($patient);
        $affectation = self::loadLeaveOfAbsence($step, $sejour);

        $affectation->effectue = 0;

        self::storeObject($affectation);
    }

    /**
     * Test A54 - Change the name of the attending doctor
     *
     * @param CCnStep $step Step
     *
     * @return void
     * @throws CMbException
     *
     */
    static function testA54(CCnStep $step)
    {
        // PES-PAM_Encounter_Management_ADVANCE
        $patient = self::loadPatientPES($step, 20);
        $sejour  = self::loadAdmitPES($patient);

        do {
            $random_value = $sejour->getRandomValue("praticien_id", true);
        } while ($sejour->praticien_id == $random_value);

        $sejour->praticien_id = $random_value;

        self::storeObject($sejour);
    }

    /**
     * Test A55 - Change back the name of the attending doctor to the original one
     *
     * @param CCnStep $step Step
     *
     * @return void
     * @throws CMbException
     *
     */
    static function testA55(CCnStep $step)
    {
        // PES-PAM_Encounter_Management_ADVANCE
        $patient = self::loadPatientPES($step, 20);
        $sejour  = self::loadAdmitPES($patient);

        $sejour->praticien_id = $sejour->getValueAtDate($sejour->loadFirstLog()->date, "praticien_id");

        self::storeObject($sejour);
    }

    /**
     * Test Z99 - Update admit
     *
     * @param CCnStep $step Step
     *
     * @return void
     * @throws CMbException
     *
     */
    static function testZ99(CCnStep $step)
    {
        $patient = self::loadPatientPES($step, 10);
        $sejour  = self::loadAdmitPES($patient);

        $scenario = $step->_ref_test->_ref_scenario;

        switch ($scenario->option) {
            case 'HISTORIC_MVT' :
                if ($step->number == 30) {
                    $sejour->sortie_reelle = CMbDT::date($sejour->sortie) . " 11:00:00";
                }
                if ($step->number == 40) {
                    $sejour->entree_reelle = CMbDT::date($sejour->entree_reelle) . " 07:30:00";
                }
                break;

            default :

                break;
        }

        self::storeObject($sejour);
    }
}
