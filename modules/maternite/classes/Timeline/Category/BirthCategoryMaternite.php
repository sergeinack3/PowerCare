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
 * Class BirthCategoryMaternite
 */
class BirthCategoryMaternite extends TimelineCategory implements ITimelineCategory
{
    use AlsoPregnancyInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        if ($this->selectedPractitioner(null)) {
            $births = $this->pregnancy->loadRefsNaissances();
            foreach ($births as $_birth) {
                $_birth->loadRefOperation();
                $_birth->loadRefSejourEnfant()->loadRefPatient();
                $_birth->loadRefSejourEnfant()->loadRefPraticien();
                $_birth->loadRefConstantesNouveauNe();

                $dates_list = $this->makeListDates($_birth->date_time);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'birth', $_birth);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
