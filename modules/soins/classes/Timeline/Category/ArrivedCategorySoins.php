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
 * Class ArrivedCategorySoins
 */
class ArrivedCategorySoins extends TimelineCategory implements ITimelineCategory
{
    use AlsoStayInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        if ($this->selectedPractitioner(null)) {
            if ($this->stay->entree_reelle) {
                $this->stay->loadRefModeEntree();
                $dates_list = $this->makeListDates($this->stay->entree_reelle);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'arrived', $this->stay);
            } elseif ($this->stay->entree_prevue) {
                $dates_list = $this->makeListDates($this->stay->entree_prevue);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'arrived', $this->stay);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
