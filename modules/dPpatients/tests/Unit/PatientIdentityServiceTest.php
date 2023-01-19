<?php

/**
 * @package Mediboard\Patient\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbString;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientLink;
use Ox\Mediboard\Patients\CPatientState;
use Ox\Mediboard\Patients\PatientIdentityService;
use Ox\Mediboard\Patients\Tests\Fixtures\SimplePatientFixtures;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

class PatientIdentityServiceTest extends OxUnitTestCase
{
    /**
     * @dataProvider listPatientsFromStateProvider
     * @throws CMbException
     */
    public function testListPatientsFromState(array $params, string $expected): void
    {
        $service = new PatientIdentityService();

        [$date_min, $date_max, $state, $page] = $params;

        $actual = $service->listPatientsFromState($state, $date_min, $date_max, $page);

        $this->assertEquals(count(CPatientState::LIST_STATE), count($actual[1]));

        foreach ($actual[0] as $_patient) {
            /** @var $_patient CPatient */
            if ($state !== CPatientState::STATE_DPOT) {
                $this->assertEquals(CMBString::upper($expected), $_patient->status);
            }
        }
    }

    /**
     * @param array  $params
     * @param string $expected
     *
     * @throws CMbException
     * @dataProvider invalidListPatientsFromStateProvider
     */
    public function testListPatientsFromStateThrowsException(array $params, string $expected): void
    {
        $this->expectExceptionMessage($expected);

        [$date_min, $date_max, $state, $page] = $params;

        $service = new PatientIdentityService();
        $service->listPatientsFromState($state, $date_min, $date_max, $page);
    }

    /**
     * @throws ReflectionException
     * @throws TestsException
     * @throws Exception
     */
    public function testLoadDuplicatePatients(): void
    {
        $patient1 = $this->getObjectFromFixturesReference(CPatient::class, SimplePatientFixtures::SAMPLE_PATIENT);

        $patient2 = $this->getObjectFromFixturesReference(CPatient::class, SimplePatientFixtures::SAMPLE_PATIENT_BIS);

        $link = new CPatientLink();

        $link->patient_id1 = $patient1->_id;
        $link->patient_id2 = $patient2->_id;
        $link->type        = CPatientState::STATE_DPOT;

        $link->store();

        $service  = new PatientIdentityService();
        $leftjoin = $this->invokePrivateMethod(
            $service,
            "prepareLeftJoinFromConditions",
            CPatientState::STATE_DPOT
        );
        $where    = $this->invokePrivateMethod(
            $service,
            "prepareWhereFromConditions",
            CPatientState::STATE_DPOT,
        );
        $actual   = $this->invokePrivateMethod($service, "loadDuplicatePatients", $where, $leftjoin, 0);

        $this->assertTrue(count($actual) <= PatientIdentityService::PAGE_LIMIT);

        if (count($actual)) {
            foreach ($actual as $_patient) {
                $this->assertNotEmpty($_patient->_ref_patient_links);
            }
        }
    }

    /**
     * @param array $params
     * @param array $expected
     *
     * @throws TestsException
     * @throws ReflectionException
     * @dataProvider whereParamsProvider
     * @config       dPpatients CPatient function_distinct 2
     */
    public function testPrepareWhereFromConditions(array $params, array $expected): void
    {
        $service = new PatientIdentityService();
        [$date_min, $date_max, $state] = $params;
        $actual = $this->invokePrivateMethod($service, "prepareWhereFromConditions", $state, $date_min, $date_max);
        foreach ($expected as $key_expected) {
            $this->assertArrayHasKey($key_expected, $actual);
        }
    }

    /**
     * @param array  $params
     * @param string $expected
     *
     * @throws ReflectionException
     * @throws TestsException
     * @dataProvider leftJoinParamsProvider
     */
    public function testPrepareLeftJoinFromConditions(array $params, string $expected): void
    {
        $service = new PatientIdentityService();
        [$state, $date_min, $date_max] = $params;
        $actual = $this->invokePrivateMethod($service, "prepareLeftJoinFromConditions", $state, $date_min, $date_max);
        $this->assertEquals($expected, $actual["sejour"]);
    }

    public function listPatientsFromStateProvider(): array
    {
        return [
            CPatientState::STATE_VALI => [
                [
                    CMbDT::date("-1 YEAR"),
                    CMbDT::date(),
                    CPatientState::STATE_VALI,
                    0,
                ],
                CPatientState::STATE_VALI,
            ],
            CPatientState::STATE_DPOT => [
                [
                    CMbDT::date("-1 YEAR"),
                    CMbDT::date(),
                    CPatientState::STATE_DPOT,
                    1,
                ],
                CPatientState::STATE_PROV,
            ],
        ];
    }

    public function invalidListPatientsFromStateProvider(): array
    {
        return [
            "date min > date max" => [
                [
                    CMbDT::date("+1 DAY"),
                    CMbDT::date(),
                    CPatientState::STATE_VALI,
                    0,
                ],
                "common-error-Date min must be lower than date max",
            ],
            "invalid page number" => [
                [
                    CMbDT::date(),
                    CMbDT::date("+1 DAY"),
                    CPatientState::STATE_VALI,
                    -1,
                ],
                "page < 0",
            ],
            "invalid status"      => [
                [
                    CMbDT::date(),
                    CMbDT::date("+1 DAY"),
                    "lorem",
                    1,
                ],
                "invalid status",
            ],
        ];
    }

    public function whereParamsProvider(): array
    {
        $patient_expect = CAppUI::isCabinet() ? "patients.function_id" : "patients.group_id";

        return [
            "date min + date max + vali" => [
                [
                    CMbDT::date(),
                    CMbDT::date("+1 DAY"),
                    CPatientState::STATE_VALI,
                ],
                ["0", "1", $patient_expect, "status"],
            ],
            "date min + prov"            => [
                [
                    CMbDT::date(),
                    CMbDT::date("+1 DAY"),
                    CPatientState::STATE_PROV,
                ],
                ["0", $patient_expect, "status"],
            ],
            "date min + date max + dpot" => [
                [
                    CMbDT::date(),
                    CMbDT::date("+1 DAY"),
                    CPatientState::STATE_DPOT,
                ],
                ["0", "1", $patient_expect],
            ],
            "date min + date max + cach" => [
                [
                    CMbDT::date(),
                    CMbDT::date("+1 DAY"),
                    CPatientState::STATE_CACH,
                ],
                ["0", "1", $patient_expect, "status", "vip"],
            ],
        ];
    }

    public function leftJoinParamsProvider(): array
    {
        return [
            "date min + date max + vali" => [
                [
                    CPatientState::STATE_VALI,
                    CMbDT::date(),
                    CMbDT::date("+1 DAY"),
                ],
                "patients.patient_id = sejour.patient_id",
            ],
            "date max + dpot"            => [
                [
                    CPatientState::STATE_DPOT,
                    null,
                    CMbDT::date("+1 DAY"),
                ],
                "sejour.patient_id = patient_link.patient_id1",
            ],
            "dpot"                       => [
                [
                    CPatientState::STATE_DPOT,
                    null,
                    null,
                ],
                "sejour.patient_id = patient_link.patient_id1",
            ],
        ];
    }
}
