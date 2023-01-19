<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Constraints;

use Ox\Core\CMbException;

/**
 * Description
 */
abstract class ExClassConstraintOperatorFactory
{
    /**
     * @param string $operator
     *
     * @return ExClassConstraintOperatorInterface
     * @throws CMbException
     */
    public static function create(string $operator): ExClassConstraintOperatorInterface
    {
        switch ($operator) {
            case 'and':
                return new ExClassConstraintAndOperator();

            case 'or':
                return new ExClassConstraintOrOperator();

            default:
                throw new CMbException('common-error-Invalid parameter: %s', $operator);
        }
    }
}
