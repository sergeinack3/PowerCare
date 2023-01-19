<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Specification;

use Ox\Core\Specification\Enum;
use Ox\Core\Specification\Exception\CouldNotCreateSpecification;
use Ox\Core\Specification\Exception\CouldNotGetPropertyValue;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Tests\OxUnitTestCase;

/**
 * Class EnumTest
 *
 * @group schedules
 */
class EnumTest extends OxUnitTestCase {
  use SpecTestTrait;

  public function invalidParametersProvider(): array {
    return [
      'invalid property'    => [[1, 2, 3], null],
      'non string property' => [123, [1, 2, 3]],
      'without parameters'  => [null, null],
      'without values'      => ['field', null],
      'invalid values'      => ['field', 1, 2, 3],
      'empty values'        => ['field', []],
    ];
  }

  public function satisfyingCandidatesProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = '1';
    $obj->setProtectedProperty(2);
    // Due to non-strict comparison
    $obj->setPrivateProperty(true);

    return [
      'coercition' => [$obj, Enum::is('public_property', [1, 2, 3])],
      'bound'      => [$obj, Enum::is('protected_property', [1, 2, 3])],
      'true'       => [$obj, Enum::is('private_property', [1, 2, 3])],
    ];
  }

  public function unsatisfyingCandidatesProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = null;
    $obj->setProtectedProperty(4);
    $obj->setPrivateProperty([1, 2, 3]);

    return [
      'null'         => [$obj, Enum::is('public_property', [1, 2, 3])],
      'out of bound' => [$obj, Enum::is('protected_property', [1, 2, 3])],
      'invalid'      => [$obj, Enum::is('private_property', [1, 2, 3])],
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
    Enum::is($field, $values);
  }

  /**
   * @param mixed|null $candidate
   *
   * @dataProvider unreachablePropertiesProvider
   */
  public function testSpecCannotGetProperty($candidate) {
    $spec = Enum::is('field', [1, 2, 3]);
    $this->expectException(CouldNotGetPropertyValue::class);
    $spec->isSatisfiedBy($candidate);
  }

  /**
   * @param SpecDummy $candidate
   * @param Enum      $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecIsSatisfied(SpecDummy $candidate, Enum $spec) {
    $this->assertTrue($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param mixed $candidate
   * @param Enum  $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecIsNotSatisfied($candidate, Enum $spec) {
    $this->assertFalse($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy $candidate
   * @param Enum      $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecDoesNotRemainderUnsatisfied(SpecDummy $candidate, Enum $spec) {
    $this->assertNotSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param mixed $candidate
   * @param Enum  $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecRemainderUnsatisfied($candidate, Enum $spec) {
    $this->assertSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy $candidate
   * @param Enum      $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecGetViolation(SpecDummy $candidate, Enum $spec) {
    $violation = $spec->toViolation($candidate);

    $this->assertInstanceOf(SpecificationViolation::class, $violation);
    $this->assertEquals(Enum::class, $violation->getType());
  }

  public function testSpecCanAccessProperty() {
    $obj                  = new SpecDummy();
    $obj->public_property = 2;
    $obj->setProtectedProperty(2);
    $obj->setPrivateProperty(2);

    $spec1 = Enum::is('public_property', [2]);
    $spec2 = Enum::is('protected_property', [2]);
    $spec3 = Enum::is('private_property', [2]);

    $this->assertTrue(
      $spec1->isSatisfiedBy($obj)
      && $spec2->isSatisfiedBy($obj)
      && $spec3->isSatisfiedBy($obj)
    );
  }
}
