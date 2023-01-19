<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Constraints;

use Ox\Core\CMbObject;
use Ox\Mediboard\Forms\Exceptions\CouldNotEvaluateExpression;

/**
 * Description
 */
class ExClassConstraintOrOperator implements ExClassConstraintOperatorInterface
{
    /**
     * @inheritDoc
     */
    public function checkConstraints(CMbObject $object, array $constraints): bool
    {
        if (empty($constraints)) {
            throw CouldNotEvaluateExpression::noOperand();
        }

        foreach ($constraints as $_constraint) {
            if ($_constraint->checkConstraint($object)) {
                return true;
            }
        }

        return false;
    }
}
