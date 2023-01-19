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
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\MondialSante\CMondialSanteAccount;
use Ox\Mediboard\MondialSante\CMondialSanteMessage;
use Ox\Mediboard\Mssante\CMSSanteCDADocument;
use Ox\Mediboard\Mssante\CMSSanteUserAccount;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineCategory;

/**
 * Class LabCategoryConsultation
 */
class LabCategoryConsultation extends TimelineCategory implements ITimelineCategory
{
    /** @var string[] */
    private $active_modules;
    /** @var CMediusers */
    private $selected_practitioner;

    /**
     * @param string[] $active_modules
     *
     * @return void
     */
    public function setActiveModules(array $active_modules): void
    {
        $this->active_modules = $active_modules;
    }

    /**
     * The user used to get lab results
     *
     * @param CMediusers $user
     *
     * @return void
     */
    public function setSelectedPractitioner(CMediusers $user): void
    {
        $this->selected_practitioner = $user;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getEventsByDate(): array
    {
        if (in_array("mondialSante", $this->active_modules)) {
            $account = CMondialSanteAccount::getAccountFor($this->selected_practitioner);
            if ($account->_id) {
                $messages = CMondialSanteMessage::loadFor($account, ['patient_id' => " = '{$this->patient->_id}'"]);

                CStoredObject::massLoadFwdRef($messages, "attachment_id");
                CStoredObject::massLoadFwdRef($messages, "content_id");
                foreach ($messages as $message) {
                    $message->loadAttachment();
                    $message->loadContent();
                    $message->updateFormFields();

                    [$year, $month, $day] = $this->makeListDates($message->datetime);
                    $this->appendTimeline($year, $month, $day, "lab", $message);
                    $this->incrementAmountEvents();

                    if (!$message->read) {
                        $this->incrementAmountEvents();
                    }
                }
            }
        }

        if (in_array("mssante", $this->active_modules)) {
            $account = CMSSanteUserAccount::getAccountFor($this->selected_practitioner);

            if ($account->_id) {
                $reports = CMSSanteCDADocument::getFor($account, $this->patient);
                foreach ($reports as $report) {
                    $report->loadFile();
                    $message = $report->loadMessage();

                    [$year, $month, $day] = $this->makeListDates($message->date);
                    $this->appendTimeline($year, $month, $day, "lab", $report);
                    $this->incrementAmountEvents();
                }
            }
        }

        return $this->getTimeline();
    }

    public function getEventsByDateTime(): array {}
}
