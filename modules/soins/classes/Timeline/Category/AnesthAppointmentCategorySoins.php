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
 * Class AnesthAppointmentCategorySoins
 */
class AnesthAppointmentCategorySoins extends TimelineCategory implements ITimelineCategory
{
    use AlsoStayInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        $this->stay->loadRefsConsultAnesth();
        if ($this->stay->_ref_consult_anesth->_id) {
            $anesth_appointment = $this->stay->_ref_consult_anesth;
            $anesth_appointment->loadRefChir()->loadRefFunction();
            $anesth_appointment->loadRefConsultation();
            $anesth_appointment->_ref_consultation->loadRefPlageConsult();
            $anesth_appointment->_ref_consultation->countDocItems();

            if ($this->selectedPractitioner($anesth_appointment->_ref_chir)) {
                $dates_list = $this->makeListDates($anesth_appointment->_ref_consultation->_datetime);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'anesth_appointments', $anesth_appointment);

                $this->addToInvolvedUser($anesth_appointment->_ref_chir);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
