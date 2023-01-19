<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Core\CHTTPClient;
use Ox\Core\CMbArray;

/**
 * HTTP exchange client
 */
class CExchangeHTTPClient extends CHTTPClient {
  public $loggable;
  /** @var Chronometer */
  public $chrono;
  /** @var CExchangeHTTP */
  public $exchange_http;

  /** @var CSourceHTTP */
  public $_source;

  /**
   * @inheritdoc
   */
    function onBeforeRequest()
    {
        $this->_source->startCallTrace();
        if (!$this->loggable) {
            return;
        }

        $exchange_http = new CExchangeHTTP();

        $exchange_http->date_echange  = "now";
        $exchange_http->emetteur      = CAppUI::conf("mb_id");
        $exchange_http->function_name = $this->request_type;
        $exchange_http->source_class  = $this->_source->_class;
        $exchange_http->source_id     = $this->_source->_id;

        switch (strtolower($this->request_type)) {
            default:
            case "post":
                $exchange_http->destinataire = $this->url;
                $exchange_http->input        = serialize($this->option);
                break;

            case "get":
                $parts                       = explode("?", $this->url, 2);
                $exchange_http->destinataire = $parts[0];
                $exchange_http->input        = serialize(CMbArray::get($parts, 1));
                break;
        }
        $exchange_http->store();

        $this->exchange_http = $exchange_http;

        CApp::$chrono->stop();

        $this->chrono = new Chronometer();
        $this->chrono->start();
    }

  /**
   * @inheritdoc
   */
    function onAfterRequest($result)
    {
        $this->_source->stopCallTrace();
        if (!$this->loggable) {
            return;
        }

        $this->chrono->stop();
        CApp::$chrono->start();

        $exchange_http                = $this->exchange_http;
        $exchange_http->date_echange  = "now";
        $exchange_http->response_time = $this->chrono->total;
        $exchange_http->status_code   = null;
        $exchange_http->output        = serialize($result);
        $exchange_http->store();
    }
}
