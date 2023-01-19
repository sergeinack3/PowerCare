<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Tests\Fixtures\SimplePatientFixtures;
use Ox\Tests\OxUnitTestCase;

class CConstantesMedicalesTest extends OxUnitTestCase
{
    /**
     * @dataProvider getForProvider
     */
    public function testGetFor(CPatient $patient, string $field, string $order, string $expected): void
    {
        [$constante, $list_datetimes, $list_contexts] =
            CConstantesMedicales::getFor($patient, null, null, null, null, null, $order);

        $this->assertEquals($expected, $constante->$field);
    }

    public function getForProvider(): array
    {
        $patient = $this->getObjectFromFixturesReference(CPatient::class, SimplePatientFixtures::SAMPLE_PATIENT);
        $user    = $this->getObjectFromFixturesReference(CMediusers::class, UsersFixtures::REF_USER_CHIR);

        $constante                = new CConstantesMedicales();
        $constante->patient_id    = $patient->_id;
        $constante->context_class = $patient->_class;
        $constante->context_id    = $patient->_id;
        $constante->user_id       = $user->_id;
        $constante->datetime      = CMbDT::dateTime('-1 minute');
        $constante->poids         = 50;
        $this->storeOrFailed($constante);

        $constante->_id      = '';
        $constante->datetime = CMbDT::dateTime();
        $constante->poids    = 60;
        $constante->_poids_g = '';
        $this->storeOrFailed($constante);

        return [
            'asc' => [
                $patient,
                'poids',
                'ASC',
                '50',
            ],

            'desc' => [
                $patient,
                'poids',
                'DESC',
                '60',
            ],
        ];
    }
}
