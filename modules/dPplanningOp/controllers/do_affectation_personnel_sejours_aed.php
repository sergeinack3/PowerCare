<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

//Récupération des paramètres
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CUserSejour;

$ids_sejour    = CView::post("ids_sejour", "str");
$ids_personnel = CView::post("ids_personnel", "str");
$debut         = CView::post("debut", "dateTime");
$fin           = CView::post("fin", "dateTime");
CView::checkin();

$ids_sejour    = explode('|', $ids_sejour);
$ids_personnel = explode('|', $ids_personnel);
foreach ($ids_sejour as $_id_sejour) {
  foreach ($ids_personnel as $_id_personnel) {
    $user = new CUserSejour();
    $user->sejour_id = $_id_sejour;
    $user->user_id   = $_id_personnel;
    $user->debut     = $debut;
    $user->fin       = $fin;
    if ($msg = $user->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
  }
}

echo CAppUI::getMsg();
CApp::rip();