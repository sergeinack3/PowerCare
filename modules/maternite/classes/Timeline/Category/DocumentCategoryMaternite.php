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
 * Class DocumentCategoryMaternite
 */
class DocumentCategoryMaternite extends TimelineCategory implements ITimelineCategory
{
    use AlsoPregnancyInCategory;

    public function getEventsByDate(): array
    {
        $stays = $this->pregnancy->loadRefsSejours();
        $all_docs = [];

        // Get stays documents
        foreach ($stays as $_stay) {
            $_stay->loadRefsDocs();
            $all_docs += $_stay->_ref_documents ?? [];

            // Get surgeries documents
            foreach ($_stay->loadRefsOperations() as $_surgery) {
                $_surgery->loadRefsDocs();
                $all_docs += $_surgery->_ref_documents ?? [];
            }
        }

        // Get appointments documents
        foreach ($this->pregnancy->loadRefsConsultations(true) as $_appointment) {
            $_appointment->loadRefsDocs();
            $all_docs += $_appointment->_ref_documents ?? [];
        }

        // Get pregnancy documents
        $this->pregnancy->loadRefsDocs();
        $all_docs += $this->pregnancy->_ref_documents ?? [];

        // Get patient documents
        $this->patient->loadRefsDocs();
        $all_docs += $this->patient->_ref_documents ?? [];

        foreach ($all_docs as $_document) {
            $_document->loadRefAuthor();

            if ($this->selectedPractitioner($_document->_ref_author)) {
                $this->addToInvolvedUser($_document->_ref_author);

                $dates_list = $this->makeListDates($_document->creation_date);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'documents', $_document);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
