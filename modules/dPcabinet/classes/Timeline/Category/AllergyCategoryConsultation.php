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
 * Class AllergyCategoryConsultation
 */
class AllergyCategoryConsultation extends TimelineCategory implements ITimelineCategory
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getEventsByDate(): array
    {
        $antecedents = $this->patient->loadRefDossierMedical()->loadRefsAntecedents();
        CStoredObject::massLoadFwdRef($antecedents, "owner_id");

        foreach ($this->patient->_ref_dossier_medical->_ref_allergies as $_allergy) {
            if ($this->selectedPractitioner($_allergy->loadRefOwner())) {
                $year = "";
                $month = "";
                $day = "undated";
                if ($_allergy->date) {
                    list($year, $month, $day) = $this->makeListDates($_allergy->date);
                }

                $this->appendTimeline($year, $month, $day, "allergies", $_allergy);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
