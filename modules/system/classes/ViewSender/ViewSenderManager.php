<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\ViewSender;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CMbDT;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\Logger\LoggerLevels;

/**
 * Manager for CViewSender.
 * Responsible of the execution of the CViewSender at the good time
 */
class ViewSenderManager
{
    public const SHUTDOWN_CLEAR_FUNCTION = [CViewSender::class, 'clearRemainingFiles'];

    /** @var bool */
    private $export;

    /** @var string */
    private $cookie;

    /** @var string */
    private $current_datetime;

    /** @var int */
    private $minute;

    /** @var int */
    private $hour;

    /** @var int */
    private $day;

    /** @var CViewSender[] */
    private $view_senders = [];

    /** @var ViewSenderExecution[] */
    private $senders_executions = [];

    private $multi_curl;

    public function __construct(bool $export, string $current_datetime = null)
    {
        $this->export = $export;

        $this->init($current_datetime);
    }

    /**
     * Prepare the multi curl, the timings and register a shutdown function to clean remaining files on FS.
     */
    private function init(string $current_datetime = null): void
    {
        $this->current_datetime = $current_datetime ?? CMbDT::dateTime();

        $this->minute    = intval(CMbDT::transform($this->current_datetime, null, "%M"));
        $this->hour      = intval(CMbDT::transform($this->current_datetime, null, "%H"));
        $this->day       = intval(CMbDT::transform($this->current_datetime, null, "%d"));

        $this->cookie = session_name() . "=" . session_id();

        $this->multi_curl = curl_multi_init();

        $this->registerShutDownClearFiles();
    }

    /**
     * Prepare the senders and execute them if "export" is set to true.
     *
     * @return CViewSender[]
     *
     * @throws Exception
     */
    public function prepareAndSend(): array
    {
        if ($this->prepareSenders() === 0) {
            return [];
        }

        if ($this->export) {
            $this->executeSenders();
        }

        return $this->view_senders;
    }

    /**
     * Execute the multi curl until every curl is over.
     * Reload the handled CViewSender to have their updated data.
     */
    private function executeSenders(): void
    {
        $this->executeCurl();

        // Reset the CViewSender array
        $this->view_senders = [];

        // Foreach ViewSenderExecution remove the handle from multi curl and reload the sender.
        foreach ($this->senders_executions as $execution) {
            $this->removeCurlHandle($execution->getHandle());

            try {
                // Need to reload the CViewSender to get the infos
                $this->view_senders[] = $execution->reloadSender();
            } catch (CMbModelNotFoundException $e) {
                CApp::log($e->getMessage(), $e, LoggerLevels::LEVEL_ERROR);
            }
        }

        // Close the multi curl handle
        $this->closeCurl();
    }

    /**
     * Load the active senders and check for each one if it must be launch at the current minute.
     * If "export" is true init the ViewSenderExecution for each CViewSender that have to be launch.
     *
     * @throws Exception
     */
    private function prepareSenders(): int
    {
        $senders = $this->loadActiveSenders();

        foreach ($senders as $sender) {
            // Check senders that have to be send at the current time
            if ($sender->getActive($this->minute, $this->hour, $this->day)) {
                $this->view_senders[] = $sender;

                if ($this->export) {
                    $this->senders_executions[] = $this->initSenderExecution($sender);
                }
            }
        }

        return count($this->view_senders);
    }

    /**
     * Init the ViewSenderExecution and add its handle to the curl_mtuli
     */
    private function initSenderExecution(CViewSender $sender): ViewSenderExecution
    {
        $execution = new ViewSenderExecution($sender);
        $execution->init($this->cookie);

        curl_multi_add_handle($this->multi_curl, $execution->getHandle());

        return $execution;
    }

    /**
     * Load the active senders (order by name for the display)
     *
     * @return CViewSender[]
     *
     * @throws Exception
     */
    protected function loadActiveSenders(): array
    {
        $sender = new CViewSender();
        $sender->active = '1';

        return $sender->loadMatchingListEsc('name');
    }

    private function closeCurl(): void
    {
        curl_multi_close($this->multi_curl);
    }

    /**
     * Loop until all the curl are over
     */
    protected function executeCurl(): void
    {
        do {
            curl_multi_exec($this->multi_curl, $running);
        } while($running);
    }

    protected function removeCurlHandle($handle): void
    {
        curl_multi_remove_handle($this->multi_curl, $handle);
    }

    protected function registerShutDownClearFiles(): void
    {
        CApp::registerShutdown(self::SHUTDOWN_CLEAR_FUNCTION);
    }

    public function getCurrentDateTime(): string
    {
        return $this->current_datetime;
    }

    public function getMinute(): int
    {
        return $this->minute;
    }
}
