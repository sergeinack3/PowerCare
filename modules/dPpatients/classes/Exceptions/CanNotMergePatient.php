<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Exceptions;

use Ox\Core\Exceptions\CanNotMerge;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Description
 */
class CanNotMergePatient extends CanNotMerge
{
    /**
     * @param CSejour $sejour1
     * @param CSejour $sejour2
     *
     * @return static
     */
    public static function patientVenueConflict(CSejour $sejour1, CSejour $sejour2): self
    {
        return new static(
            'CPatient-merge-warning-venue-conflict',
            $sejour1->_view,
            $sejour2->_view
        );
    }
}
