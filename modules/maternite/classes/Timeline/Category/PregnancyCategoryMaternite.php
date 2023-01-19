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
 * Class PregnancyCategoryMaternite
 */
class PregnancyCategoryMaternite extends TimelineCategory implements ITimelineCategory
{
    use AlsoPregnancyInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        if ($this->selectedPractitioner(null)) {
            if (!$this->pregnancy->date_dernieres_regles) {
                return [];
            }

            $dates_list = $this->makeListDates($this->pregnancy->date_dernieres_regles);
            $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'pregnancy', $this->pregnancy);
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
