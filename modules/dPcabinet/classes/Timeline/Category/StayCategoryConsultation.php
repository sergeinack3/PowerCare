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
 * Class StayCategoryConsultation
 */
class StayCategoryConsultation extends TimelineCategory implements ITimelineCategory
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getEventsByDate(): array
    {
        $stays = $this->patient->loadRefsSejours();

        foreach ($stays as $_stay) {
            if ($this->selectedPractitioner(null)) {
                $_stay->loadRefModeEntree();
                $_stay->loadRefModeSortie();

                // Get the admissions of each stay
                if ($_stay->entree_reelle) {
                    $dates_list = $this->makeListDates($_stay->entree_reelle);
                    $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'arrived', $_stay);
                } elseif ($_stay->entree_prevue) {
                    $dates_list = $this->makeListDates($_stay->entree_prevue);
                    $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'arrived', $_stay);
                }

                // Get the leaving of each stay
                if ($_stay->sortie_reelle) {
                    $_stay->loadRefModeSortie();
                    $dates_list = $this->makeListDates($_stay->sortie_reelle);
                    $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'left', $_stay);
                } elseif ($_stay->sortie_prevue) {
                    $dates_list = $this->makeListDates($_stay->sortie_prevue);
                    $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'left', $_stay);
                }
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
