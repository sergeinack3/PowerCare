<?php
/**
 * @package Core\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\CMbFieldSpec;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CMbFieldSpecTest
 */
class CMbFieldSpecTest extends OxUnitTestCase {

  /**
   * @dataProvider sepcsProvider
   *
   * @param CMbFieldSpec $spec
   */
  public function testGetSepcsForApiSchema(CMbFieldSpec $spec): void {
    // Act
    $options = $spec->transform();

    // Assert
    $this->assertIsArray($options);
  }

  /**
   * @dataProvider sepcsProvider
   *
   * @param CMbFieldSpec $spec
   */
  public function testValidateSpecsForApiSchema(CMbFieldSpec $spec): void {
    // Act
    $options = $spec->transform();

    // Assert
    $this->assertEquals($options['id'], md5($spec->className . '-' . $spec->fieldName));
    $this->assertEquals($options['type'], $spec->getSpecType());

  }


  /**
   * @return mixed
   * @throws Exception
   */
  public function sepcsProvider() {
    $children = CClassMap::getInstance()->getClassChildren(CMbFieldSpec::class);
    $datas    = [];

    foreach ($children as $class_name) {
      $sn         = CClassMap::getSN($class_name);
      $spec       = new $class_name('lorem', 'ipsum');
      $datas[$sn] = [$spec];
    }


    return $datas;
  }


}
