<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite\Timeline\Category;

use Ox\Mediboard\Maternite\Timeline\AlsoPregnancyInCategory;
use Ox\Mediboard\Soins\Timeline\AlsoStayInCategory;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineCategory;

/**
 * Class FormCategoryMaternite
 */
class FormCategoryMaternite extends TimelineCategory implements ITimelineCategory
{
    use AlsoPregnancyInCategory;
    use AlsoStayInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        foreach ($this->pregnancy->loadRefsForms() as $_form) {
            $_form->loadTargetObject();
            $_form->loadRefExObject();
            $_form->_ref_ex_object->loadRefOwner();
            $_form->_ref_ex_object->loadRefExClass();

            if ($this->selectedPractitioner($_form->_ref_ex_object->_ref_owner)) {
                $this->addToInvolvedUser($_form->_ref_ex_object->_ref_owner);

                $dates_list = $this->makeListDates($_form->datetime_create);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'forms', $_form);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
