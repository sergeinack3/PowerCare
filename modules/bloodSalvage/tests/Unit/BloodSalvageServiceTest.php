<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\BloodSalvage\tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Mediboard\BloodSalvage\CBloodSalvage;
use Ox\Mediboard\BloodSalvage\Services\BloodSalvageService;
use Ox\Mediboard\BloodSalvage\Tests\Fixtures\BloodSalvageFixtures;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Personnel\CAffectationPersonnel;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Tests\OxUnitTestCase;

/**
 * @group schedules
 */
class BloodSalvageServiceTest extends OxUnitTestCase
{
    /**
     * @throws Exception
     * @dataProvider loadAffectedProvider
     */
    public function testLoadAffected(
        CBloodSalvage $blood_salvage,
        array $list_nurse_sspi,
        array $tabAffected,
        array $timingAffect,
        array $expected
    ): void {
        BloodSalvageService::loadAffected($blood_salvage->_id, $list_nurse_sspi, $tabAffected, $timingAffect);
        $this->assertEquals($expected, $timingAffect);
    }

    /**
     * @throws Exception
     */
    public function loadAffectedProvider(): array
    {
        $tabAffected     = [];
        $timingAffected  = [];
        $list_nurse_sspi = CPersonnel::loadListPers("reveil");
        $affectations    = [
            $this->getAffectationPersonnel(BloodSalvageFixtures::TAG_AFFECTATION_PERSONNEL3),
            $this->getAffectationPersonnel(BloodSalvageFixtures::TAG_AFFECTATION_PERSONNEL4),
        ];

        $timings = [];
        foreach ($affectations as $_affectation) {
            $timings[$_affectation->_id] = [
                '_debut' => [],
                '_fin'   => [],
            ];
            for ($i = -10; $i < 10; $i++) {
                $timings[$_affectation->_id]['_debut'][] = CMbDT::time("$i minutes", $_affectation->_debut);
                $timings[$_affectation->_id]['_fin'][]   = CMbDT::time("$i minutes", $_affectation->_fin);
            }
        }

        return [
            'empty'          => [
                $this->getBloodSalvage(BloodSalvageFixtures::TAG_BLOOD_SALVAGE2),
                $list_nurse_sspi,
                $tabAffected,
                $timingAffected,
                [
                    $this->getAffectationPersonnel(BloodSalvageFixtures::TAG_AFFECTATION_PERSONNEL1)->_id => [
                        "_debut" => [],
                        "_fin"   => [],
                    ],
                    $this->getAffectationPersonnel(BloodSalvageFixtures::TAG_AFFECTATION_PERSONNEL2)->_id => [
                        "_debut" => [],
                        "_fin"   => [],
                    ],
                ],
            ],
            'with_debut_fin' => [
                $this->getBloodSalvage(BloodSalvageFixtures::TAG_BLOOD_SALVAGE1),
                $list_nurse_sspi,
                $tabAffected,
                $timingAffected,
                $timings,
            ],
        ];
    }

    /**
     * @throws Exception
     * @dataProvider providerFillData
     */
    public function testFillData(array $where, array $ljoin, array $serie, array $dates, int $expected): void
    {
        BloodSalvageService::fillData($where, $ljoin, $serie, $dates);

        $this->assertFalse(isset($where['patients.naissance']));
        $this->assertEquals($expected, $serie['data'][0][1]);
    }

    /**
     * @throws Exception
     */
    public function providerFillData(): array
    {
        $ds                           = (new CBloodSalvage())->getDS();
        $serie                        = ['data' => [], 'label' => '0 - 19 ans'];
        $where1                       = [
            "patients.patient_id" => $ds->prepareIn([$this->getPatient()->_id]),
            "patients.naissance"  => $ds->prepareBetween(
                CMbDT::date('-19 YEARS'),
                CMbDT::date()
            ),
        ];
        $where2                       = $where1;
        $where2["patients.naissance"] = $ds->prepareBetween(
            CMbDT::date('-20 YEARS'),
            CMbDT::date('-22 YEARS')
        );


        $ljoin = [
            'operations'          => 'blood_salvage.operation_id = operations.operation_id',
            'consultation_anesth' => 'operations.operation_id = consultation_anesth.operation_id',
            'sejour'              => 'operations.sejour_id = sejour.sejour_id',
            'patients'            => 'sejour.patient_id = patients.patient_id',
        ];

        /** @var COperation $op */
        $op     = $this->getOperation();
        $dates1 = [
            [
                'start' => CMbDT::transform(null, $op->date, "%Y-%m-01 00:00:00"),
                'end'   => CMbDT::transform(null, $op->date, "%Y-%m-31 23:59:59"),
            ],
        ];

        $dates2 = [
            [
                'start' => CMbDT::dateTime('-1 YEAR', $dates1[0]['start']),
                'end'   => CMbDT::dateTime('-1 YEAR', $dates1[0]['end']),
            ],
        ];

        return [
            'ok'                     => [
                $where1,
                $ljoin,
                $serie,
                $dates1,
                2,
            ],
            'not_in_operations_date' => [
                $where1,
                $ljoin,
                $serie,
                $dates2,
                0,
            ],
            'not_in_patient_age'     => [
                $where2,
                $ljoin,
                $serie,
                $dates1,
                0,
            ],
        ];
    }

    /**
     * @dataProvider providerFillData
     * @throws Exception
     */
    public function testComputeMeanValue(array $where, array $ljoin, array $serie, array $dates, int $expected): void
    {
        BloodSalvageService::computeMeanValue($where, $ljoin, $serie, $dates, 'wash_volume');

        $this->assertEquals($expected, $serie['data'][0][1]);
    }

    /**
     * @throws Exception
     */
    protected function getBloodSalvage(string $tag): CStoredObject
    {
        return $this->getObjectFromFixturesReference(CBloodSalvage::class, $tag);
    }

    /**
     * @throws Exception
     */
    protected function getAffectationPersonnel(string $tag): CStoredObject
    {
        return $this->getObjectFromFixturesReference(CAffectationPersonnel::class, $tag);
    }

    /**
     * @throws Exception
     */
    protected function getOperation(): CStoredObject
    {
        return $this->getObjectFromFixturesReference(COperation::class, BloodSalvageFixtures::TAG_OPERATION);
    }

    /**
     * @throws Exception
     */
    protected function getPatient(): CStoredObject
    {
        return $this->getObjectFromFixturesReference(CPatient::class, BloodSalvageFixtures::TAG_PATIENT);
    }
}
