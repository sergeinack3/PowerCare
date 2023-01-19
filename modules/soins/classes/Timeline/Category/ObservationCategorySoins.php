<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Timeline\Category;

use Ox\Mediboard\Soins\Timeline\AlsoStayInCategory;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineCategory;

/**
 * Class ObservationCategorySoins
 */
class ObservationCategorySoins extends TimelineCategory implements ITimelineCategory
{
    use AlsoStayInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        $this->stay->loadRefsObservations();
        foreach ($this->stay->_ref_observations as $_observation) {
            $_observation->loadRefUser();

            if ($this->selectedPractitioner($_observation->_ref_user)) {
                $dates_list = $this->makeListDates($_observation->date);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'observations', $_observation);
                $this->addToInvolvedUser($_observation->_ref_user);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
