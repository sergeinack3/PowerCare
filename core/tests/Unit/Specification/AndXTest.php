<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Specification;

use Ox\Core\Specification\AndX;
use Ox\Core\Specification\OrX;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Tests\OxUnitTestCase;

/**
 * Class AndXTest
 *
 * @group schedules
 */
class AndXTest extends OxUnitTestCase {
  use SpecTestTrait;

  public function satisfyingCandidatesProvider(): array {
    return [
      'true'         => [$this->getSpecMock(true)],
      'true && true' => [$this->getSpecMock(true), $this->getSpecMock(true)],
    ];
  }

  public function unsatisfyingCandidatesProvider(): array {
    return [
      'false'          => [$this->getSpecMock(false)],
      'false && false' => [$this->getSpecMock(false), $this->getSpecMock(false)],
      'false && true'  => [$this->getSpecMock(false), $this->getSpecMock(true)],
      'true && false'  => [$this->getSpecMock(true), $this->getSpecMock(false)],
    ];
  }

  public function remainderUnsatisfiedSpecProvider(): array {
    $spec_true  = $this->getSpecMock(true);
    $spec_false = $this->getSpecMock(false);

    $true_and_true   = new AndX($spec_true, $spec_true);
    $true_and_false  = new AndX($spec_true, $spec_false);
    $false_and_true  = new AndX($spec_false, $spec_true);
    $false_and_false = new AndX($spec_false, $spec_false);

    $true_or_true   = new OrX($spec_true, $spec_true);
    $true_or_false  = new OrX($spec_true, $spec_false);
    $false_or_true  = new OrX($spec_false, $spec_true);
    $false_or_false = new OrX($spec_false, $spec_false);

    return [
      'true'                     => [new AndX($spec_true), null],
      'false'                    => [new AndX($spec_false), $spec_false],
      'true && false'            => [$true_and_false, $spec_false],
      'false && true'            => [$false_and_true, $spec_false],
      'true && true'             => [$true_and_true, null],
      'false && false'           => [$false_and_false, $false_and_false],
      'true && (true && true)'   => [new AndX($spec_true, $true_and_true), null],
      'true && (true && false)'  => [new AndX($spec_true, $true_and_false), $spec_false],
      'true && (false && true)'  => [new AndX($spec_true, $false_and_true), $spec_false],
      'true && (false && false)' => [new AndX($spec_true, $false_and_false), $false_and_false],
      'true && (true || true)'   => [new AndX($spec_true, $true_or_true), null],
      'true && (true || false)'  => [new AndX($spec_true, $true_or_false), null],
      'true && (false || true)'  => [new AndX($spec_true, $false_or_true), null],
      'true && (false || false)' => [new AndX($spec_true, $false_or_false), $false_or_false],
    ];
  }

  /**
   * @param mixed ...$specs
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecIsSatisfied(...$specs) {
    $spec = new AndX(...$specs);
    $this->assertTrue($spec->isSatisfiedBy(null));
  }

  /**
   * @param mixed ...$specs
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecIsNotSatisfied(...$specs) {
    $spec = new AndX(...$specs);
    $this->assertFalse($spec->isSatisfiedBy(null));
  }

  /**
   * @param mixed ...$specs
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecRemainderSatisfied(...$specs) {
    $spec = new AndX(...$specs);

    $remains = $spec->remainderUnsatisfiedBy(null);
    $this->assertNull($remains);
  }

  /**
   * @param AndX  $spec
   * @param mixed $expected
   *
   * @dataProvider remainderUnsatisfiedSpecProvider
   */
  public function testSpecRemainderUnsatisfied(AndX $spec, $expected) {
    $remains = $spec->remainderUnsatisfiedBy(null);
    $this->assertEquals($expected, $remains);
  }

  /**
   * @param mixed ...$specs
   *
   * @dataProvider satisfyingCandidatesProvider
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecGetViolation(...$specs) {
    $spec      = new AndX(...$specs);
    $violation = $spec->toViolation(true);

    $this->assertInstanceOf(SpecificationViolation::class, $violation);
    $this->assertEquals(AndX::class, $violation->getType());
  }
}
