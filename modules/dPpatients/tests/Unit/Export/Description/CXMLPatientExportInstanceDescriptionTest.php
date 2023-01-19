<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit\Export\Description;

use Ox\Core\CMbException;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Export\Description\CXMLPatientExportFieldDescription;
use Ox\Mediboard\Patients\Export\Description\CXMLPatientExportInstanceDescription;
use Ox\Tests\OxUnitTestCase;

class CXMLPatientExportInstanceDescriptionTest extends OxUnitTestCase
{
    private $instance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new CXMLPatientExportInstanceDescription(new CPatient());
    }

    public function testAdd(): void
    {
        $this->assertCount(0, $this->instance->getItems());

        $this->instance->add('nom');

        $this->assertCount(1, $this->instance->getItems());
        $this->assertArrayHasKey('nom', $this->instance->getItems());
    }

    public function testAddException(): void
    {
        $this->expectExceptionObject(
            new CMbException(
                'CXMLPatientExportFieldDescription-Error-Field must not be null and must be a field of the object',
                'test',
                'CPatient'
             )
        );

        $this->instance->add('test');
    }

    public function testOffsetSetOk(): void
    {
        $this->assertArrayNotHasKey('nom', $this->instance->getItems());

        $this->instance['nom'] = new CXMLPatientExportFieldDescription(new CPatient(), 'nom');

        $this->assertArrayHasKey('nom', $this->instance->getItems());
    }

    public function testOffsetSetInvalidObject(): void
    {
        $this->expectExceptionObject(
            new CMbException(
                'CXMLPatientExportInstanceDescription-Error-Value is not a CXMLPatientExportFieldDescription'
            )
        );

        $this->instance['test'] = 'test';
    }

    public function testBuildPath(): void
    {
        $this->assertEquals('//object[@class="CPatient"]', $this->invokePrivateMethod($this->instance, 'buildPath'));
    }
}
