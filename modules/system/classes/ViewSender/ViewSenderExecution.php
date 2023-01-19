<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\ViewSender;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbModelNotFoundException;

/**
 * An execution for a CViewSender.
 * Each execution has a curl handle and a CViewSender
 */
class ViewSenderExecution
{
    public const SEND_VIEW_URL = '/?m=system&tab=ajax_send_view&view_sender_id=%s';

    /** @var CViewSender */
    private $view_sender;

    /** @var resource */
    private $curl_handle;

    public function __construct(CViewSender $sender)
    {
        $this->view_sender = $sender;
    }

    /**
     * Create the curl handle from the url for the current CViewSender.
     * Use the session cookie for the connection.
     */
    public function init(string $session_cookie): void
    {
        $this->curl_handle = curl_init($this->makeUrl());

        $curl_opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIE         => $session_cookie,
        ];

        curl_setopt_array($this->curl_handle, $curl_opts);
    }

    /**
     * @return resource
     */
    public function getHandle()
    {
        return $this->curl_handle;
    }

    public function getSender(): CViewSender
    {
        return $this->view_sender;
    }

    /**
     * Reload the CViewSender with data from the database.
     *
     * @throws CMbModelNotFoundException
     */
    public function reloadSender(): CViewSender
    {
        $this->view_sender = CViewSender::findOrFail($this->view_sender->_id);
        $this->view_sender->loadRefSendersSource();

        return $this->view_sender;
    }

    /**
     * Create the URL to make and send the view for the current CViewSender.
     *
     * @throws Exception
     */
    private function makeUrl(): string
    {
        return rtrim(CAppUI::conf("base_url"), '/') . sprintf(self::SEND_VIEW_URL, $this->view_sender->_id);
    }
}
