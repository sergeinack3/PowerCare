<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CExchangeSourceAdvanced;

/**
 * Status exchange source
 */
CCanDo::check();

$source_guid = CView::get("source_guid", "guid class|CExchangeSource");
CView::checkin();

$status = null;
try {
    $source = new CExchangeSource();
    /** @var CExchangeSource $source */
    if ($source_guid) {
        $source = CMbObject::loadFromGuid($source_guid);
        $last_stat = "";
        if($source instanceof CExchangeSourceAdvanced) {
            $last_stat = $source->loadRefLastStatistic();
        }
        if ($last_stat && $last_stat->failures === "0") {
            
            $source->_reachable = 2;
            $source->_response_time = $last_stat->last_response_time;
            $source->_message       = CAppUI::tr("$source->_class-reachable-source", $source->host);
        } else {
           
            $source->_reachable = 0;
          
            if ($last_stat !== false && $last_stat->last_response_time !== null) {
                $response_time = $last_stat->last_response_time;
            } else {
                $response_time = 0;
            }
            $source->_response_time = $response_time;
            $source->_message = CAppUI::tr(
                    "$source->_class.last_status.2",
                    $source->host
                ) . " depuis le " . $last_stat->last_verification_date;
        }
    }

    $status = [
        'type'          => CAppUI::tr($source->_class),
        'active'        => $source->active,
        'reachable'     => $source->_reachable,
        'message'       => $source->_message,
        'name'          => $source->name,
        'response_time' => $source->_response_time > 0 ? $source->_response_time . " ms" : $source->_response_time,
    ];
} catch (CMbException $e) {
    if ($error = $e->getMessage()) {
        CAppUI::stepMessage(UI_MSG_ERROR, $error);
    }
}

if ($source->_message) {
    CAppUI::stepMessage(UI_MSG_ERROR, $source->_message);
}
elseif ($source->retry_strategy !== null) {
    CAppUI::stepMessage(
        UI_MSG_ERROR,
        'CSourceSFTP-connexion-failed',
        "{$source->host}:{$source->port}"
    );
}
CApp::json($status);
