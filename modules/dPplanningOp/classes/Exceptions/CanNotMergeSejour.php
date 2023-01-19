<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp\Exceptions;

use Ox\Core\Exceptions\CanNotMerge;
use Ox\Mediboard\Hospi\CAffectation;

/**
 * Description
 */
class CanNotMergeSejour extends CanNotMerge
{
    public static function sejourNotAllowed(): self
    {
        return new static('CSejour-merge-warning-Not allowed');
    }

    public static function multipleAffectations(): self
    {
        return new static('CSejour-merge-warning-Multiple affectations');
    }

    /**
     * @param CAffectation $affectation1
     * @param CAffectation $affectation2
     *
     * @return static
     */
    public static function affectationsConflict(CAffectation $affectation1, CAffectation $affectation2): self
    {
        $affectation1->updateView();
        $affectation2->updateView();

        return new static(
            'CSejour-merge-warning-affectation-conflict',
            $affectation1->_view,
            $affectation2->_view,
        );
    }
}
