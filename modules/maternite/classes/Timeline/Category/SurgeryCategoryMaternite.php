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
 * Class SurgeryCategoryMaternite
 */
class SurgeryCategoryMaternite extends TimelineCategory implements ITimelineCategory
{
    use AlsoPregnancyInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        $stays = $this->pregnancy->loadRefsSejours();

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
                $_surgery->countDocItems();

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
