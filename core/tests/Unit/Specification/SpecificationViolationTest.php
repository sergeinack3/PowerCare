<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Specification;

use Ox\Core\Specification\SpecificationViolation;
use Ox\Tests\OxUnitTestCase;

/**
 * Class SpecificationViolationTest
 *
 * @group schedules
 */
class SpecificationViolationTest extends OxUnitTestCase {
  use SpecTestTrait;

  public function testViolationType() {
    $spec = $this->getSpecMock(true);

    $violation = new SpecificationViolation($spec);
    $this->assertSame(get_class($spec), $violation->getType());

    $violation = SpecificationViolation::create($spec, 'message');
    $this->assertSame(get_class($spec), $violation->getType());

    $violation = SpecificationViolation::create($spec, $violation);
    $this->assertSame(get_class($spec), $violation->getType());
  }

  public function testViolationToString() {
    $spec = $this->getSpecMock(true);

    $violation = new SpecificationViolation($spec);
    $violation->add('test 1');
    $violation->add('test 2');

    $this->assertSame('test 1 test 2', $violation->__toString());
  }

  // Todo: Make __toString and toArray consistent
//  public function testViolationToArray() {
//
//  }
}
