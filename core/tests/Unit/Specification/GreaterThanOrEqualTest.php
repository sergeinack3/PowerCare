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
use Ox\Core\Specification\GreaterThanOrEqual;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Tests\OxUnitTestCase;

/**
 * Class GreaterThanOrEqualTest
 *
 * @group schedules
 */
class GreaterThanOrEqualTest extends OxUnitTestCase {
  use SpecTestTrait;

  public function satisfyingCandidatesProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = '123';
    $obj->setProtectedProperty(123);
    $obj->setPrivateProperty('2020-01-01 00:00:01');

    $obj2                  = new SpecDummy();
    $obj2->public_property = [1, 2, 3];
    $obj2->setPrivateProperty(true);

    return [
      'equal'      => [$obj, GreaterThanOrEqual::is('public_property', 123)],
      'superior'   => [$obj, GreaterThanOrEqual::is('public_property', 122)],
      'coercition' => [$obj, GreaterThanOrEqual::is('public_property', 122)],
      'int'        => [$obj, GreaterThanOrEqual::is('protected_property', 121)],
      'date'       => [$obj, GreaterThanOrEqual::is('private_property', '2020-01-01 00:00:00')],
      'array'      => [$obj2, GreaterThanOrEqual::is('public_property', [1, 2, 2])],
      'truly'      => [$obj2, GreaterThanOrEqual::is('private_property', 500)],
    ];
  }

  public function unsatisfyingCandidatesProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = null;
    $obj->setProtectedProperty(4);
    $obj->setPrivateProperty([1, 2, 3]);

    $obj2                  = new SpecDummy();
    $obj2->public_property = '123';
    $obj2->setPrivateProperty(false);

    return [
      'null'         => [$obj, GreaterThanOrEqual::is('public_property', 1)],
      'out of bound' => [$obj, GreaterThanOrEqual::is('protected_property', 124)],
      'invalid'      => [$obj, GreaterThanOrEqual::is('private_property', [1, 2, 4])],
      'coercition'   => [$obj2, GreaterThanOrEqual::is('public_property', 124)],
      'falsy'        => [$obj2, GreaterThanOrEqual::is('private_property', 1)],
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
    GreaterThanOrEqual::is($field, $values);
  }

  /**
   * @param mixed|null $candidate
   *
   * @dataProvider unreachablePropertiesProvider
   */
  public function testSpecCannotGetProperty($candidate) {
    $spec = GreaterThanOrEqual::is('field', 123);

    $this->expectException(CouldNotGetPropertyValue::class);
    $spec->isSatisfiedBy($candidate);
  }

  /**
   * @param SpecDummy          $candidate
   * @param GreaterThanOrEqual $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecIsSatisfied(SpecDummy $candidate, GreaterThanOrEqual $spec) {
    $this->assertTrue($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param mixed              $candidate
   * @param GreaterThanOrEqual $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecIsNotSatisfied($candidate, GreaterThanOrEqual $spec) {
    $this->assertFalse($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy          $candidate
   * @param GreaterThanOrEqual $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecDoesNotRemainderUnsatisfied(SpecDummy $candidate, GreaterThanOrEqual $spec) {
    $this->assertNotSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param mixed              $candidate
   * @param GreaterThanOrEqual $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecRemainderUnsatisfied($candidate, GreaterThanOrEqual $spec) {
    $this->assertSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy          $candidate
   * @param GreaterThanOrEqual $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecGetViolation(SpecDummy $candidate, GreaterThanOrEqual $spec) {
    $violation = $spec->toViolation($candidate);

    $this->assertInstanceOf(SpecificationViolation::class, $violation);
    $this->assertEquals(GreaterThanOrEqual::class, $violation->getType());
  }

  public function testSpecCanAccessProperty() {
    $obj                  = new SpecDummy();
    $obj->public_property = 2;
    $obj->setProtectedProperty(2);
    $obj->setPrivateProperty(2);

    $spec1 = GreaterThanOrEqual::is('public_property', 1);
    $spec2 = GreaterThanOrEqual::is('protected_property', 1);
    $spec3 = GreaterThanOrEqual::is('private_property', 1);

    $this->assertTrue(
      $spec1->isSatisfiedBy($obj)
      && $spec2->isSatisfiedBy($obj)
      && $spec3->isSatisfiedBy($obj)
    );
  }
}
