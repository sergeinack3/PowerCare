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
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Tests\OxUnitTestCase;

/**
 * Class NotNullTest
 *
 * @group schedules
 */
class NotNullTest extends OxUnitTestCase {
  use SpecTestTrait;

  public function satisfyingCandidatesProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = 123;
    $obj->setProtectedProperty('4');
    $obj->setPrivateProperty([1, 2, 3]);

    $obj2                  = new SpecDummy();
    $obj2->public_property = (object)123;
    $obj2->setPrivateProperty(false);

    return [
      'int'    => [$obj, NotNull::is('public_property')],
      'string' => [$obj, NotNull::is('protected_property')],
      'array'  => [$obj, NotNull::is('private_property')],
      'object' => [$obj2, NotNull::is('public_property')],
      'bool'   => [$obj2, NotNull::is('private_property')],
    ];
  }

  public function unsatisfyingCandidatesProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = null;

    return [
      'null' => [$obj, NotNull::is('protected_property')],
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
    NotNull::is($field, $values);
  }

  /**
   * @param mixed|null $candidate
   *
   * @dataProvider unreachablePropertiesProvider
   */
  public function testSpecCannotGetProperty($candidate) {
    $spec = NotNull::is('field');

    $this->expectException(CouldNotGetPropertyValue::class);
    $spec->isSatisfiedBy($candidate);
  }

  /**
   * @param SpecDummy $candidate
   * @param NotNull   $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecIsSatisfied(SpecDummy $candidate, NotNull $spec) {
    $this->assertTrue($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param mixed   $candidate
   * @param NotNull $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecIsNotSatisfied($candidate, NotNull $spec) {
    $this->assertFalse($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy $candidate
   * @param NotNull   $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecDoesNotRemainderUnsatisfied(SpecDummy $candidate, NotNull $spec) {
    $this->assertNotSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param mixed   $candidate
   * @param NotNull $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecRemainderUnsatisfied($candidate, NotNull $spec) {
    $this->assertSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy $candidate
   * @param NotNull   $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecGetViolation(SpecDummy $candidate, NotNull $spec) {
    $violation = $spec->toViolation($candidate);

    $this->assertInstanceOf(SpecificationViolation::class, $violation);
    $this->assertEquals(NotNull::class, $violation->getType());
  }

  public function testSpecCanAccessProperty() {
    $obj                  = new SpecDummy();
    $obj->public_property = true;
    $obj->setProtectedProperty('abc');
    $obj->setPrivateProperty(123);

    $spec1 = NotNull::is('public_property');
    $spec2 = NotNull::is('protected_property');
    $spec3 = NotNull::is('private_property');

    $this->assertTrue(
      $spec1->isSatisfiedBy($obj)
      && $spec2->isSatisfiedBy($obj)
      && $spec3->isSatisfiedBy($obj)
    );
  }
}
