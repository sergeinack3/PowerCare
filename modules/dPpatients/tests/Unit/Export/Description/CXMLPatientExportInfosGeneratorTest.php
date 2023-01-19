<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit\Export\Description;

use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Export\Description\CXMLPatientExportInfosGenerator;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\OxUnitTestCase;
use ReflectionProperty;

class CXMLPatientExportInfosGeneratorTest extends OxUnitTestCase
{
    public function testIsAlreadyHandled(): void
    {
        $generator = new CXMLPatientExportInfosGenerator([], []);

        $property = new ReflectionProperty($generator, 'handled_classes');
        $property->setAccessible(true);
        $property->setValue($generator, [CPatient::class, CSejour::class]);

        $this->assertTrue($this->invokePrivateMethod($generator, 'isAlreadyHandled', new CSejour()));
        $this->assertFalse($this->invokePrivateMethod($generator, 'isAlreadyHandled', new CConsultation()));
    }

    /**
     * @dataProvider isFiniteMetaRefProvider
     */
    public function testIsFiniteMetaRef(CStoredObject $instance, CRefSpec $spec, bool $expected): void
    {
        $generator = new CXMLPatientExportInfosGenerator([], []);

        $this->assertEquals($expected, $this->invokePrivateMethod($generator, 'isFiniteMetaRef', $instance, $spec));
    }

    /**
     * @dataProvider isRefFieldProvider
     */
    public function testIsRefField(CStoredObject $instance, string $field_name, bool $expected): void
    {
        $generator = new CXMLPatientExportInfosGenerator([], []);

        $this->assertEquals($expected, $this->invokePrivateMethod($generator, 'isRefField', $instance, $field_name));
    }

    public function testGenerateInfosForClass(): void
    {
        $patient = new CPatient();
        $fields  = $patient->getPlainFields();

        $generator   = new CXMLPatientExportInfosGenerator([], []);
        $description = $this->invokePrivateMethod($generator, 'generateInfosForClass', $patient);

        foreach (array_keys($fields) as $field) {
            $this->assertArrayHasKey($field, $description);
        }
    }

    public function testGenerateInfosForClassFieldDoesNotExists(): void
    {
        $patient       = new CPatient();
        $patient->test = 'toto';

        $generator   = new CXMLPatientExportInfosGenerator([], []);
        $description = $this->invokePrivateMethod($generator, 'generateInfosForClass', $patient);

        $this->assertArrayNotHasKey('test', $description);
    }

    public function testBuildInfosForBackRefs(): void
    {
        $back_tree = ['files', 'consultations', 'consultations', 'test'];
        $generator = new CXMLPatientExportInfosGenerator([], []);

        $this->assertEmpty($this->getPrivateProperty($generator, 'descriptions'));

        $this->invokePrivateMethod($generator, 'buildInfosForBackRefs', new CPatient(), $back_tree);

        $descriptions = $this->getPrivateProperty($generator, 'descriptions');

        $this->assertArrayHasKey('CFile', $descriptions);
        $this->assertArrayHasKey('CConsultation', $descriptions);
        $this->assertArrayNotHasKey('CSejour', $descriptions);
        $this->assertArrayNotHasKey('test', $descriptions);
    }

    public function testBuildInfosForFwRefs(): void
    {
        $fw_tree   = ['object_id', 'medecin_traitant_id', 'medecin_traitant_id', 'test', 'codes_cim'];
        $generator = new CXMLPatientExportInfosGenerator([], []);

        $this->assertEmpty($this->getPrivateProperty($generator, 'descriptions'));

        $this->invokePrivateMethod($generator, 'buildInfosForFwRefs', new CDossierMedical(), $fw_tree);

        $descriptions = $this->getPrivateProperty($generator, 'descriptions');

        $this->assertArrayHasKey('CSejour', $descriptions);
        $this->assertArrayHasKey('CPatient', $descriptions);
        $this->assertArrayHasKey('CMediusers', $descriptions);
        $this->assertArrayNotHasKey('test', $descriptions);
        $this->assertArrayNotHasKey('codes_cim', $descriptions);
    }

    public function testGenerateInfos(): void
    {
        $fw_tree = [
            'CPatient' => [
                'medecin_traitant',
            ],
            'CSejour'  => [
                'praticien_id',
                'patient_id',
                'group_id',
            ],
        ];

        $back_tree = [
            'CPatient' => [
                'files',
                'sejours',
                'consultations',
            ],
            'CSejour'  => [
                'files',
            ],
        ];

        $generator = new CXMLPatientExportInfosGenerator($fw_tree, $back_tree);

        $descriptions = $generator->generateInfos(new CPatient());

        $this->assertEqualsCanonicalizing(
            [
                'CPatient',
                'CMedecin',
                'CSejour',
                'CMediusers',
                'CGroups',
                'CFile',
                'CConsultation'
            ],
            array_keys($descriptions)
        );
    }

    public function isFiniteMetaRefProvider(): array
    {
        $patient = new CPatient();
        $file    = new CFile();
        $dossier = new CDossierMedical();

        return [
            'not a meta' => [$patient, $patient->_specs['medecin_traitant'], true],
            'not finite' => [$file, $file->_specs['object_id'], false],
            'finite'     => [$dossier, $dossier->_specs['object_id'], true],
        ];
    }

    public function isRefFieldProvider(): array
    {
        $patient = new CPatient();

        return [
            'property does not exist'    => [$patient, 'test', false],
            'property_exists_not_ref'    => [$patient, 'nom', false],
            'property_exists_and_is_ref' => [$patient, 'medecin_traitant', true],
        ];
    }
}
