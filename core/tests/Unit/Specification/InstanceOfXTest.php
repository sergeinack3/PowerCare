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
use Ox\Core\Specification\InstanceOfX;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Tests\OxUnitTestCase;
use stdClass;

/**
 * Class InstanceOfXTest
 *
 * @group schedules
 */
class InstanceOfXTest extends OxUnitTestCase {
  use SpecTestTrait;

  public function satisfyingCandidatesProvider(): array {
    $obj = new SpecDummy();
    $obj->setProtectedProperty((object)123);
    $obj->setPrivateProperty(new SpecDummy());

    return [
      'object' => [$obj, InstanceOfX::is('protected_property', new stdClass())],
      'dummy'  => [$obj, InstanceOfX::is('private_property', SpecDummy::class)],
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
      'null'         => [$obj, InstanceOfX::is('public_property', SpecDummy::class)],
      'out of bound' => [$obj, InstanceOfX::is('protected_property', SpecDummy::class)],
      'invalid'      => [$obj, InstanceOfX::is('private_property', SpecDummy::class)],
      'coercition'   => [$obj2, InstanceOfX::is('public_property', SpecDummy::class)],
      'truly'        => [$obj2, InstanceOfX::is('private_property', SpecDummy::class)],
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
    InstanceOfX::is($field, $values);
  }

  /**
   * @param mixed|null $candidate
   *
   * @dataProvider unreachablePropertiesProvider
   */
  public function testSpecCannotGetProperty($candidate) {
    $spec = InstanceOfX::is('field', SpecDummy::class);

    $this->expectException(CouldNotGetPropertyValue::class);
    $spec->isSatisfiedBy($candidate);
  }

  /**
   * @param SpecDummy   $candidate
   * @param InstanceOfX $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecIsSatisfied(SpecDummy $candidate, InstanceOfX $spec) {
    $this->assertTrue($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param mixed       $candidate
   * @param InstanceOfX $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecIsNotSatisfied($candidate, InstanceOfX $spec) {
    $this->assertFalse($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy   $candidate
   * @param InstanceOfX $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecDoesNotRemainderUnsatisfied(SpecDummy $candidate, InstanceOfX $spec) {
    $this->assertNotSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param mixed       $candidate
   * @param InstanceOfX $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecRemainderUnsatisfied($candidate, InstanceOfX $spec) {
    $this->assertSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy   $candidate
   * @param InstanceOfX $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecGetViolation(SpecDummy $candidate, InstanceOfX $spec) {
    $violation = $spec->toViolation($candidate);

    $this->assertInstanceOf(SpecificationViolation::class, $violation);
    $this->assertEquals(InstanceOfX::class, $violation->getType());
  }

  public function testSpecCanAccessProperty() {
    $obj                  = new SpecDummy();
    $obj->public_property = new SpecDummy();
    $obj->setProtectedProperty(new SpecDummy());
    $obj->setPrivateProperty(new SpecDummy());

    $spec1 = InstanceOfX::is('public_property', SpecDummy::class);
    $spec2 = InstanceOfX::is('protected_property', SpecDummy::class);
    $spec3 = InstanceOfX::is('private_property', SpecDummy::class);

    $this->assertTrue(
      $spec1->isSatisfiedBy($obj)
      && $spec2->isSatisfiedBy($obj)
      && $spec3->isSatisfiedBy($obj)
    );
  }
}
