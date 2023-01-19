<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Tests\Unit;

use Ox\Core\CMbObject;
use Ox\Mediboard\Forms\Constraints\ExClassConstraintAndOperator;
use Ox\Mediboard\Forms\Exceptions\CouldNotEvaluateExpression;
use Ox\Mediboard\System\Forms\CExClassConstraint;
use Ox\Tests\OxUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

class ExClassConstraintAndOperatorTest extends OxUnitTestCase
{
    private function mockConstraint(bool $return_value): MockObject
    {
        $constraints = $this->getMockBuilder(CExClassConstraint::class)
            ->disableOriginalConstructor()
            ->getMock();

        $constraints->method('checkConstraint')->willReturn($return_value);

        return $constraints;
    }

    public function getConstraints(): array
    {
        $object = $this->getMockBuilder(CMbObject::class)->disableOriginalConstructor()->getMock();

        return [
            'true && true'   => [$object, true, [$this->mockConstraint(true), $this->mockConstraint(true)]],
            'false && false' => [$object, false, [$this->mockConstraint(false), $this->mockConstraint(false)]],
            'true && false'  => [$object, false, [$this->mockConstraint(true), $this->mockConstraint(false)]],
            'false && true'  => [$object, false, [$this->mockConstraint(false), $this->mockConstraint(true)]],
            'true'           => [$object, true, [$this->mockConstraint(true)]],
            'false'          => [$object, false, [$this->mockConstraint(false)]],
            'empty'          => [$object, CouldNotEvaluateExpression::class, []],
        ];
    }

    /**
     * @dataProvider getConstraints
     *
     * @param CMbObject $object
     * @param mixed     $expected
     * @param array     $constraints
     *
     * @throws CouldNotEvaluateExpression
     */
    public function testCheckConstraints(CMbObject $object, $expected, array $constraints): void
    {
        if (is_a($expected, Throwable::class, true)) {
            $this->expectException($expected);
        }

        $operator = new ExClassConstraintAndOperator();
        $result   = $operator->checkConstraints($object, $constraints);

        $this->assertEquals($expected, $result);
    }
}
