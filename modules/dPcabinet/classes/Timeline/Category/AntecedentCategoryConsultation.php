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
 * Class AntecedentCategoryConsultation
 */
class AntecedentCategoryConsultation extends TimelineCategory implements ITimelineCategory
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getEventsByDate(): array
    {
        $antecedents = $this->patient->loadRefDossierMedical()->loadRefsAntecedents();
        CStoredObject::massLoadFwdRef($antecedents, "owner_id");

        foreach ($this->patient->_ref_dossier_medical->_all_antecedents as $_antecedent) {
            if ($_antecedent->type != "alle") {
                if ($this->selectedPractitioner($_antecedent->loadRefOwner())) {
                    if ($_antecedent->creation_date) {
                        list($year, $month, $day) = $this->makeListDates($_antecedent->creation_date);
                        $this->appendTimeline($year, $month, $day, "antecedent", $_antecedent);
                    } elseif (!$_antecedent->creation_date) {
                        $this->appendTimeline("", "", "undated", "antecedent", $_antecedent);
                    }
                }
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
