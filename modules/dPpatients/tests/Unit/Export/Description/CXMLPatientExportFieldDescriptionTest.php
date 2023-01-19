<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit\Export\Description;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Export\Description\CXMLPatientExportFieldDescription;
use Ox\Tests\OxUnitTestCase;

class CXMLPatientExportFieldDescriptionTest extends OxUnitTestCase
{
    public function testCreateWithNoField(): void
    {
        $this->expectExceptionObject(
            new CMbException(
                'CXMLPatientExportFieldDescription-Error-Field must not be null and must be a field of the object',
                '',
                'CPatient'
            )
        );

        new CXMLPatientExportFieldDescription(new CPatient(), '');
    }

    public function testCreateWithNonexistingField(): void
    {
        $this->expectExceptionObject(
            new CMbException(
                'CXMLPatientExportFieldDescription-Error-Field must not be null and must be a field of the object',
                'test',
                'CPatient'
            )
        );

        new CXMLPatientExportFieldDescription(new CPatient(), 'test');
    }

    /**
     * @dataProvider createProvider
     */
    public function testCreateWithKey(CStoredObject $object, string $field, string $path): void
    {
        $description = new CXMLPatientExportFieldDescription($object, $field);

        $this->assertEquals($field, $description->getName());
        $this->assertEquals(CAppUI::tr($object->_class . '-' . $field), $description->getTr());
        $this->assertEquals(CAppUI::tr($object->_class . '-' . $field . '-desc'), $description->getDesc());
        $this->assertEquals($object->getProps()[$field], $description->getProp());
        $this->assertEquals($object->_specs[$field]->getDBSpec(), $description->getSqlProp());
        $this->assertEquals($path, $description->getPath());
    }

    public function createProvider(): array
    {
        $patient = new CPatient();

        return [
            'create_with_key' => [$patient, 'patient_id', '/@id'],
            'create_with_ref' => [$patient, 'medecin_traitant', '/@medecin_traitant'],
            'create_with_field' => [$patient, 'nom', '/field[@name="nom"]'],
        ];
    }
}
