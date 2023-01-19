<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Maternite\CGrossesse;

CCanDo::checkRead();

$date = CView::request("date", "date default|" . CMbDT::date());

CView::checkin();
CView::enforceSlave();

$date_max = CMbDT::date("+2 month", $date);
$group    = CGroups::loadCurrent();

$grossesse = new CGrossesse();

$where = array();

$where["terme_prevu"] = "BETWEEN '$date' AND '$date_max'";
$where["group_id"]    = "= '$group->_id'";

$grossesses = $grossesse->loadList($where);

$sejours = CStoredObject::massLoadBackRefs($grossesses, "sejours", "entree_prevue DESC");
CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC");

$fiches_anesth = array();

$params = array(
  "dossier_anesth_id" => "",
  "operation_id"      => "",
  "offline"           => 1,
  "print"             => 1,
  "pdf"               => 0
);

/** @var CGrossesse $_grossesse */
foreach ($grossesses as $_grossesse) {
  foreach ($_grossesse->loadRefsConsultations() as $_consult) {
    foreach ($_consult->loadRefsDossiersAnesth() as $_dossier_anesth) {
      $params["dossier_anesth_id"]          = $_dossier_anesth->_id;
      $fiches_anesth[$_dossier_anesth->_id] = CApp::fetch("dPcabinet", "print_fiche", $params);
    }
  }
}

$smarty = new CSmartyDP();

$smarty->assign("fiches_anesth", $fiches_anesth);

$smarty->display("offline_print_fiches.tpl");