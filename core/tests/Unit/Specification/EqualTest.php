<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Specification;

use Ox\Core\Specification\Equal;
use Ox\Core\Specification\Exception\CouldNotCreateSpecification;
use Ox\Core\Specification\Exception\CouldNotGetPropertyValue;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Tests\OxUnitTestCase;

/**
 * Class EqualTest
 *
 * @group schedules
 */
class EqualTest extends OxUnitTestCase {
  use SpecTestTrait;

  public function satisfyingCandidatesProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = '123';
    $obj->setProtectedProperty(123);
    // Due to non-strict comparison
    $obj->setPrivateProperty(true);

    return [
      'coercition' => [$obj, Equal::is('public_property', 123)],
      'bound'      => [$obj, Equal::is('protected_property', 123)],
      'true'       => [$obj, Equal::is('private_property', 123)],
    ];
  }

  public function unsatisfyingCandidatesProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = null;
    $obj->setProtectedProperty(4);
    $obj->setPrivateProperty([1, 2, 3]);

    return [
      'null'         => [$obj, Equal::is('public_property', 123)],
      'out of bound' => [$obj, Equal::is('protected_property', 123)],
      'invalid'      => [$obj, Equal::is('private_property', 123)],
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
    Equal::is($field, $values);
  }

  /**
   * @param mixed|null $candidate
   *
   * @dataProvider unreachablePropertiesProvider
   */
  public function testSpecCannotGetProperty($candidate) {
    $spec = Equal::is('field', 123);

    $this->expectException(CouldNotGetPropertyValue::class);
    $spec->isSatisfiedBy($candidate);
  }

  /**
   * @param SpecDummy $candidate
   * @param Equal     $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecIsSatisfied(SpecDummy $candidate, Equal $spec) {
    $this->assertTrue($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param mixed $candidate
   * @param Equal $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecIsNotSatisfied($candidate, Equal $spec) {
    $this->assertFalse($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy $candidate
   * @param Equal     $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecDoesNotRemainderUnsatisfied(SpecDummy $candidate, Equal $spec) {
    $this->assertNotSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param mixed $candidate
   * @param Equal $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecRemainderUnsatisfied($candidate, Equal $spec) {
    $this->assertSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy $candidate
   * @param Equal     $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecGetViolation(SpecDummy $candidate, Equal $spec) {
    $violation = $spec->toViolation($candidate);

    $this->assertInstanceOf(SpecificationViolation::class, $violation);
    $this->assertEquals(Equal::class, $violation->getType());
  }

  public function testSpecCanAccessProperty() {
    $obj                  = new SpecDummy();
    $obj->public_property = 2;
    $obj->setProtectedProperty(2);
    $obj->setPrivateProperty(2);

    $spec1 = Equal::is('public_property', 2);
    $spec2 = Equal::is('protected_property', 2);
    $spec3 = Equal::is('private_property', 2);

    $this->assertTrue(
      $spec1->isSatisfiedBy($obj)
      && $spec2->isSatisfiedBy($obj)
      && $spec3->isSatisfiedBy($obj)
    );
  }
}
