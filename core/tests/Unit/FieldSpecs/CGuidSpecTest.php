<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\FieldSpecs;

use Ox\Core\FieldSpecs\CGuidSpec;
use Ox\Mediboard\Admin\CUser;
use Ox\Tests\OxUnitTestCase;
use stdClass;

class CGuidSpecTest extends OxUnitTestCase
{
    /**
     * @dataProvider checkPropertyProvider
     */
    public function testCheckProperty(CGuidSpec $spec, $obj, ?string $expected): void
    {
        $this->assertEquals($expected, $spec->checkProperty($obj));
    }

    public function checkPropertyProvider(): array
    {
        $obj_invalid = new stdClass();
        $obj_invalid->bar = 'stdClass-1234';

        $current_user = CUser::get();
        $obj = new stdClass();
        $obj->bar = $current_user->_guid;

        $obj_not_found = new stdClass();
        $obj_not_found->bar = 'CUser-' . PHP_INT_MAX;

        $spec_notNull = new CGuidSpec('foo', 'bar');
        $spec_notNull->notNull = true;

        $spec_class_not_cstored_object = new CGuidSpec('foo', 'bar');
        $spec_class_not_cstored_object->class = 'lorem';

        $spec_class_guid_diff = new CGuidSpec('foo', 'bar');
        $spec_class_guid_diff->class = 'CPatient';

        return [
            'Spec notNull'                         => [$spec_notNull, $obj, "Spécifications de propriété incohérentes 'notNull'"],
            'Spec class not CStoredObject'         => [$spec_class_not_cstored_object, $obj, "La classe 'lorem' n'est pas une classe d'objet enregistrée"],
            'Spec class GUID is not of same class' => [$spec_class_guid_diff, $obj, "Objet référencé 'CUser' n'est pas du type 'CPatient'"],
            'GUID is not CStoredObject'            => [new CGuidSpec('foo', 'bar'), $obj_invalid, "La classe 'stdClass' n'est pas une classe d'objet enregistrée"],
            'Object not found'                     => [new CGuidSpec('foo', 'bar'), $obj_not_found, "Objet référencé de type 'CUser' introuvable"],
            'Property valid'                       => [new CGuidSpec('foo', 'bar'), $obj, null],
        ];
    }
}
