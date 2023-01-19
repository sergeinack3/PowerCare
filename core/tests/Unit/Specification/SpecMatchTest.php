<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Specification;

use Ox\Core\Specification\Exception\CouldNotCheckSpecification;
use Ox\Core\Specification\Exception\CouldNotCreateSpecification;
use Ox\Core\Specification\Exception\CouldNotGetPropertyValue;
use Ox\Core\Specification\SpecMatch;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Tests\OxUnitTestCase;

/**
 * Class MatchTest
 *
 * @group schedules
 */
class SpecMatchTest extends OxUnitTestCase {
  use SpecTestTrait;

  public function satisfyingCandidatesProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = 123;
    $obj->setProtectedProperty('123');
    $obj->setPrivateProperty(123.0);

    $obj2                  = new SpecDummy();
    $obj2->public_property = true;
    $obj2->setProtectedProperty(null);

    return [
      'int'    => [$obj, SpecMatch::is('public_property', '/\d{3}/')],
      'string' => [$obj, SpecMatch::is('protected_property', '/123/')],
      'float'  => [$obj, SpecMatch::is('private_property', '/123/')],
      'bool'   => [$obj2, SpecMatch::is('public_property', '/1/')],
      'null'   => [$obj2, SpecMatch::is('protected_property', '/.*/')],
    ];
  }

  public function unsatisfyingCandidatesProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = null;
    $obj->setProtectedProperty(4);
    $obj->setPrivateProperty(true);

    return [
      'null'         => [$obj, SpecMatch::is('public_property', '/0/')],
      'out of bound' => [$obj, SpecMatch::is('protected_property', '/3/')],
      'true'        => [$obj, SpecMatch::is('private_property', '/0/')],
    ];
  }

  public function invalidMatchingParametersProvider(): array {
    $obj                  = new SpecDummy();
    $obj->public_property = [1, 2, 3];
    $obj->setProtectedProperty((object)123);

    return [
      'array'  => [$obj, SpecMatch::is('public_property', '/.*/')],
      'object' => [$obj, SpecMatch::is('protected_property', '/.*/')],
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
    SpecMatch::is($field, $values);
  }

  /**
   * @param mixed|null $candidate
   *
   * @dataProvider unreachablePropertiesProvider
   */
  public function testSpecCannotGetProperty($candidate) {
    $spec = SpecMatch::is('field', '/123/');

    $this->expectException(CouldNotGetPropertyValue::class);
    $spec->isSatisfiedBy($candidate);
  }

  /**
   * @param mixed|null $candidate
   *
   * @dataProvider invalidMatchingParametersProvider
   */
  public function testSpecCannotMatch($candidate) {
    $spec = SpecMatch::is('public_property', '/.*/');

    $this->expectException(CouldNotCheckSpecification::class);
    $spec->isSatisfiedBy($candidate);
  }

  /**
   * @param SpecDummy $candidate
   * @param SpecMatch     $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecIsSatisfied(SpecDummy $candidate, SpecMatch $spec) {
    $this->assertTrue($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param mixed $candidate
   * @param SpecMatch $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecIsNotSatisfied($candidate, SpecMatch $spec) {
    $this->assertFalse($spec->isSatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy $candidate
   * @param SpecMatch     $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecDoesNotRemainderUnsatisfied(SpecDummy $candidate, SpecMatch $spec) {
    $this->assertNotSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param mixed $candidate
   * @param SpecMatch $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecRemainderUnsatisfied($candidate, SpecMatch $spec) {
    $this->assertSame($spec, $spec->remainderUnsatisfiedBy($candidate));
  }

  /**
   * @param SpecDummy $candidate
   * @param SpecMatch     $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecGetViolation(SpecDummy $candidate, SpecMatch $spec) {
    $violation = $spec->toViolation($candidate);

    $this->assertInstanceOf(SpecificationViolation::class, $violation);
    $this->assertEquals(SpecMatch::class, $violation->getType());
  }

  public function testSpecCanAccessProperty() {
    $obj                  = new SpecDummy();
    $obj->public_property = 2;
    $obj->setProtectedProperty(2);
    $obj->setPrivateProperty(2);

    $spec1 = SpecMatch::is('public_property', '/.*/');
    $spec2 = SpecMatch::is('protected_property', '/.*/');
    $spec3 = SpecMatch::is('private_property', '/.*/');

    $this->assertTrue(
      $spec1->isSatisfiedBy($obj)
      && $spec2->isSatisfiedBy($obj)
      && $spec3->isSatisfiedBy($obj)
    );
  }
}
