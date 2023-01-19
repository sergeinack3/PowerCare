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
 * Class LeftCategorySoins
 */
class LeftCategorySoins extends TimelineCategory implements ITimelineCategory
{
    use AlsoStayInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        if ($this->selectedPractitioner(null)) {
            if ($this->stay->sortie_reelle) {
                $this->stay->loadRefModeSortie();
                $dates_list = $this->makeListDates($this->stay->sortie_reelle);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'left', $this->stay);
            } elseif ($this->stay->sortie_prevue) {
                $dates_list = $this->makeListDates($this->stay->sortie_prevue);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'left', $this->stay);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
