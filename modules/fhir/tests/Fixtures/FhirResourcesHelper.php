<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Tests\Fixtures;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;
use Ox\Core\CModelObjectException;
use Ox\Core\FieldSpecs\CPhoneSpec;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CConsultationCategorie;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\CSlot;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;
use Ox\Mediboard\Patients\Constants\CConstantReleve;
use Ox\Mediboard\Patients\Constants\CConstantSpec;
use Ox\Mediboard\Patients\Constants\CValueInt;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class FhirResourcesHelper
 */
class FhirResourcesHelper
{
    public static function getSamplePatient(): CPatient
    {
        $patient = CPatient::getSampleObject();

        return $patient;
    }

    /**
     * @throws CModelObjectException
     * @throws Exception
     */
    public static function getSampleFhirMedecin(): CMedecin
    {
        /** @var CMedecin $medecin */
        $medecin              = CMedecin::getSampleObject();
        $medecin->tel         = self::generateRandomPhoneNumber();
        $medecin->tel_autre   = self::generateRandomPhoneNumber();
        $medecin->fax         = self::generateRandomPhoneNumber();
        $medecin->portable    = self::generateRandomPhoneNumber();
        $medecin->email       = 'mail@mail.com';
        $medecin->adresse     = '1 rue du fhir';
        $medecin->ville       = 'FHIRLAND';
        $medecin->cp          = '01010';
        $medecin->rpps        = CMbString::createLuhn(random_int(10000000000, 99999999999));
        $medecin->disciplines = 'SM05 : Chirurgie générale';

        return $medecin;
    }

    /**
     * @throws CModelObjectException
     */
    public static function getSampleFhirGroups(): CGroups
    {
        /** @var CGroups $groups */
        $groups = CGroups::getSampleObject();

        return $groups;
    }

    /**
     * @throws CModelObjectException
     * @throws Exception
     */
    public static function getSampleFhirFunctions(CGroups $group): CFunctions
    {
        /** @var CFunctions $function */
        $function           = CFunctions::getSampleObject();
        $function->group_id = $group->_id;

        return $function;
    }

    /**
     * @throws Exception
     */
    public static function generateRandomPhoneNumber(): string
    {
        $phone_min_length = CAppUI::conf("system phone_min_length");

        return CMbFieldSpec::randomString(range(0, 9), $phone_min_length);
    }

    /**
     * @throws CModelObjectException
     */
    public static function getSampleFhirPlageconsult(): CPlageconsult
    {
        $deb = CMbDT::time();
        $fin = CMbDT::time('+15 MINUTES', $deb);

        /** @var CPlageconsult $schedule */
        $schedule        = CPlageconsult::getSampleObject();
        $schedule->debut = $deb;
        $schedule->fin   = $fin;

        return $schedule;
    }

    /**
     * @throws CModelObjectException
     */
    public static function getSampleFhirConsultationCategorie(): CConsultationCategorie
    {
        /** @var CConsultationCategorie $consult_category */
        $consult_category = CConsultationCategorie::getSampleObject(null, false);

        return $consult_category;
    }

    /**
     * @throws CModelObjectException
     */
    public static function getSampleFhirConsultation(): CConsultation
    {
        /** @var CConsultation $appointment */
        $appointment            = CConsultation::getSampleObject();
        $appointment->_datetime = CMbDT::dateTime();

        return $appointment;
    }

    /**
     * @throws CModelObjectException
     */
    public static function getSampleFhirSlot(): CSlot
    {
        $deb = CMbDT::dateTime();
        $fin = CMbDT::dateTime('+15 MINUTES', $deb);

        /** @var CSlot $slot */
        $slot             = CSlot::getSampleObject();
        $slot->start      = $deb;
        $slot->end        = $fin;
        $slot->overbooked = 1;

        return $slot;
    }

    /**
     * @throws CModelObjectException
     */
    public static function getSampleFhirConstantReleve(): CConstantReleve
    {
        $releve = CConstantReleve::getSampleObject();

        return $releve;
    }

    /**
     * @throws CModelObjectException
     */
    public static function getSampleFhirValueInt(): CValueInt
    {
        $constant          = CValueInt::getSampleObject();
        $constant->spec_id = CConstantSpec::$XML_SPECS;

        return $constant;
    }

    /**
     * @return CSejour
     * @throws CModelObjectException
     */
    public static function getSampleFhirSejour(): CSejour
    {
        /** @var CSejour $sejour */
        $sejour = CSejour::getSampleObject();

        return $sejour;
    }
}
