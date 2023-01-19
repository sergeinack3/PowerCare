<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Specification;

use Ox\Core\Specification\SpecificationInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Trait SpecTestTrait
 */
trait SpecTestTrait {
  public function invalidParametersProvider(): array {
    return [
      'invalid property'    => [[1, 2, 3], null],
      'non string property' => [123, 123],
      'without parameters'  => [null, null],
    ];
  }

  public function unreachablePropertiesProvider(): array {
    $obj1        = new SpecDummy();
    $obj1->field = 1;

    $obj2 = new SpecDummy();

    return [
      'null'    => [null],
      'scalar'  => [123],
      'array'   => [[1, 2, 3]],
      'dynamic' => [$obj1],
      'unset'   => [$obj2],
    ];
  }

  public function unreachablePropertiesLengthProvider(): array {
    $obj1                  = new SpecDummy();
    $obj1->public_property = 123;

    $obj2                  = new SpecDummy();
    $obj2->public_property = true;

    return [
      'int'  => [$obj1],
      'bool' => [$obj2],
    ];
  }

  /**
   * @param bool $satisfiying
   *
   * @return MockObject
   */
  public function getSpecMock(bool $satisfiying): MockObject {
    $spec = $this->getMockBuilder(SpecificationInterface::class)
      ->getMock();

    $spec->method('isSatisfiedBy')->willReturn($satisfiying);
    $spec->method('remainderUnsatisfiedBy')->willReturnSelf();

    return $spec;
  }
}
