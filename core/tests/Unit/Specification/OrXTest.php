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
 * Class OrXTest
 *
 * @group schedules
 */
class OrXTest extends OxUnitTestCase {
  use SpecTestTrait;

  public function satisfyingCandidatesProvider(): array {
    return [
      'true'          => [$this->getSpecMock(true)],
      'true || true'  => [$this->getSpecMock(true), $this->getSpecMock(true)],
      'true || false' => [$this->getSpecMock(true), $this->getSpecMock(false)],
      'false || true' => [$this->getSpecMock(false), $this->getSpecMock(true)],
    ];
  }

  public function unsatisfyingCandidatesProvider(): array {
    return [
      'false'          => [$this->getSpecMock(false)],
      'false || false' => [$this->getSpecMock(false), $this->getSpecMock(false)],
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

    $false_or_true_and_true   = new OrX($spec_false, $true_and_true);
    $false_or_true_and_false  = new OrX($spec_false, $true_and_false);
    $false_or_false_and_true  = new OrX($spec_false, $false_and_true);
    $false_or_false_and_false = new OrX($spec_false, $false_and_false);
    $false_or_true_or_true    = new OrX($spec_false, $true_or_true);
    $false_or_true_or_false   = new OrX($spec_false, $true_or_false);
    $false_or_false_or_true   = new OrX($spec_false, $false_or_true);
    $false_or_false_or_false  = new OrX($spec_false, $false_or_false);

    return [
      'true'                      => [new OrX($spec_true), null],
      'false'                     => [new OrX($spec_false), $spec_false],
      'true || false'             => [$true_or_false, null],
      'false || true'             => [$false_or_true, null],
      'true || true'              => [$true_or_true, null],
      'false || false'            => [$false_or_false, $false_or_false],
      'false || (true && true)'   => [$false_or_true_and_true, null],
      'false || (true && false)'  => [$false_or_true_and_false, $false_or_false],
      'false || (false && true)'  => [$false_or_false_and_true, $false_or_false],
      'false || (false && false)' => [$false_or_false_and_false, $false_or_false_and_false],
      'false || (true || true)'   => [$false_or_true_or_true, null],
      'false || (true || false)'  => [$false_or_true_or_false, null],
      'false || (false || true)'  => [$false_or_false_or_true, null],
      'false || (false || false)' => [$false_or_false_or_false, $false_or_false_or_false],
    ];
  }

  /**
   * @param mixed ...$specs
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecIsSatisfied(...$specs) {
    $spec = new OrX(...$specs);
    $this->assertTrue($spec->isSatisfiedBy(null));
  }

  /**
   * @param mixed ...$specs
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecIsNotSatisfied(...$specs) {
    $spec = new OrX(...$specs);
    $this->assertFalse($spec->isSatisfiedBy(null));
  }

  /**
   * @param mixed ...$specs
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecRemainderSatisfied(...$specs) {
    $spec    = new OrX(...$specs);
    $remains = $spec->remainderUnsatisfiedBy(null);
    $this->assertNull($remains);
  }

  /**
   * @param OrX   $spec
   * @param mixed $expected
   *
   * @dataProvider remainderUnsatisfiedSpecProvider
   */
  public function testSpecRemainderUnsatisfied(OrX $spec, $expected) {
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
    $spec      = new OrX(...$specs);
    $violation = $spec->toViolation(true);

    $this->assertInstanceOf(SpecificationViolation::class, $violation);
    $this->assertEquals(OrX::class, $violation->getType());
  }
}
