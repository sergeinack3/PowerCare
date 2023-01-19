<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Ssr\CPlateauTechnique;
use Ox\Mediboard\Ssr\CTechnicien;

global $m;

CCanDo::checkRead();

$plateau_id    = CView::get("plateau_id", "ref class|CPlateauTechnique");
$technicien_id = CView::get("technicien_id", "ref class|CTechnicien");

CView::checkin();

// Plateau du contexte
$plateau = new CPlateauTechnique();
$plateau->load($plateau_id);

// Détails des techniciens
$date = CMbDT::date();
foreach ($plateau->loadRefsTechniciens(false) as $_technicien) {
  $_technicien->countSejoursDate($date, $m);
};

// Technicien à editer
$technicien = new CTechnicien();
$technicien->load($technicien_id);
$technicien->plateau_id = $plateau->_id;
$technicien->loadRefsNotes();
$technicien->loadRefPlateau();
$technicien->loadRefKine();
$technicien->countSejoursDate($date, $m);

// Alter egos pour les transferts de séjours
$where["kine_id"] = "= '$technicien->kine_id'";
/** @var CTechnicien[] $alteregos */
$alteregos = $technicien->loadList($where);
unset($alteregos[$technicien->_id]);
CStoredObject::massLoadFwdRef($alteregos, "plateau_id");
CStoredObject::massLoadFwdRef($alteregos, "kine_id");
foreach ($alteregos as $_alterego) {
  $_alterego->loadRefPlateau();
  $_alterego->loadRefKine();
}

// Kinés
$user  = new CMediusers();
$kines = $user->loadListFromType(array("Diététicien", "Infirmière", "Rééducateur"));

// Création du template
$smarty = new CSmartyDP("modules/ssr");

$smarty->assign("technicien", $technicien);
$smarty->assign("alteregos", $alteregos);
$smarty->assign("plateau", $plateau);
$smarty->assign("kines", $kines);

$smarty->display("inc_edit_technicien");
