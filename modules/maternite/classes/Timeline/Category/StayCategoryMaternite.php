<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite\Timeline\Category;

use Ox\Mediboard\Maternite\Timeline\AlsoPregnancyInCategory;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineCategory;

/**
 * Class StayCategoryMaternite
 */
class StayCategoryMaternite extends TimelineCategory implements ITimelineCategory
{
    use AlsoPregnancyInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        $stays = $this->pregnancy->loadRefsSejours();

        foreach ($stays as $_stay) {
            $_stay->loadRefPraticien();

            if ($this->selectedPractitioner($_stay->_ref_praticien)) {
                $dates_list = $this->makeListDates($_stay->entree);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'stays', $_stay);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
