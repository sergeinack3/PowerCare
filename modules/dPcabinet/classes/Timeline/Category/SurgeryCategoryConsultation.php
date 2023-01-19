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
 * Class SurgeryCategoryConsultation
 */
class SurgeryCategoryConsultation extends TimelineCategory implements ITimelineCategory
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getEventsByDate(): array
    {
        $stays = $this->patient->loadRefsSejours();
        $surgeries = CStoredObject::massLoadBackRefs($stays, "operations");
        CStoredObject::massLoadFwdRef($surgeries, "plageop_id");
        CStoredObject::massLoadFwdRef($surgeries, "salle_id");
        $surgeons = CStoredObject::massLoadFwdRef($surgeries, "chir_id");
        CStoredObject::massLoadFwdRef($surgeons, "function_id");

        foreach ($stays as $_stay) {
            $surgeries = $_stay->loadRefsOperations();

            foreach ($surgeries as $_surgery) {
                $_surgery->_ref_sejour = $_stay;
                $_surgery->loadRefPlageOp();
                $_surgery->updateFormFields();
                $_surgery->loadRefSalle();
                $_surgery->loadRefChir();
                $_surgery->_ref_chir->loadRefFunction();
                $_surgery->loadExtCodesCCAM();

                if ($this->selectedPractitioner($_surgery->_ref_chir)) {
                    $dates_list = $this->makeListDates($_surgery->_datetime_best);
                    $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'surgeries', $_surgery);

                    $this->addToInvolvedUser($_surgery->_ref_chir);
                }
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
