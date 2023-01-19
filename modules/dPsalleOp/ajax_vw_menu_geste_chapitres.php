<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\SalleOp\CAnesthPeropChapitre;

CCanDo::checkRead();
$chapitre_ids   = CView::get("chapitre_ids", "str");
$see_all_gestes = CView::get("see_all_gestes", "bool default|0", true);
CView::checkin();

$chapitre_ids = explode("|", $chapitre_ids);

$count_chapitre_ids = $chapitre_ids && count($chapitre_ids)  ? count($chapitre_ids) : 0;

$where = array();
$where["actif"] = " = '1'";

if ($count_chapitre_ids) {
  CMbArray::removeValue("", $chapitre_ids);

  $where["anesth_perop_chapitre_id"] = CSQLDataSource::prepareIn($chapitre_ids);
}

if (!$count_chapitre_ids || in_array("0", $chapitre_ids)) {
  $chapitre = new CAnesthPeropChapitre();
  $chapitre->libelle = CAppUI::tr("common-No chapter");

  $chapters = array(CAppUI::tr("common-No chapter") => $chapitre);
}

$chapitre = new CAnesthPeropChapitre();
$chapitres = $chapitre->loadGroupList($where, "libelle ASC");

foreach ($chapitres as $_chapitre) {
  $chapters[$_chapitre->_view] = $_chapitre;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("chapters", $chapters);
$smarty->assign("see_all_gestes", $see_all_gestes);
$smarty->display("inc_vw_menu_geste_chapitres");
