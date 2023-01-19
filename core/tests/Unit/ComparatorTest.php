<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\Comparator;
use Ox\Core\ComparatorException;
use Ox\Core\IComparator;
use Ox\Tests\OxUnitTestCase;

class ComparatorTest extends OxUnitTestCase
{
    /**
     * @throws ComparatorException
     */
    public function testExecuteStrategy(): void
    {
        $strategy = new class implements IComparator {
            /** @inheritDoc */
            public function equals($a, $b): bool
            {
                return true;
            }
        };

        $comparator = new Comparator($strategy);
        $this->assertTrue($comparator->executeStrategy('var', 'var'));
    }

    public function testCompare(): void
    {
        $strategy = new class implements IComparator {
            /** @inheritDoc */
            public function equals($a, $b): bool
            {
                return true;
            }
        };

        $this->assertTrue(Comparator::compare($strategy, 'var', 'var'));
    }
}
