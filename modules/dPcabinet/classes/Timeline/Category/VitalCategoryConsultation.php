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
 * Class VitalCategoryConsultation
 */
class VitalCategoryConsultation extends TimelineCategory implements ITimelineCategory
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getEventsByDate(): array
    {
        $this->patient->loadRefsConstantesMedicales();

        foreach ($this->patient->_refs_all_contantes_medicales as $_constant) {
            if ($this->selectedPractitioner($_constant->loadRefUser())) {
                $_constant->getValuedConstantes();
                list($year, $month, $day) = $this->makeListDates($_constant->datetime);

                $this->appendTimeline($year, $month, $day, "vitals", $_constant);
                $this->incrementAmountEvents();
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
