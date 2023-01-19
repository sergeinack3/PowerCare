<?php

/**
 * @package Mediboard\Ihe\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe\Tests;

use Exception;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Erp\SourceCode\CFixturesReference;
use Ox\Interop\Connectathon\CCnStep;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CIHETestCase
 * Test Case IHE
 */
class CIHETestCase
{
    /**
     * Run test
     *
     * @param string  $code Event code
     * @param CCnStep $step Step
     *
     * @return void
     * @throws CMbException
     *
     */
    public static function run(string $code, CCnStep $step): void
    {
        $receiver = $step->_ref_test->loadRefPartner()->loadReceiverHL7v2();

        if ($receiver) {
            CValue::setSessionAbs("cn_receiver_guid", $receiver->_guid);
        }

        $transaction = str_replace("-", "", $step->transaction);

        if (!$transaction) {
            throw new CMbException("CIHETestCase-no_transaction");
        }

        $class_name = "C{$transaction}Test";
        call_user_func([$class_name, "test$code"], $step);
    }

    /**
     * Load patient PDS
     *
     * @param CCnStep $step        Step
     * @param int     $step_number Step number
     *
     * @return CPatient $patient
     * @throws CMbException
     * @throws Exception
     *
     */
    public static function loadPatientPDS(CCnStep $step, int $step_number): CPatient
    {
        // PDS-PAM_Identification_Mgt_Merge : Récupération du step 10
        $test    = $step->_ref_test;
        $partner = $test->_ref_partner;

        $patient      = new CPatient();
        $where        = [];
        $where["nom"] = " = '{$partner->name}_{$test->_id}_$step_number'";
        $patient->loadObject($where);

        if (!$patient->_id) {
            throw new CMbException("CPAM-cn_test-no_patient_id");
        }

        return $patient;
    }

    /**
     * Load patient PES
     *
     * @param CCnStep $step        Step
     * @param int     $step_number Step number
     *
     * @return CPatient $patient
     * @throws CMbException
     * @throws Exception
     *
     */
    public static function loadPatientPES(CCnStep $step, int $step_number): CPatient
    {
        // PES-PAM_Encounter_Management_Basic
        $test    = $step->_ref_test;
        $partner = $test->_ref_partner;

        $name = null;
        switch ($step_number) {
            case 10:
                $name = "ONE";
                break;
            case 20:
                $name = "TWO";
                break;
            case 30:
                $name = "THREE";
                break;
            case 40:
                $name = "FOUR";
                break;
            case 50:
                if ($step->number == 80) {
                    $name = "UPDATE";
                } else {
                    $name = "FIVE";
                }
                break;
            default:
                break;
        }
        $name = "PAM$name";

        $patient      = new CPatient();
        $where        = [];
        $where["nom"] = " = '{$name}_{$partner->name}_{$test->_id}'";
        $patient->loadObject($where);

        if (!$patient->_id) {
            $patient->random();
            $patient->nom = "{$name}_{$partner->name}_{$test->_id}";

            if ($msg = $patient->store()) {
                throw new CMbException($msg);
            }
        }

        return $patient;
    }

    /**
     * Load admit PES
     *
     * @param CPatient $patient Person
     *
     * @return CSejour $sejour
     * @throws CMbException
     * @throws Exception
     *
     */
    public static function loadAdmitPES(CPatient $patient): CSejour
    {
        $sejour = new CSejour();

        $where = [];

        $where["patient_id"] = " = '$patient->_id'";
        $where["libelle"]    = " = 'Sejour ITI-31 - $patient->nom'";

        $order = "sejour_id DESC";

        $sejour->loadObject($where, $order);

        if (!$sejour->_id) {
            throw new CMbException("La séjour du patient '$patient->nom' n'a pas été retrouvé");
        }

        return $sejour;
    }

    /**
     * Load leave of absence
     *
     * @param CCnStep $step   Step
     * @param CSejour $sejour Admit
     *
     * @return CAffectation $affectation
     * @throws CMbException
     * @throws Exception
     *
     */
    public static function loadLeaveOfAbsence(CCnStep $step, CSejour $sejour): CAffectation
    {
        $service_externe = CService::loadServiceExterne($step->_ref_test->group_id);

        if (!$service_externe->_id) {
            throw new CMbException("Aucun service externe de configuré");
        }

        $affectation             = new CAffectation();
        $affectation->service_id = $service_externe->_id;
        $affectation->sejour_id  = $sejour->_id;
        $affectation->entree     = $sejour->entree;
        $affectation->loadMatchingObject();

        if (!$affectation->_id) {
            throw new CMbException("Aucune affectation retrouvée");
        }

        return $affectation;
    }

    /**
     * Store object
     *
     * @param CMbObject $object Object
     *
     * @return null|string null if successful otherwise returns and error message
     * @throws CMbException
     * @throws Exception
     *
     */
    public static function storeObject(CMbObject $object): ?string
    {
        if ($msg = $object->store()) {
            $object->repair();

            if ($msg = $object->store()) {
                throw new CMbException($msg);
            }
        }

        return null;
    }

    /**
     * Delete object
     *
     * @param CMbObject $object Object
     *
     * @return null|string null if successful otherwise returns and error message
     * @throws CMbException
     * @throws Exception
     *
     */
    public static function deleteObject(CMbObject $object): ?string
    {
        if ($msg = $object->delete()) {
            throw new CMbException($msg);
        }

        return null;
    }

    /**
     * @throws CMbException
     */
    public static function getReference(string $fixtureClass, string $objectClass, string $tag): ?CStoredObject
    {
        $ref                 = new CFixturesReference();
        $ref->fixtures_class = $fixtureClass;
        $ref->object_class   = $objectClass;
        $ref->tag            = $tag;
        $ref->loadMatchingObjectEsc();

        return $ref->_id ? $ref->loadTarget() : null;
    }
}
