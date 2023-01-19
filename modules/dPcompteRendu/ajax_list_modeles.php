<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

// Filtres
$filtre = new CCompteRendu();
$filtre->_id          = CValue::getOrSession("compte_rendu_id");
$filtre->user_id      = CValue::getOrSession("user_id");
$filtre->function_id  = CValue::getOrSession("function_id");
$filtre->object_class = CValue::getOrSession("object_class");
$filtre->type         = CValue::getOrSession("type");
$filtre->actif        = CValue::getOrSession("actif");

$type_dmp     = CView::get("type_dmp", "str");
$enable_slave = CView::get("enable_slave", "num default|1");
$order_col    = CView::get("order_col", "enum list|nom|object_class|file_category_id|type|_count_utilisation|_image_status|_date_last_use default|object_class", true);
$order_way    = CView::get("order_way", "enum list|ASC|DESC default|DESC", true);

CView::checkin();

if (!isset($_GET["compte_rendu_id"]) && $enable_slave) {
  CView::enableSlave();
}

$order = "";

switch ($order_col) {
  default:
  case "object_class":
    $order = "object_class $order_way, type, nom";
    break;
  case "nom":
    $order = "nom $order_way, object_class, type";
    break;
  case "type":
    $order = "type $order_way, object_class, nom";
    break;
  case "file_category_id":
    $order = "file_category_id $order_way, object_class, nom";
}

// Praticien
$user = CMediusers::get($filtre->user_id);
$filtre->user_id = $user->_id;

$owner = "prat";
$owner_id = $filtre->user_id;
$owners = $user->getOwners();

if ($filtre->function_id) {
  $owner = "func";
  $owner_id = $filtre->function_id;
  $func = new CFunctions();
  $func->load($owner_id);
  $owners = [
    "func" => $func,
    "etab" => $func->loadRefGroup(),
    'instance' => CCompteRendu::getInstanceObject()
  ];
}
else {
  $sec_func = $user->loadRefsSecondaryFunctions();
  foreach ($sec_func as $_func) {
    $owners["func" . $_func->_id] = $_func;
  }
}

$modeles = CCompteRendu::loadAllModelesFor($owner_id, $owner, $filtre->object_class, $filtre->type, 1, $order, $filtre->actif);

if ($type_dmp) {
  foreach ($modeles as $object_key => $_models) {
    foreach ($_models as $key => $_model) {
      if ($_model->type_doc_dmp !== $type_dmp) {
        unset($modeles[$object_key][$key]);
      }
    }
  }
}

if ($filtre->function_id) {
  unset($modeles["prat"]);
}

foreach ($modeles as $key => &$_modeles) {
  $sort_modeles = array(
    "body"    => array(),
    "header"  => array(),
    "footer"  => array(),
    "preface" => array(),
    "ending"  => array()
  );

  /** @var $_modele CCompteRendu */
  foreach ($_modeles as $_modele) {
    $_modele->canDo();

    $sort_modeles[$_modele->type][$_modele->_id] = $_modele;

    $_modele->_count["documents_generated"] = 0;
  }

  CStoredObject::massCountBackRefs($sort_modeles["body"], "documents_generated");

  CStoredObject::massLoadFwdRef($sort_modeles["body"], "header_id");
  CStoredObject::massLoadFwdRef($sort_modeles["body"], "footer_id");
  CStoredObject::massLoadFwdRef($sort_modeles["body"], "preface_id");
  CStoredObject::massLoadFwdRef($sort_modeles["body"], "ending_id");

  CStoredObject::massCountBackRefs($sort_modeles["header"], "modeles_headed", array("object_id" => "IS NULL"));
  CStoredObject::massCountBackRefs($sort_modeles["footer"], "modeles_footed", array("object_id" => "IS NULL"));

  CStoredObject::massCountBackRefs($sort_modeles["preface"], "modeles_prefaced");
  CStoredObject::massCountBackRefs($sort_modeles["ending"] , "modeles_ended");

  CCompteRendu::massGetDateLastUse($sort_modeles["body"]);

  foreach ($_modeles as $_modele) {
    switch ($_modele->type) {
      case "body":
        $_modele->loadComponents();
        break;
      default:
    }
  }

  CStoredObject::massLoadFwdRef($_modeles, "content_id");

  foreach ($_modeles as $_modele) {
    $_modele->loadContent()->getImageStatus();
  }

  if ($order_col === "_count_utilisation") {
    CMbArray::pluckSort($_modeles, $order_way == "ASC" ? SORT_ASC : SORT_DESC, "_count", "documents_generated");
  }
  elseif ($order_col === "_image_status") {
    CMbArray::pluckSort($_modeles, $order_way == "ASC" ? SORT_ASC : SORT_DESC, "_ref_content", "_image_status");
  }
  elseif ($order_col === "_date_last_use") {
    CMbArray::pluckSort($_modeles, $order_way == "ASC" ? SORT_ASC : SORT_DESC, "_date_last_use");
  }
}

$smarty = new CSmartyDP();

$smarty->assign("filtre"   , $filtre);
$smarty->assign("modeles"  , $modeles);
$smarty->assign("owners"   , $owners);
$smarty->assign("order_way", $order_way);
$smarty->assign("order_col", $order_col);

$smarty->display("inc_list_modeles");
