<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Timeline\Category;


use Exception;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineCategory;

/**
 * Class AppointmentCategoryConsultation
 */
class AppointmentCategoryConsultation extends TimelineCategory implements ITimelineCategory
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getEventsByDate(): array
    {
        $appointments = $this->patient->loadRefsConsultations();
        CStoredObject::massLoadFwdRef($appointments, "categorie_id");
        $plages = CStoredObject::massLoadFwdRef($appointments, "plageconsult_id");
        $users = CStoredObject::massLoadFwdRef($plages, "chir_id");
        CStoredObject::massLoadFwdRef($users, "function_id");

        foreach ($appointments as $_appointment) {
            $_appointment->loadRefConsultAnesth();

            if ($_appointment->_ref_consult_anesth->_id) {
                continue;
            }

            if ($this->selectedPractitioner($_appointment->loadRefPraticien())) {
                $_appointment->loadRefCategorie();
                $_appointment->_ref_chir->loadRefFunction();
                $_appointment->countDocItems();
                $_appointment->loadRefPatient();
                $_appointment->_ref_categorie->getSessionOrder($_appointment->patient_id);

                list($year, $month, $day) = $this->makeListDates($_appointment->_date);

                $this->appendTimeline($year, $month, $day, "appointments", $_appointment);
                $this->incrementAmountEvents();
                $this->addToInvolvedUser($_appointment->_ref_chir);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
