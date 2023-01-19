<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CGestePerop;
use Ox\Mediboard\SalleOp\CProtocoleGestePeropItem;

CCanDo::checkEdit();
$geste_perop_ids          = CView::post("_geste_perop_ids", "str");
$protocole_geste_perop_id = CView::post("protocole_geste_perop_id", "ref class|CProtocoleGestePerop");
CView::checkin();

$gestes_ids = json_decode(stripslashes($geste_perop_ids), true);

$counter      = 0;
$current_user = CMediusers::get();

foreach ($gestes_ids as $_geste_id) {
    $geste = CGestePerop::find($_geste_id);

    if ($geste->_id) {
        $protocole_geste_perop_item                           = new CProtocoleGestePeropItem();
        $protocole_geste_perop_item->protocole_geste_perop_id = $protocole_geste_perop_id;
        $protocole_geste_perop_item->object_class             = $geste->_class;
        $protocole_geste_perop_item->object_id                = $geste->_id;

        if ($msg = $protocole_geste_perop_item->store()) {
            return $msg;
        }
        $counter++;
    }
}

CAppUI::displayMsg($msg, CAppUI::tr("CProtocoleGestePeropItem-msg-create") . " x $counter");

echo CAppUI::getMsg();
CApp::rip();
