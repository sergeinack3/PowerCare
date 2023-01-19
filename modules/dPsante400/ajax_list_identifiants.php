<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * List idexs
 */

CCanDo::check();

$idex_id      = CView::get("idex_id", "ref class|CIdSante400");
$page         = CView::get("page", "num default|0");
$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", ($object_class ? "ref class|$object_class" : "num"));
$tag          = CView::get("tag", "str");
$id400        = CView::get("id400", "str");

CView::checkin();

if (!$object_id && !$object_class && !$tag && !$id400) {
    CAppUI::stepMessage(UI_MSG_WARNING, "No filter");
    CApp::rip();
}

// Chargement de la liste des id4Sante400 pour le filtre
$filter               = new CIdSante400();
$filter->object_id    = $object_id;
$filter->object_class = $object_class;
$filter->tag          = $tag;
$filter->id400        = $id400;
$filter->nullifyEmptyFields();

// Chargement de la cible si objet unique
$target = null;
if ($filter->object_id && $filter->object_class) {
    $target = CMbObject::getInstance($filter->object_class);
    $target->load($filter->object_id);
}

// Requête du filtre
$step  = 25;
$idexs = $filter->loadMatchingList(null, "$page, $step");
CStoredObject::massLoadFwdRef($idexs, "object_id");
foreach ($idexs as $_idex) {
    $_idex->getSpecialType();
}

$total_idexs = $filter->countMatchingList();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("idexs", $idexs);
$smarty->assign("total_idexs", $total_idexs);
$smarty->assign("filter", $filter);
$smarty->assign("idex_id", $idex_id);
$smarty->assign("page", $page);
$smarty->assign("target", $target);
$smarty->assign("looking_for_duplicate", false);

$smarty->display("inc_list_identifiants.tpl");
