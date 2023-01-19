<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Mediboard\Patients\CPatient;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * @description Sample a single patient with random data
 */
class SimplePatientFixtures extends Fixtures implements GroupFixturesInterface
{
    public const SAMPLE_PATIENT = 'sample_patient';
    public const SAMPLE_PATIENT_BIS = 'sample_patient_bis';
    public const SAMPLE_PATIENT_TER = 'sample_patient_ter';

    public const SAMPLE_PATIENT_REFS = [
        self::SAMPLE_PATIENT,
        self::SAMPLE_PATIENT_BIS,
        self::SAMPLE_PATIENT_TER,
    ];

    public function load()
    {
        foreach (self::SAMPLE_PATIENT_REFS as $ref){
            $patient = CPatient::getSampleObject();
            $patient->naissance = CMbDT::getRandomDate('1850-01-01', CMbDT::date(), 'Y-m-d');
            $patient->cp = 17000;
            $patient->cp_naissance = 17000;
            $this->store($patient, $ref);

            $source = $patient->loadFirstBackRef('sources_identite');
            $this->store($source, $ref);
        }
    }

    public static function getGroup(): array
    {
        return ['patients'];
    }
}
