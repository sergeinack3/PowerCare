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
use Ox\Mediboard\SalleOp\CAnesthPeropCategorie;

CCanDo::checkEdit();
CView::checkin();

$evenement_category   = new CAnesthPeropCategorie();
$evenement_categories = $evenement_category->loadGroupList(null, "libelle ASC");

CStoredObject::massLoadBackRefs($evenement_categories, "files");
CStoredObject::massLoadBackRefs($evenement_categories, "gestes_perop");
CStoredObject::massLoadFwdRef($evenement_categories, "group_id");

foreach ($evenement_categories as $_categorie) {
  $_categorie->loadRefFile();
  $_categorie->loadRefsGestesPerop();
  $_categorie->loadRefGroup();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("evenement_categories", $evenement_categories);
$smarty->display("inc_vw_evenement_perop_categories");
