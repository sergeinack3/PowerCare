<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite\Timeline\Category;

use Ox\Core\CMbObject;
use Ox\Mediboard\Maternite\Timeline\AlsoPregnancyInCategory;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineCategory;

/**
 * Class AppointmentCategoryMaternite
 */
class AppointmentCategoryMaternite extends TimelineCategory implements ITimelineCategory
{
    use AlsoPregnancyInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        $appointments = $this->pregnancy->loadRefsConsultations();

        CMbObject::massLoadFwdRef($appointments, 'categorie_id');

        foreach ($appointments as $_appointment) {
            if ($_appointment->loadRefConsultAnesth()->_id) {
                continue;
            }

            $_appointment->loadRefPraticien();
            $_appointment->loadRefCategorie();

            if ($this->selectedPractitioner($_appointment->_ref_chir)) {
                $dates_list = $this->makeListDates($_appointment->_datetime);

                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'appointments', $_appointment);
                $this->addToInvolvedUser($_appointment->_ref_chir);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
