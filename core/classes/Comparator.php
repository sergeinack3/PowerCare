<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Description
 */
class Comparator
{
    /** @var IComparator */
    private $comparator;

    public function __construct(IComparator $comparator)
    {
        $this->comparator = $comparator;
    }

    /**
     * Executes the comparison strategy
     *
     * @param mixed $a The first variable
     * @param mixed $b The second variable to compare to
     *
     * @return bool True if the variables are equals, false otherwise
     * @throws ComparatorException
     */
    public function executeStrategy($a, $b): bool
    {
        return $this->comparator->equals($a, $b);
    }

    /**
     * @param IComparator $comparator
     * @param mixed       $a
     * @param mixed       $b
     *
     * @return bool
     * @throws ComparatorException
     */
    public static function compare(IComparator $comparator, $a, $b): bool
    {
        $comparator = new Comparator($comparator);

        return $comparator->executeStrategy($a, $b);
    }
}
