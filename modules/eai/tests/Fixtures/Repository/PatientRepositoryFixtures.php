<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tests\Fixtures\Repository;

use Exception;
use Ox\Core\CMbSecurity;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * @description Use for test algorithms used in interop to record the Patient
 */
class PatientRepositoryFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const PATIENT_WITH_INS = 'patient_locator_ins';
    /** @var string */
    public const PATIENT_WITH_IPP = 'patient_locator_ipp';
    /** @var string */
    public const PATIENT_WITH_NO_IDENTIFIERS = 'patient_locator_without_identifiers';

    /** @var string */
    public const TRAIT_FAMILY = 'TEST_FIXTURE_NAME';
    /** @var string */
    public const TRAIT_GIVEN = 'TEST_FIXTURE_GIVEN';
    /** @var string */
    public const TRAIT_BIRTH_DATE = '1990-01-01';

    /**
     * @return void
     * @throws FixturesException
     * @throws CModelObjectException
     */
    public function load(): void
    {
        // ins
        /** @var CPatient $patient */
        $patient = CPatient::getSampleObject();
        $this->store($patient, self::PATIENT_WITH_INS);
        $this->createINS($patient);

        // ipp
        /** @var CPatient $patient */
        $patient = CPatient::getSampleObject();
        $this->store($patient, self::PATIENT_WITH_IPP);
        $this->createIPP($patient);

        // trait - resource identifier
        $patient = $this->createPatientWithoutIdentifiers();
        $this->store($patient, self::PATIENT_WITH_NO_IDENTIFIERS);
    }

    private function createIPP(CPatient $patient): void
    {
        $id_sante400 = new CIdSante400();
        $id_sante400->id400 = CMbSecurity::generateUUID();
        $id_sante400->tag = 'tag_ipp';
        $id_sante400->object_class = $patient->_class;
        $id_sante400->object_id = $patient->_id;
        $this->store($id_sante400);
    }

    /**
     * @param CPatient $patient
     *
     * @return void
     * @throws Exception
     */
    private function createINS(CPatient $patient): void
    {
        $source_identity = $patient->loadRefSourceIdentite();

        $ins_nir = CMbSecurity::generateUUID();

        CPatientINSNIR::createUpdate(
            $patient->_id,
            $patient->nom,
            $patient->prenom,
            $patient->naissance,
            $ins_nir,
            'from_fixtures',
            CPatientINSNIR::OID_INS_NIR,
            null,
            $source_identity->_id
        );
    }

    /**
     * @return CPatient
     * @throws CModelObjectException
     */
    private function createPatientWithoutIdentifiers(): CPatient
    {
        /** @var CPatient $patient */
        $patient            = CPatient::getSampleObject();
        $patient->nom       = self::TRAIT_FAMILY;
        $patient->prenom    = self::TRAIT_GIVEN;
        $patient->naissance = self::TRAIT_BIRTH_DATE;

        return $patient;
    }

    /**
     * @return array
     */
    public static function getGroup(): array
    {
        return ['eai-repository'];
    }
}
