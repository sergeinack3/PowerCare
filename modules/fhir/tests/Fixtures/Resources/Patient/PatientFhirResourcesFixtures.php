<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Tests\Fixtures\Resources\Patient;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CModelObjectException;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Fhir\Tests\Fixtures\FhirResourcesHelper;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CConsultationCategorie;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\CSlot;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Class FhirApiFixtures
 * @package Ox\Interop\Fhir\Tests\Fixtures\Resources\Patient
 */
class PatientFhirResourcesFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const  REF_PATIENT_FHIR_RESOURCE = 'fhir_patient_resource';

    public const OBJECT_RESOURCE_COUPLE = [
        [
            'fhir_resource' => CFHIRResourcePatient::class,
            'object_class'  => 'CPatient',
            'fixture_ref'   => self::REF_PATIENT_FHIR_RESOURCE,
        ],
    ];

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     * @throws Exception
     */
    public function load(): void
    {
        $medecin      = $this->generateGeneralPractitioner();
        $organization = $this->generateGroupsOrganization();
        $patient      = $this->generatePatient($medecin, $organization);
        $this->generatePatientCorrespondant($patient);
    }

    /**
     * @return array
     */
    public static function getGroup(): array
    {
        return ['fhir_resources'];
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     * @throws Exception
     */
    private function generatePatient(CMedecin $medecin, CGroups $organization): CPatient
    {
        /** @var CPatient $patient */
        $patient                    = CPatient::getSampleObject();
        $patient->nom               = 'fhir_patient_fixtures';
        $patient->nom_jeune_fille   = 'fhir_patient_fixtures';
        $patient->prenom            = 'fhir_patient_prenom';
        $patient->prenom_usuel      = 'fhir_patient_usual';
        $patient->tel               = FhirResourcesHelper::generateRandomPhoneNumber();
        $patient->tel2              = FhirResourcesHelper::generateRandomPhoneNumber();
        $patient->tel_autre         = FhirResourcesHelper::generateRandomPhoneNumber();
        $patient->tel_autre_mobile  = FhirResourcesHelper::generateRandomPhoneNumber();
        $patient->tel_pro           = FhirResourcesHelper::generateRandomPhoneNumber();
        $patient->tel_refus         = '0';
        $patient->email             = 'mail@mail.com';
        $patient->email_refus       = '0';
        $patient->naissance         = CMbDT::date('2000-01-01');
        $patient->deces             = CMbDT::dateTime('-15 DAY');
        $patient->adresse           = '1 rue du FHIR';
        $patient->cp                = '01010';
        $patient->ville             = 'FHIRLAND';
        $patient->situation_famille = 'S';
        $patient->medecin_traitant  = $medecin->_id;
        $patient->group_id          = $organization->_id;
        $this->store($patient, self::REF_PATIENT_FHIR_RESOURCE);

        return $patient;
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     * @throws Exception
     */
    private function generatePatientCorrespondant(CPatient $patient): CCorrespondantPatient
    {
        /** @var CCorrespondantPatient $correspondant */
        $correspondant                  = CCorrespondantPatient::getSampleObject();
        $correspondant->patient_id      = $patient->_id;
        $correspondant->relation        = 'parent_proche';
        $correspondant->nom_jeune_fille = 'fhir_patient_correspondant';
        $correspondant->tel             = FhirResourcesHelper::generateRandomPhoneNumber();
        $correspondant->tel_autre       = FhirResourcesHelper::generateRandomPhoneNumber();
        $correspondant->mob             = FhirResourcesHelper::generateRandomPhoneNumber();
        $correspondant->fax             = FhirResourcesHelper::generateRandomPhoneNumber();
        $correspondant->email           = 'mail@mail.com';
        $correspondant->adresse         = '1 rue du fhir';
        $correspondant->ville           = 'FHIRLAND';
        $correspondant->cp              = '01010';
        $this->store($correspondant);

        return $correspondant;
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generateGeneralPractitioner(): CMedecin
    {
        /** @var CMedecin $medecin */
        $medecin = CMedecin::getSampleObject();
        $this->store($medecin);

        return $medecin;
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generateGroupsOrganization(): CGroups
    {
        /** @var CGroups $organization */
        $organization = CGroups::getSampleObject();
        $this->store($organization);

        return $organization;
    }
}
