<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Specification;

use Ox\Core\Specification\SpecificationViolation;
use Ox\Core\Specification\XorX;
use Ox\Tests\OxUnitTestCase;

/**
 * Class XorXTest
 *
 * @group schedules
 */
class XorXTest extends OxUnitTestCase {
  use SpecTestTrait;

  public function satisfyingCandidatesProvider(): array {
    return [
      'true'         => [$this->getSpecMock(true)],
      'true ^ false' => [$this->getSpecMock(true), $this->getSpecMock(false)],
      'false ^ true' => [$this->getSpecMock(false), $this->getSpecMock(true)],
    ];
  }

  public function unsatisfyingCandidatesProvider(): array {
    return [
      'false'         => [$this->getSpecMock(false)],
      'true ^ true'   => [$this->getSpecMock(true), $this->getSpecMock(true)],
      'false ^ false' => [$this->getSpecMock(false), $this->getSpecMock(false)],
    ];
  }

  public function remainderUnsatisfiedSpecProvider(): array {
    $spec_true  = $this->getSpecMock(true);
    $spec_false = $this->getSpecMock(false);

    $true_xor_true   = new XorX($spec_true, $spec_true);
    $true_xor_false  = new XorX($spec_true, $spec_false);
    $false_xor_true  = new XorX($spec_false, $spec_true);
    $false_xor_false = new XorX($spec_false, $spec_false);

    return [
      'true'          => [new XorX($spec_true), null],
      'false'         => [new XorX($spec_false), $spec_false],
      'true ^ false'  => [$true_xor_false, null],
      'false ^ true'  => [$false_xor_true, null],
      'true ^ true'   => [$true_xor_true, $true_xor_true],
      'false ^ false' => [$false_xor_false, $false_xor_false],
    ];
  }

  /**
   * @param mixed ...$specs
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecIsSatisfied(...$specs) {
    $spec = new XorX(...$specs);
    $this->assertTrue($spec->isSatisfiedBy(null));
  }

  /**
   * @param mixed ...$specs
   *
   * @dataProvider unsatisfyingCandidatesProvider
   */
  public function testSpecIsNotSatisfied(...$specs) {
    $spec = new XorX(...$specs);
    $this->assertFalse($spec->isSatisfiedBy(null));
  }

  /**
   * @param mixed ...$specs
   *
   * @dataProvider satisfyingCandidatesProvider
   */
  public function testSpecRemainderSatisfied(...$specs) {
    $spec    = new XorX(...$specs);
    $remains = $spec->remainderUnsatisfiedBy(null);
    $this->assertNull($remains);
  }

  /**
   * @param XorX  $spec
   * @param mixed $expected
   *
   * @dataProvider remainderUnsatisfiedSpecProvider
   */
  public function testSpecRemainderUnsatisfied(XorX $spec, $expected) {
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
    $spec      = new XorX(...$specs);
    $violation = $spec->toViolation(true);

    $this->assertInstanceOf(SpecificationViolation::class, $violation);
    $this->assertEquals(XorX::class, $violation->getType());
  }
}
