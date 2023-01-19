<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit\Export;

use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Export\CCSVPatientExport;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CCSVPatientExportTest extends OxUnitTestCase
{
    private $export;

    protected function setUp(): void
    {
        parent::setUp();

        $this->export = new CCSVPatientExport(CGroups::loadCurrent(), []);
    }

    /**
     * @dataProvider buildIdxProvider
     */
    public function testBuildIdx(string $expected_result, CPatient $patient): void
    {
        $this->assertEquals($expected_result, $this->invokePrivateMethod($this->export, 'buildIdx', $patient));
    }

    public function buildIdxProvider(): array
    {
        $patients = [];

        for ($i = 0; $i < 4; $i++) {
            $patient = CPatient::getSampleObject();
            $this->assertNull($patient->store());

            $patients[] = $patient;
        }

        $patient_no_idx     = array_pop($patients);
        $patient_idx_no_tag = array_pop($patients);
        $patient_multi_idx = array_pop($patients);

        $old_idx_patient_idx_no_tag = $this->handleOldIdx($patient_idx_no_tag);
        $this->addIdx($patient_idx_no_tag, '1234');

        $old_idx_patient_multi_idx = $this->handleOldIdx($patient_multi_idx);
        $this->addIdx($patient_multi_idx, '56789', 'tag');
        $this->addIdx($patient_multi_idx, 'ddffeeef', 'other_tag');
        $this->addIdx($patient_multi_idx, 'zertyrty');

        return [
            'no_idx' => [$this->handleOldIdx($patient_no_idx), $patient_no_idx],
            'idx_no_tag' => [
                (($old_idx_patient_idx_no_tag) ? $old_idx_patient_idx_no_tag . ',' : null) . '1234',
                $patient_idx_no_tag
            ],
            'multi_idx' => [
                (($old_idx_patient_multi_idx) ? $old_idx_patient_multi_idx . ',' : null) . '56789|tag,ddffeeef|other_tag,zertyrty',
                $patient_multi_idx
            ]
        ];
    }

    private function addIdx(CPatient $patient, string $id400, ?string $tag = null): void
    {
        $idx               = new CIdSante400();
        $idx->object_class = 'CPatient';
        $idx->object_id    = $patient->_id;
        $idx->id400        = $id400;
        $idx->tag          = $tag;

        if ($msg = $idx->store()) {
            $this->fail($msg);
        }
    }

    private function handleOldIdx(CPatient $patient): string
    {
        $export = new CCSVPatientExport(CGroups::loadCurrent(), []);

        return $this->invokePrivateMethod($export, 'buildIdx', $patient);
    }
}
