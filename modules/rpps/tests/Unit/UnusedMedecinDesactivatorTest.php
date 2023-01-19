<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Tests\Unit;

use Ox\Core\CMbArray;
use Ox\Core\CMbBackSpec;
use Ox\Core\CMbException;
use Ox\Import\Rpps\UnusedMedecinDesactivator;
use Ox\Mediboard\Files\CContextDoc;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Tests\OxUnitTestCase;
use ReflectionClass;

/**
 * @group schedules
 */
class UnusedMedecinDesactivatorTest extends OxUnitTestCase
{
    /**
     * @dataProvider loadIdsInUseWithAbstractObjectProvider
     */
    public function testLoadIdsInUseWithAbstractObject(string $short_class_name, string $field_name): void
    {
        $desactivator = new UnusedMedecinDesactivator();
        $this->assertEmpty(
            $this->invokePrivateMethod($desactivator, 'loadIdsInUse', $short_class_name, $field_name)
        );
    }

    public function testLoadIdsInUseWithNormalField(): void
    {
        /** @var CMedecin $medecin */
        $medecin  = CMedecin::getSampleObject();
        $this->storeOrFailed($medecin);

        for ($i = 0; $i < 5; $i++) {
            /** @var CPatient $patient */
            $patient = CPatient::getSampleObject();
            $patient->medecin_traitant = $medecin->_id;
            $this->storeOrFailed($patient);
        }

        $desactivator = new UnusedMedecinDesactivator();
        $medecin_ids  = $this->invokePrivateMethod($desactivator, 'loadIdsInUse', 'CPatient', 'medecin_traitant');
        $this->assertContains($medecin->_id, $medecin_ids);
    }

    public function testLoadIdsInUseWithMetaField(): void
    {
        /** @var CMedecin $medecin */
        $medecin = CMedecin::getSampleObject();
        $this->storeOrFailed($medecin);

        $context_doc                = new CContextDoc();
        $context_doc->context_id    = $medecin->_id;
        $context_doc->context_class = $medecin->_class;
        $context_doc->type          = 'sejour';
        $context_doc->loadMatchingObjectEsc();
        if (!$context_doc->_id) {
            if ($msg = $context_doc->store()) {
                $this->fail($msg);
            }
        }

        $desactivator = new UnusedMedecinDesactivator();
        $medecin_ids  = $this->invokePrivateMethod($desactivator, 'loadIdsInUse', 'CContextDoc', 'context_id');
        $this->assertContains($medecin->_id, $medecin_ids);
    }

    public function testSetMedecinsInUseWithEmptyArray(): void
    {
        $desactivator = new UnusedMedecinDesactivator();
        $this->invokePrivateMethod($desactivator, 'setMedecinsInUse', []);
        $this->assertEmpty($this->getPrivateProperty($desactivator, 'used_medecin_ids'));
    }

    public function testSetMedecinsInUseWithMultipleValues(): void
    {
        $desactivator = new UnusedMedecinDesactivator();
        $this->invokePrivateMethod($desactivator, 'setMedecinsInUse', [10, 20, 'test', '', null, 10, 'toto', 1568, '20']);
        $this->assertEquals(
            [10 => true, 20 => true, 'test' => true, 'toto' => true, 1568 => true],
            $this->getPrivateProperty($desactivator, 'used_medecin_ids')
        );
    }

    /**
     * @dataProvider ignoreBackSpecProvider
     */
    public function testIgnoreBackSpec(CMbBackSpec $spec, bool $expected): void
    {
        $this->assertEquals(
            $expected,
            $this->invokePrivateMethod(new UnusedMedecinDesactivator(), 'ignoreBackSpec', $spec)
        );
    }

    public function testDisableUnusedMedecinsError(): void
    {
        $mock = $this->getMockBuilder(UnusedMedecinDesactivator::class)
            ->onlyMethods(['executeQuery'])
            ->getMock();

        $mock->method('executeQuery')->willReturn(false);

        $reflection = new ReflectionClass(UnusedMedecinDesactivator::class);
        $prop = $reflection->getProperty('used_medecin_ids');
        $prop->setAccessible(true);
        $prop->setValue($mock, ['lorem']);

        $this->expectException(CMbException::class);
        $this->invokePrivateMethod($mock, 'disableUnusedMedecins');
    }

    public function testDisableMedecins(): void
    {
        $medecin_ids = [];
        for ($i = 0; $i < 10; $i++) {
            /** @var CMedecin $medecin */
            $medecin = CMedecin::getSampleObject();
            $this->storeOrFailed($medecin);

            $medecin_ids[] = $medecin->_id;
        }

        $mock = $this->getMockBuilder(UnusedMedecinDesactivator::class)
            ->onlyMethods(['executeQuery', 'loadIdsInUse', 'getAffectedRow'])
            ->getMock();

        $mock->method('executeQuery')->willReturn(true);
        $mock->method('loadIdsInUse')->willReturn($medecin_ids);
        $mock->method('getAffectedRow')->willReturn(count($medecin_ids));

        $this->assertEquals(count($medecin_ids), $mock->disableMedecins());
    }

    public function loadIdsInUseWithAbstractObjectProvider(): array
    {
        return [
            'abstactObject'               => ['CActe', 'montant_base'],
            'field not a CRefSpec'        => ['CMedecin', 'nom'],
            'field not a ref to CMedecin' => ['CMediusers', 'user_id'],
        ];
    }

    public function ignoreBackSpecProvider(): array
    {
        $back_spec_unlink          = new CMbBackSpec();
        $back_spec_unlink->_unlink = true;

        $back_spec_cascade           = new CMbBackSpec();
        $back_spec_cascade->_cascade = true;
        $back_spec_cascade->_unlink  = false;

        $back_spec_ignored_class          = new CMbBackSpec();
        $back_spec_ignored_class->class   = 'CExLink';
        $back_spec_ignored_class->_unlink = false;

        $back_spec          = new CMbBackSpec();
        $back_spec->_unlink = false;

        return [
            'spec is unlink'  => [$back_spec_unlink, true],
            'spec is cascade' => [$back_spec_cascade, true],
            'Ignored class'   => [$back_spec_ignored_class, true],
            'Do not ignore'   => [$back_spec, false],
        ];
    }
}
