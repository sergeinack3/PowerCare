<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Constraints;

use Ox\Core\CMbObject;
use Ox\Mediboard\Forms\Exceptions\CouldNotEvaluateExpression;
use Ox\Mediboard\System\Forms\CExClassConstraint;
use Ox\Mediboard\System\Forms\CExClassMandatoryConstraint;

/**
 * Description
 */
interface ExClassConstraintOperatorInterface
{
    /**
     * Tell if given constraints are satisfied according to given object
     *
     * @param CMbObject                                          $object
     * @param CExClassConstraint[]|CExClassMandatoryConstraint[] $constraints
     *
     * @return bool
     * @throws CouldNotEvaluateExpression
     */
    public function checkConstraints(CMbObject $object, array $constraints): bool;
}
