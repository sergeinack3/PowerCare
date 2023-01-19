<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CSocketBasedServer;
use Ox\Core\CView;
use Ox\Interop\Dicom\CDicomServer;
use Ox\Interop\Hl7\CMLLPServer;

$port       = CView::get("port", "num");
$type       = CView::get("type", "str");
$action     = CView::get("action", "str");
$uid        = CView::get("uid", "str");
$process_id = CView::get("process_id", "num");
CView::checkin();

if (!$port) {
    CAppUI::stepAjax("No port specified", UI_MSG_ERROR);
}

switch ($action) {
    case "stop" :
    case "restart":
        try {
            CSocketBasedServer::send("localhost", $port, "__" . strtoupper($action) . "__\n");
            CAppUI::displayAjaxMsg("Serveur $type : '$action' ");
        } catch (Exception $e) {
            CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
        }
        break;

    case "test" :
        try {
            $server_class = '';
            switch ($type) {
                case "Dicom" :
                    $server_class = CDicomServer::class;
                    break;
                case "MLLP" :
                    $server_class = CMLLPServer::class;
                    break;
                default :
                    return;
            }
            $response = CSocketBasedServer::send("localhost", $port, $server_class::sampleMessage());
            echo "<pre class='er7'>$response</pre>";

            return;
        } catch (Exception $e) {
            CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
        }
        break;

    case "stats":
        try {
            CApp::log(
                'EAI server action',
                json_decode(CSocketBasedServer::send("localhost", $port, "__" . strtoupper($action) . "__\n"), true)
            );
        } catch (Exception $e) {
            CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
        }

        return;

    default:
        CAppUI::stepAjax("Unknown command '$action'", UI_MSG_ERROR);
}

echo CAppUI::getMsg();
