<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CAideSaisie;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Liste d'aides à la saisie
 */
CCanDO::checkRead();
// Utilisateur sélectionné ou utilisateur courant
$user_id        = CView::get("user_id", "ref class|CMediusers", true);
$function_id    = CView::get("function_id", "ref class|CFunctions", true);
$class          = CView::get("class", "str", true);
$start          = CView::get("start", "str", true);
$keywords       = CView::get("keywords", "str", true);
$order_col_aide = CView::get("order_col_aide", "str", true);
$order_way      = CView::get("order_way", "str", true);
$aide_id        = CView::get("aide_id", "str", true);
CView::checkin();

$order_by = $order_col_aide ? $order_col_aide . " " . $order_way : null;

$userSel = CMediusers::get($user_id);
$userSel->loadRefFunction()->loadRefGroup();

$function = new CFunctions();
$function->load($function_id);
$function->loadRefGroup();

$owners = $userSel->getOwners();

$where = [];
if ($class) {
  $where["class"] = "= '$class'";
}

// Liste des aides pour le praticien

// Accès aux aides à la saisie de la fonction et de l'établissement
$module = CModule::getActive("dPcompteRendu");
$is_admin = $module && $module->canAdmin();
$access_function = $is_admin || CAppUI::gconf("dPcompteRendu CAideSaisie access_function");
$access_group    = $is_admin || CAppUI::gconf("dPcompteRendu CAideSaisie access_group");

$aides      = [];
$aidesCount = [];

if (!$function_id) {
  $aides["user"]      = [];
  $aidesCount["user"] = 0;
}
if ($access_function) {
  $aides["func"]      = [];
  $aidesCount["func"] = 0;
}
if ($access_group) {
  $aides["etab"]      = [];
  $aidesCount["etab"] = 0;
}

$aides["instance"] = [];
$aidesCount["instance"] = [];

$_aide = new CAideSaisie();

foreach ($aides as $owner => $_aides_by_owner) {
  switch ($owner) {
    default:
    case "user":
      $key_where = "user_id";
      $where[$key_where]   = "= '$userSel->user_id'";
      break;

    case "func":
      $key_where = "function_id";
      $where[$key_where] = "= '".($function_id ? $function_id : $userSel->function_id) ."'";
      break;

    case "etab":
      $key_where = "group_id";
      $where[$key_where]  = "= '".($function_id ? $function->_ref_group->_id  : $userSel->_ref_function->group_id) ."'";
      break;

    case "instance":
      $key_where = 100;
      $where[$key_where] = "user_id IS NULL AND function_id IS NULL AND group_id IS NULL";
  }

  $aides["{$owner}_ids"] = array_keys($_aide->seek($keywords, $where, 1000));
  $aides[$owner] = $_aide->seek($keywords, $where, $start[$owner].", 30", true, null, $order_by);
  $aidesCount[$owner] = $_aide->_totalSeek;
  unset($where[$key_where]);

  foreach ($aides[$owner] as $aide) {
    $aide->loadRefUser();
    $aide->loadRefFunction();
    $aide->loadRefGroup();
    $aide->loadRefsCodesLoinc();
    $aide->loadRefsCodesSnomed();
  }

  CStoredObject::massLoadBackRefs($aides[$owner], 'hypertext_links');
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("userSel"       , $userSel);
$smarty->assign("function"      , $function);
$smarty->assign("aides"         , $aides);
$smarty->assign("aidesCount"    , $aidesCount);
$smarty->assign("class"         , $class);
$smarty->assign("start"         , $start);
$smarty->assign("order_col_aide", $order_col_aide);
$smarty->assign("order_way"     , $order_way);
$smarty->assign("aide_id"       , $aide_id);
$smarty->assign("owners"        , $owners);
$smarty->display("inc_tabs_aides");
