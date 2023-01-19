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
 * Class FileCategoryMaternite
 */
class FileCategoryMaternite extends TimelineCategory implements ITimelineCategory
{
    use AlsoPregnancyInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        $stays = $this->pregnancy->loadRefsSejours();
        $all_docs = [];

        // Get stays files
        foreach ($stays as $_stay) {
            $_stay->loadRefsFiles();
            $all_docs += $_stay->_ref_files ?? [];

            // Get surgeries files
            foreach ($_stay->loadRefsOperations() as $_surgery) {
                $_surgery->loadRefsFiles();
                $all_docs += $_surgery->_ref_files ?? [];
            }
        }

        // Get appointments files
        foreach ($this->pregnancy->loadRefsConsultations(true) as $_appointment) {
            $_appointment->loadRefsFiles();
            $all_docs += $_appointment->_ref_files ?? [];
        }

        // Get pregnancy files
        $this->pregnancy->loadRefsFiles();
        $all_docs += $this->pregnancy->_ref_files ?? [];

        // Get patient files
        $this->patient->loadRefsFiles();
        $all_docs += $this->patient->_ref_files ?? [];

        foreach ($all_docs as $_file) {
            $_file->loadRefAuthor();
            $_file->loadTargetObject();

            if ($this->selectedPractitioner($_file->_ref_author)) {
                $this->addToInvolvedUser($_file->_ref_author);

                $dates_list = $this->makeListDates($_file->file_date);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'files', $_file);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
