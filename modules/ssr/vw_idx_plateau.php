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
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Ssr\CEquipement;
use Ox\Mediboard\Ssr\CPlateauTechnique;
use Ox\Mediboard\Ssr\CTechnicien;

global $m;

CCanDo::checkRead();
$plateau_id    = CView::get("plateau_id", "ref class|CPlateauTechnique", true);
$template_mode = CView::get("template_mode", "str");
CView::checkin();

$where = array("type = '$m' OR type IS NULL");
// Plateaux disponibles
$plateau           = new CPlateauTechnique();
$plateau->group_id = CGroups::loadCurrent()->_id;
$plateaux          = $plateau->loadGroupList($where);
foreach ($plateaux as $_plateau) {
  $_plateau->countBackRefs("techniciens");
  $_plateau->countBackRefs("equipements");
}

// Plateau sélectionné
$plateau->load($plateau_id);
$plateau->loadRefsNotes();
$plateau->loadRefsEquipements(false);

$date = CMbDT::date();
foreach ($plateau->loadRefsTechniciens(false) as $_technicien) {
  $_technicien->countSejoursDate($date, $m);
}

// Equipement
$equipement             = new CEquipement();
$equipement->plateau_id = $plateau->_id;

// Technicien
$technicien             = new CTechnicien();
$technicien->plateau_id = $plateau->_id;

// Kinés
$user  = new CMediusers();
$kines = $user->loadKines();

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("equipement", $equipement);
$smarty->assign("technicien", $technicien);
$smarty->assign("kines", $kines);
$smarty->assign("plateau", $plateau);
$smarty->assign("plateaux", $plateaux);

if ($template_mode === "refresh_list") {
  $smarty->display("inc_list_plateaux");
}
elseif ($template_mode === "load_form") {
  $smarty->display("inc_form_plateau");
}
else {
  $smarty->display("vw_idx_plateau");
}
