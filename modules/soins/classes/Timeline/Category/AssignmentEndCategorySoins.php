<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Timeline\Category;

use Ox\Core\CMbObject;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Soins\Timeline\AlsoStayInCategory;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineCategory;

/**
 * Class AssignmentEndCategorySoins
 */
class AssignmentEndCategorySoins extends TimelineCategory implements ITimelineCategory
{
    use AlsoStayInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        if ($this->selectedPractitioner(null)) {
            $this->stay->loadRefsAffectations();
            CAffectation::massUpdateView($this->stay->_ref_affectations);
            foreach ($this->stay->_ref_affectations as $_assignment) {
                $dates_list = $this->makeListDates($_assignment->sortie);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'assignment_end', $_assignment);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
