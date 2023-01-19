<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CView;
use Ox\Mediboard\Stock\CStockMouvement;

$mvts_ids = CView::post("mvts_ids", "str");
CView::checkin();

$mvts_ids  = json_decode(utf8_encode(stripslashes($mvts_ids)), true);

foreach ($mvts_ids as $_mvt_id => $_mvt) {
  if ($_mvt["checked"]) {
    $mouvement = new CStockMouvement();
    $mouvement->load($_mvt_id);
    $mouvement->etat = "realise";
    if ($msg = $mouvement->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::setMsg("CProductMovement-msg-modify", UI_MSG_OK);
    }
  }
}

echo CAppUI::getMsg();
