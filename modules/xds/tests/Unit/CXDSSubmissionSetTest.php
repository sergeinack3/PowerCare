<?php

/**
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Tests\Unit;

use Ox\Interop\Xds\Structure\SubmissionSet\CXDSSubmissionSet;
use Ox\Tests\OxUnitTestCase;
use Throwable;


class CXDSSubmissionSetTest extends OxUnitTestCase
{

    public function providerPatientId(): array
    {
        return [
            "Patient Id Complete" => [
                'patient_id' => [
                    'CX.1' => '194052203228988',
                    'CX.4' => '&1.2.250.1.213.1.4.10&ISO',
                    'CX.5' => 'NH',
                ],
                'expected'      => true
            ],
            "Patient Id Not Complete CX.1" => [
                'patient_id' => [
                    'CX.4' => '&1.2.250.1.213.1.4.10&ISO',
                    'CX.5' => 'NH',
                ],
                'expected'      => false
            ],
            "Patient Id Not Complete CX.4" => [
                'patient_id' => [
                    'CX.1' => '194052203228988',
                    'CX.5' => 'NH',
                ],
                'expected'      => false
            ],
            "Patient Id Not Complete CX.5" => [
                'patient_id' => [
                    'CX.1' => '194052203228988',
                    'CX.4' => '&1.2.250.1.213.1.4.10&ISO',
                ],
                'expected'      => false
            ],
        ];
    }

    /**
     * @dataProvider providerPatientId
     *
     * @param array $patient_id
     * @param string $expected
     *
     */
    public function testCheckPatientIdCdaCompleted(array $patient_id, bool $expected) {
        $submission_set = new CXDSSubmissionSet(CXDSSubmissionSet::TYPE_XDS);

        $this->assertEquals(
            $expected ? true : false,
            $this->invokePrivateMethod($submission_set, 'checkPatientIdCdaCompleted', $patient_id)
        );
    }
}
