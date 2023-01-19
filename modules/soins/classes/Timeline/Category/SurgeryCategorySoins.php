<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Timeline\Category;

use Ox\Core\CMbObject;
use Ox\Mediboard\Soins\Timeline\AlsoStayInCategory;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineCategory;

/**
 * Class SurgeryCategorySoins
 */
class SurgeryCategorySoins extends TimelineCategory implements ITimelineCategory
{
    use AlsoStayInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        $this->stay->loadRefsOperations();

        $rooms = CMbObject::massLoadFwdRef($this->stay->_ref_operations, 'salle_id');
        CMbObject::massLoadFwdRef($rooms, 'bloc_id');

        $users = CMbObject::massLoadFwdRef($this->stay->_ref_operations, 'chir_id');
        CMbObject::massLoadFwdRef($users, 'function_id');

        foreach ($this->stay->_ref_operations as $_surgery) {
            $_surgery->_ref_sejour = $this->stay;
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

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
