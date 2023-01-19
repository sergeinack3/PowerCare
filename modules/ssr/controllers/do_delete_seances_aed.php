<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CEvenementSSR;

$evts_to_delete = CView::post("evts_to_delete", "str");
$annule = CView::post("annule", "bool default|0");
CView::checkin();

$evenement_ids = json_decode(utf8_encode(stripslashes($evts_to_delete)), true);
foreach ($evenement_ids as $evt) {
  if ($evt["_checked"]) {
    $evenement = new CEvenementSSR();
    $evenement->load($evt["evt_id"]);
    if ($evenement->_id) {
      if ($annule) {
        $evenement->_traitement = 1;
        $evenement->realise = 0;
        $evenement->annule = 1;
        $msg = $evenement->store();
        CAppUI::displayMsg($msg, "CEvenementSSR-msg-modify");
      }
      else {
        $msg = $evenement->delete();
        CAppUI::displayMsg($msg, "CEvenementSSR-msg-delete");
      }
    }
  }
}

echo CAppUI::getMsg();
CApp::rip();
