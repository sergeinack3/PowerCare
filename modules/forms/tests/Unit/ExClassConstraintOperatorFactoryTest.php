<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Tests\Unit;

use Ox\Core\CMbException;
use Ox\Mediboard\Forms\Constraints\ExClassConstraintAndOperator;
use Ox\Mediboard\Forms\Constraints\ExClassConstraintOperatorFactory;
use Ox\Mediboard\Forms\Constraints\ExClassConstraintOrOperator;
use Ox\Tests\OxUnitTestCase;
use Throwable;

class ExClassConstraintOperatorFactoryTest extends OxUnitTestCase
{
    public function getOperators(): array
    {
        return [
            'and'          => ['and', ExClassConstraintAndOperator::class],
            'or'           => ['or', ExClassConstraintOrOperator::class],
            'OR'           => ['OR', CMbException::class],
            'AND'          => ['AND', CMbException::class],
            'empty string' => ['', CMbException::class],
            'integer'      => [123, CMbException::class],
            'null'         => [null, Throwable::class],
        ];
    }

    /**
     * @dataProvider getOperators
     *
     * @param mixed  $operator
     * @param string $class_type
     *
     * @throws CMbException
     */
    public function testCreate($operator, string $class_type): void
    {
        if (is_a($class_type, Throwable::class, true)) {
            $this->expectException($class_type);
        }

        $operator = ExClassConstraintOperatorFactory::create($operator);
        $this->assertInstanceOf($class_type, $operator);
    }
}
