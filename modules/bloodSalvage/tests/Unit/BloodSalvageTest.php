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
use Ox\Mediboard\BloodSalvage\CCellSaver;
use Ox\Mediboard\BloodSalvage\CTypeEi;
use Ox\Mediboard\BloodSalvage\Tests\Fixtures\BloodSalvageFixtures;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Tests\OxUnitTestCase;

/**
 * @group schedules
 */
class BloodSalvageTest extends OxUnitTestCase
{
    /**
     * @throws Exception
     */
    public function testLoadRefsFwd(): void
    {
        /** @var CBloodSalvage $bl */
        $bl = $this->getBloodSalvage(BloodSalvageFixtures::TAG_BLOOD_SALVAGE1);
        $bl->loadRefsFwd();

        $this->assertEquals($this->getOperation()->_id, $bl->_ref_operation->_id);
        $this->assertEquals($this->getPatient()->_id, $bl->_ref_patient->_id);
        $this->assertEquals($this->getCellSaver()->_id, $bl->_ref_cell_saver->_id);
        $this->assertEquals($this->getTypeEi()->_id, $bl->_ref_incident_type->_id);
    }

    /**
     * @throws Exception
     * @dataProvider poviderUpdatePlainField
     */
    public function testUpdatePlainFields(
        CBloodSalvage $bl,
        string $value,
        string $prop,
        string $expected,
        bool $test_sub = false
    ): void {
        $_sub_prop      = "_$prop";
        $bl->$_sub_prop = $value;
        $bl->updatePlainFields();
        $test_sub ?
            $this->assertGreaterThanOrEqual($expected, $bl->$_sub_prop) :
            $this->assertEquals($expected, $bl->$prop);
    }

    /**
     * @throws Exception
     */
    public function poviderUpdatePlainField(): array
    {
        $date_expected = $this->getOperation()->date;
        $time_expected = CMbDT::time();

        /** @var CBloodSalvage $bl */
        $bl = $this->getBloodSalvage(BloodSalvageFixtures::TAG_BLOOD_SALVAGE1);

        return [
            'recuperation_start_ok'      => [
                $bl,
                $bl->_recuperation_start = '10:00:00',
                'recuperation_start',
                $date_expected . ' 10:00:00',
            ],
            'recuperation_start_current' => [
                $bl,
                'current',
                'recuperation_start',
                $time_expected,
                true
            ],
            'recuperation_start_empty'   => [
                $bl,
                '',
                'recuperation_start',
                '',
            ],
            'recuperation_end_ok'        => [
                $bl,
                '10:00:00',
                'recuperation_end',
                $date_expected . ' 10:00:00',
            ],
            'recuperation_end_current' => [
                $bl,
                'current',
                'recuperation_end',
                $time_expected,
                true
            ],
            'recuperation_end_empty'     => [
                $bl,
                '',
                'recuperation_end',
                '',
            ],
            'transfusion_start_ok'       => [
                $bl,
                '10:00:00',
                'transfusion_start',
                $date_expected . ' 10:00:00',
            ],
            'transfusion_start_current' => [
                $bl,
                'current',
                'transfusion_start',
                $time_expected,
                true
            ],
            'transfusion_start_empty'    => [
                $bl,
                '',
                'transfusion_start',
                '',
            ],
            'transfusion_end_ok'         => [
                $bl,
                '10:00:00',
                'transfusion_end',
                $date_expected . ' 10:00:00',
            ],
            'transfusion_end_current' => [
                $bl,
                'current',
                'transfusion_end',
                $time_expected,
                true
            ],
            'transfusion_end_empty'      => [
                $bl,
                '',
                'transfusion_end',
                '',
            ],
        ];
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

    /**
     * @throws Exception
     */
    protected function getCellSaver(): CStoredObject
    {
        return $this->getObjectFromFixturesReference(CCellSaver::class, BloodSalvageFixtures::TAG_CELL_SAVER);
    }

    /**
     * @throws Exception
     */
    protected function getTypeEi(): CStoredObject
    {
        return $this->getObjectFromFixturesReference(CTypeEi::class, BloodSalvageFixtures::TAG_TYPE_EI);
    }
}
