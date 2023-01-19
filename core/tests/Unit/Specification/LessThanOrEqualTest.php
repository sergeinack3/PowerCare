<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Specification;

use Ox\Core\Specification\Exception\CouldNotCreateSpecification;
use Ox\Core\Specification\Exception\CouldNotGetPropertyValue;
use Ox\Core\Specification\LessThanOrEqual;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Tests\OxUnitTestCase;

/**
 * Class LessThanOrEqualTest
 *
 * @group schedules
 */
class LessThanOrEqualTest extends OxUnitTestCase {
  use SpecTestTrait;

  public function satisfyingCandidatesProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = '123';
    $obj->setProtectedProperty(123);
    $obj->setPrivateProperty('2020-01-01 00:00:00');

    $obj2                  = new SpecDummy();
    $obj2->public_property = [1, 2, 3];

    return [
      'inferior'   => [$obj, LessThanOrEqual::is('public_property', 125)],
      'equal'      => [$obj, LessThanOrEqual::is('public_property', 123)],
      'coercition' => [$obj, LessThanOrEqual::is('public_property', 124)],
      'int'        => [$obj, LessThanOrEqual::is('protected_property', 123)],
      'date'       => [$obj, LessThanOrEqual::is('private_property', '2020-01-01 00:00:01')],
      'array'      => [$obj2, LessThanOrEqual::is('public_property', [1, 2, 4])],
    ];
  }

  public function unsatisfyingCandidatesProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = null;
    $obj->setProtectedProperty(4);
    $obj->setPrivateProperty([1, 2, 3]);

    $obj2                  = new SpecDummy();
    $obj2->public_property = '123';
    $obj2->setPrivateProperty(true);

    return [
      'null'         => [$obj, LessThanOrEqual::is('public_property', 1)],
      'out of bound' => [$obj, LessThanOrEqual::is('protected_property', 3)],
      'invalid'      => [$obj, LessThanOrEqual::is('private_property', [1, 2, 2])],
      'coercition'   => [$obj2, LessThanOrEqual::is('public_property', 122)],
      'truly'        => [$obj2, LessThanOrEqual::is('private_property', 0)],
    ];
  }

  /**
   * @param mixed|null $field
   * @param mixed|null $values
   *
   * @dataProvider invalidParametersProvider
   */
  public function testSpecCannotBeInstantiated($field, $values) {
    $this->expectException(CouldNotCreateSpecification::class);
    LessThanOrEqual::is($field, $values);
  }

  /**
   * @param mixed|null $candidate
   *
   * @dataProvider unreachablePropertiesProvider
   */
  public function testSpecCannotGetProperty($candidate) {
    $spec = LessThanOrEqual::is('field', 123);

    $this->expectException(CouldNotGetPropertyValue::class);
    $spec->isSatisfiedBy($candidate);
  }

  /**
   * @param SpecDummy       $candidate
   * @param LessThanOrEqual $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecIsSatisfied(SpecDummy $candidate, LessThanOrEqual $spec) {
    $this->assertTrue($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param mixed           $candidate
   * @param LessThanOrEqual $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecIsNotSatisfied($candidate, LessThanOrEqual $spec) {
    $this->assertFalse($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy       $candidate
   * @param LessThanOrEqual $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecDoesNotRemainderUnsatisfied(SpecDummy $candidate, LessThanOrEqual $spec) {
    $this->assertNotSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param mixed           $candidate
   * @param LessThanOrEqual $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecRemainderUnsatisfied($candidate, LessThanOrEqual $spec) {
    $this->assertSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy       $candidate
   * @param LessThanOrEqual $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecGetViolation(SpecDummy $candidate, LessThanOrEqual $spec) {
    $violation = $spec->toViolation($candidate);

    $this->assertInstanceOf(SpecificationViolation::class, $violation);
    $this->assertEquals(LessThanOrEqual::class, $violation->getType());
  }

  public function testSpecCanAccessProperty() {
    $obj                  = new SpecDummy();
    $obj->public_property = 1;
    $obj->setProtectedProperty(1);
    $obj->setPrivateProperty(1);

    $spec1 = LessThanOrEqual::is('public_property', 2);
    $spec2 = LessThanOrEqual::is('protected_property', 2);
    $spec3 = LessThanOrEqual::is('private_property', 2);

    $this->assertTrue(
      $spec1->isSatisfiedBy($obj)
      && $spec2->isSatisfiedBy($obj)
      && $spec3->isSatisfiedBy($obj)
    );
  }
}
