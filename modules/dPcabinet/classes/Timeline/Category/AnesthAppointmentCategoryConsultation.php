<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Timeline\Category;

use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineCategory;

/**
 * Class AnesthAppointmentCategoryConsultation
 */
class AnesthAppointmentCategoryConsultation extends TimelineCategory implements ITimelineCategory
{
    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        $appointments = $this->patient->loadRefsConsultations();
        CStoredObject::massLoadBackRefs($appointments, "consult_anesth");
        CStoredObject::massLoadFwdRef($appointments, "categorie_id");
        $plages = CStoredObject::massLoadFwdRef($appointments, "plageconsult_id");
        $users = CStoredObject::massLoadFwdRef($plages, "chir_id");
        CStoredObject::massLoadFwdRef($users, "function_id");

        foreach ($appointments as $_appointment) {
            $_appointment->loadRefConsultAnesth();
            if (!$_appointment->_ref_consult_anesth->_id) {
                continue;
            }

            $anesth_appointment = $_appointment->_ref_consult_anesth;
            $anesth_appointment->loadRefChir()->loadRefFunction();
            $anesth_appointment->loadRefConsultation();
            $anesth_appointment->_ref_consultation->loadRefPlageConsult();
            $anesth_appointment->_ref_consultation->loadRefCategorie();
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
