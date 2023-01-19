<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Timeline\Category;

use Ox\Mediboard\Cabinet\CExamIgs;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Soins\CChungScore;
use Ox\Mediboard\Soins\Timeline\AlsoStayInCategory;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineCategory;

/**
 * Class VitalCategorySoins
 */
class VitalCategorySoins extends TimelineCategory implements ITimelineCategory
{
    use AlsoStayInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        /** @var CConstantesMedicales[] $vitals */
        $vitals = $this->stay->loadBackRefs('contextes_constante');
        foreach ($vitals as $_vitals) {
            $_vitals->loadRefUser()->loadRefFunction();
            $_vitals->getValuedConstantes();

            if ($this->selectedPractitioner($_vitals->_ref_user)) {
                $dates_list = $this->makeListDates($_vitals->datetime);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'vitals', $_vitals);
                $this->addToInvolvedUser($_vitals->_ref_user);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
