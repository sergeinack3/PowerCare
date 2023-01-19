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
 * Class ExpectedTermCategoryMaternite
 */
class ExpectedTermCategoryMaternite extends TimelineCategory implements ITimelineCategory
{
    use AlsoPregnancyInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        if ($this->selectedPractitioner(null)) {
            $expected_term = $this->pregnancy->terme_prevu;
            if ($expected_term) {
                $dates_list = $this->makeListDates($expected_term);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'expected_term', $this->pregnancy);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
