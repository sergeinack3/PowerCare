<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Timeline\Category;

use Ox\Mediboard\Soins\Timeline\AlsoStayInCategory;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineCategory;

/**
 * Class DocumentCategorySoins
 */
class DocumentCategorySoins extends TimelineCategory implements ITimelineCategory
{
    use AlsoStayInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        $this->stay->loadRefsDocItems(false);
        foreach ($this->stay->_ref_documents as $_document) {
            $_document->loadRefAuthor();
            $_document->loadTargetObject();
            $_document->loadFile();

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
