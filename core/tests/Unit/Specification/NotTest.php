<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Specification;

use Ox\Core\Specification\Not;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Tests\OxUnitTestCase;

/**
 * Class NotTest
 *
 * @group schedules
 */
class NotTest extends OxUnitTestCase {
  use SpecTestTrait;

  public function satisfyingCandidatesProvider(): array {
    return [
      'not false' => [$this->getSpecMock(false)],
    ];
  }

  public function unsatisfyingCandidatesProvider(): array {
    return [
      'not true' => [$this->getSpecMock(true)],
    ];
  }

  public function remainderUnsatisfiedSpecProvider(): array {
    $spec_true  = $this->getSpecMock(true);
    $spec_false = $this->getSpecMock(false);

    return [
      'not false' => [new Not($spec_false), null],
      'not true'  => [new Not($spec_true), new Not($spec_true)],
    ];
  }

  /**
   * @param mixed $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecIsSatisfied($spec) {
    $spec = new Not($spec);
    $this->assertTrue($spec->isSatisfiedBy(null));
  }

  /**
   * @param mixed $spec
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecIsNotSatisfied($spec) {
    $spec = new Not($spec);
    $this->assertFalse($spec->isSatisfiedBy(null));
  }

  /**
   * @param mixed $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecRemainderSatisfied($spec) {
    $spec    = new Not($spec);
    $remains = $spec->remainderUnsatisfiedBy(null);
    $this->assertNull($remains);
  }

  /**
   * @param Not   $spec
   * @param mixed $expected
   *
   * @dataProvider remainderUnsatisfiedSpecProvider
   */
  public function testSpecRemainderUnsatisfied(Not $spec, $expected) {
    $remains = $spec->remainderUnsatisfiedBy(null);
    $this->assertEquals($expected, $remains);
  }

  /**
   * @param mixed $spec
   *
   * @dataProvider satisfyingCandidatesProvider
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecGetViolation($spec) {
    $spec      = new Not($spec);
    $violation = $spec->toViolation(true);

    $this->assertInstanceOf(SpecificationViolation::class, $violation);
    $this->assertEquals(Not::class, $violation->getType());
  }
}
