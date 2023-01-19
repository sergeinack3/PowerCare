<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;
use Ox\Mediboard\Patients\CSourceIdentite;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Create patient with INS Temporaire
 */
class PatientINSTemporaireFixtures extends Fixtures implements GroupFixturesInterface
{
    public const PATIENT_INS_TEMPORAIRE = 'ins_temporaire';
    public const PATIENT_INS_VALIDE     = "ins_valide";

    public const SAMPLE_PATIENT_REFS = [
        self::PATIENT_INS_TEMPORAIRE,
        self::PATIENT_INS_VALIDE,
    ];

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    public function load(): void
    {
        $create = 1;
        foreach (self::SAMPLE_PATIENT_REFS as $ref) {
            $patient               = CPatient::getSampleObject();
            $this->store($patient, $ref);

            /** @var CSourceIdentite $source */
            $source = $patient->loadFirstBackRef('sources_identite');
            $this->store($source, $ref);

            $this->createINSNIR($patient, $source->_id, $create, $ref);
            if ($create) {
                $source = new CSourceIdentite();
                $source->patient_id = $patient->_id;
                $source->active = 1;
                $source->mode_obtention = "insi";
                $source->_oid = CPatientINSNIR::OID_INS_NIR_TEST;
                $source->_ins = '185108606606616';
                $this->store($source);
                $create = 0;
            }
        }
    }

    /**
     * @param CPatient $patient
     * @param int      $source_id
     * @param int      $tmp
     * @param string   $ref
     *
     * @return void
     * @throws FixturesException
     */
    public function createINSNIR(CPatient $patient, int $source_id, int $tmp, string $ref): void
    {
        $INSNIR                     = new CPatientINSNIR();
        $INSNIR->patient_id         = $patient->_id;
        $INSNIR->ins_temporaire     = $tmp;
        $INSNIR->source_identite_id = $source_id;
        $INSNIR->provider           = "INSi";
        $INSNIR->oid                = "1234567";
        $INSNIR->ins_nir            = "12345678";
        $this->store($INSNIR, $ref);
    }

    public static function getGroup(): array
    {
        return ['ins_temporaire'];
    }
}
