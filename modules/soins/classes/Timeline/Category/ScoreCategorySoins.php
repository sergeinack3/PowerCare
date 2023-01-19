<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Timeline\Category;

use Ox\Mediboard\Cabinet\CExamIgs;
use Ox\Mediboard\Soins\CChungScore;
use Ox\Mediboard\Soins\Timeline\AlsoStayInCategory;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineCategory;

/**
 * Class ScoreCategorySoins
 */
class ScoreCategorySoins extends TimelineCategory implements ITimelineCategory
{
    use AlsoStayInCategory;

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        if ($this->selectedPractitioner(null)) {
            // Score IGS
            /** @var CExamIgs[] $igs_scores */
            $igs_scores = $this->stay->loadBackRefs('exams_igs');
            foreach ($igs_scores as $_score) {
                $dates_list = $this->makeListDates($_score->date);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'score', $_score);
            }

            // Score Chung
            /** @var CChungScore[] $chung_scores */
            $chung_scores = $this->stay->loadBackRefs('chung_scores');
            foreach ($chung_scores as $_score) {
                $dates_list = $this->makeListDates($_score->datetime);
                $this->appendTimeline($dates_list[0], $dates_list[1], $dates_list[2], 'score', $_score);
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
