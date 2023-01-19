<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\SalleOp\CAnesthPeropChapitre;

CCanDo::checkEdit();
CView::checkin();

$evenement_chapitre   = new CAnesthPeropChapitre();
$evenement_chapitres = $evenement_chapitre->loadGroupList(null, "libelle ASC");

CStoredObject::massLoadBackRefs($evenement_chapitres, "anesth_perop_categories");
CStoredObject::massLoadFwdRef($evenement_chapitres, "group_id");

foreach ($evenement_chapitres as $_chapitre) {
  $_chapitre->loadRefsCategoriesGestes();
  $_chapitre->loadRefGroup();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("evenement_chapitres", $evenement_chapitres);
$smarty->display("inc_vw_evenement_perop_chapitres");
