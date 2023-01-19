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
use Ox\Core\Specification\IsNull;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Tests\OxUnitTestCase;

/**
 * Class IsNullTest
 *
 * @group schedules
 */
class IsNullTest extends OxUnitTestCase {
  use SpecTestTrait;

  public function satisfyingCandidatesProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = null;

    return [
      'null' => [$obj, IsNull::is('protected_property')],
    ];
  }

  public function unsatisfyingCandidatesProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = 123;
    $obj->setProtectedProperty('4');
    $obj->setPrivateProperty([1, 2, 3]);

    $obj2                  = new SpecDummy();
    $obj2->public_property = (object)123;
    $obj2->setPrivateProperty(false);

    return [
      'int'    => [$obj, IsNull::is('public_property')],
      'string' => [$obj, IsNull::is('protected_property')],
      'array'  => [$obj, IsNull::is('private_property')],
      'object' => [$obj2, IsNull::is('public_property')],
      'bool'   => [$obj2, IsNull::is('private_property')],
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
    IsNull::is($field, $values);
  }

  /**
   * @param mixed|null $candidate
   *
   * @dataProvider unreachablePropertiesProvider
   */
  public function testSpecCannotGetProperty($candidate) {
    $spec = IsNull::is('field');

    $this->expectException(CouldNotGetPropertyValue::class);
    $spec->isSatisfiedBy($candidate);
  }

  /**
   * @param SpecDummy $candidate
   * @param IsNull    $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecIsSatisfied(SpecDummy $candidate, IsNull $spec) {
    $this->assertTrue($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param mixed  $candidate
   * @param IsNull $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecIsNotSatisfied($candidate, IsNull $spec) {
    $this->assertFalse($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy $candidate
   * @param IsNull    $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecDoesNotRemainderUnsatisfied(SpecDummy $candidate, IsNull $spec) {
    $this->assertNotSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param mixed  $candidate
   * @param IsNull $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecRemainderUnsatisfied($candidate, IsNull $spec) {
    $this->assertSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy $candidate
   * @param IsNull    $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecGetViolation(SpecDummy $candidate, IsNull $spec) {
    $violation = $spec->toViolation($candidate);

    $this->assertInstanceOf(SpecificationViolation::class, $violation);
    $this->assertEquals(IsNull::class, $violation->getType());
  }

  public function testSpecCanAccessProperty() {
    $obj                  = new SpecDummy();
    $obj->public_property = null;
    $obj->setProtectedProperty(null);
    $obj->setPrivateProperty(null);

    $spec1 = IsNull::is('public_property');
    $spec2 = IsNull::is('protected_property');
    $spec3 = IsNull::is('private_property');

    $this->assertTrue(
      $spec1->isSatisfiedBy($obj)
      && $spec2->isSatisfiedBy($obj)
      && $spec3->isSatisfiedBy($obj)
    );
  }
}
