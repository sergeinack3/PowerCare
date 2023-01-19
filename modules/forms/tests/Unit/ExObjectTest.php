<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Tests\Unit;

use Ox\Core\CMbObject;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Forms\Tests\Fixtures\FormReportDataFixtures;
use Ox\Mediboard\Forms\Tests\Fixtures\SimpleFormFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExObject;
use Ox\Tests\OxUnitTestCase;

/**
 * TODO :
 *  - Test reporting from & to MB objects
 *  - Test list concept & others datatype
 *  - Test forms import & export
 */
class ExObjectTest extends OxUnitTestCase
{
    /** @var CSejour|null */
    private static $sejour;

    public function testStoreSimpleFormWithSingleField(): void
    {
        /***** ARRANGE *****/
        $ex_class       = $this->getExClass(SimpleFormFixtures::REF_EX_CLASS_SINGLE_FIELD);
        $ex_class_field = $this->getExClassField(SimpleFormFixtures::REF_EX_CLASS_FIELD_STR);
        $ex_object      = new CExObject($ex_class->_id);
        $sejour         = $this->getSejour();

        /***** ACT *****/
        $ex_object->{$ex_class_field->name} = "lorem";
        $this->setReferences($ex_object, $sejour, 'modification');
        $this->storeOrFailed($ex_object);

        /***** ASSERT *****/
        $this->assertNotNull($ex_object->{$ex_class_field->name});
        $this->deleteOrFailed($ex_object);
    }

    public function testInterFormReportingWithSimpleField(): void
    {
        /***** ARRANGE *****/
        $ex_class       = $this->getExClass(FormReportDataFixtures::REF_EX_CLASS_REPORT_DATA);
        $ex_class_field = $this->getExClassField(FormReportDataFixtures::REF_EX_CLASS_FIELD_NUM);
        $sejour         = $this->getSejour();
        $ex_object      = new CExObject($ex_class->_id);
        $ex_object_2    = new CExObject($ex_class->_id);

        /***** ACT *****/
        $ex_object->{$ex_class_field->name} = 100;
        $this->setReferences($ex_object, $sejour, 'modification');

        $this->storeOrFailed($ex_object);
        $value_before = $ex_object->{$ex_class_field->name};

        $this->setReferences($ex_object_2, $sejour, 'modification');
        $ex_object_2->getReportedValues();
        $value_after = $ex_object_2->{$ex_class_field->name};

        /***** ASSERT *****/
        $this->assertEquals($value_before, $value_after);
        $this->deleteOrFailed($ex_object);
    }

    /**
     * @depends testInterFormReportingWithSimpleField
     */
    public function testNoDataReportingBetweenForms(): void
    {
        /***** ARRANGE *****/
        $ex_class       = $this->getExClass(SimpleFormFixtures::REF_EX_CLASS_SINGLE_FIELD);
        $ex_class_field = $this->getExClassField(SimpleFormFixtures::REF_EX_CLASS_FIELD_STR);
        $sejour         = $this->getSejour();
        $ex_object      = new CExObject($ex_class->_id);
        $ex_object_2    = new CExObject($ex_class->_id);

        /***** ACT *****/
        $ex_object->{$ex_class_field->name} = "lorem";
        $this->setReferences($ex_object, $sejour, 'modification');
        $this->storeOrFailed($ex_object);

        $this->setReferences($ex_object_2, $sejour, 'modification');
        $ex_object_2->getReportedValues();
        $value_after = $ex_object_2->{$ex_class_field->name};

        /***** ASSERT *****/
        $this->assertEmpty($value_after);
        $this->deleteOrFailed($ex_object);
    }

    private function getExClass(string $tag): CExClass
    {
        return $this->getObjectFromFixturesReference(
            CExClass::class,
            $tag
        );
    }

    private function getExClassField(string $tag): CExClassField
    {
        return $this->getObjectFromFixturesReference(
            CExClassField::class,
            $tag
        );
    }

    private function setReferences(CExObject $ex_object, CMbObject $object, string $event_name): void
    {
        [$ref1, $ref2] = $this->getObjectReferences($object, $event_name);
        $ex_object->setObject($object);
        $ex_object->setReferenceObject_1($ref1);
        $ex_object->setReferenceObject_2($ref2);
        $ex_object->group_id = CGroups::loadCurrent()->_id;
    }

    private function getObjectReferences(CMbObject $object, string $event_name): array
    {
        $event = $object->getSpec()->events[$event_name] ?? [];

        if (!$event) {
            $this->fail('No event name for this object');
        }

        $ref1 = $event['reference1'];
        $ref2 = $event['reference2'];

        $ref1_class = $ref1[0];
        $ref2_class = $ref2[0];

        $ref1_field = $ref1[1];
        $ref2_field = $ref2[1];

        $obj1 = $ref1_class::findOrFail($object->{$ref1_field});
        $obj2 = $ref2_class::findOrFail($object->{$ref2_field});

        return [$obj1, $obj2];
    }

    public function generateSejour(): CSejour
    {
        if (static::$sejour instanceof CSejour) {
            return static::$sejour;
        }

        /** @var CPatient $patient */
        $patient = CPatient::getSampleObject();
        $this->storeOrFailed($patient);

        /** @var CSejour $sejour */
        $sejour               = CSejour::getSampleObject();
        $sejour->patient_id   = $patient->_id;
        $sejour->praticien_id = $this->getObjectFromFixturesReference(
            CMediusers::class,
            UsersFixtures::REF_USER_MEDECIN
        )->_id;
        $sejour->group_id     = CGroups::loadCurrent()->_id;
        $this->storeOrFailed($sejour);

        return static::$sejour = $sejour;
    }

    private function getSejour(): CSejour
    {
        return $this->generateSejour();
    }
}
