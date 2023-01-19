<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Sélecteur de modèle
 */
CCanDo::checkRead();

$object_id    = CView::get("object_id", "num");
$object_class = CView::get("object_class", "str");
$praticien_id = CView::get("praticien_id", "num", true);
$appfine      = CView::get("appfine", "bool default|0");
$order_id     = CView::get("order_id", "num");

CView::checkin();
CView::enableSlave();

// Chargement de l'objet
$object = new $object_class;
/** @var $object CMbObject */
$object->load($object_id);

// Chargement du praticien concerné et des praticiens disponibles
$praticien = CMediusers::get($praticien_id);
$praticien->canDo();

// Chargement des objets relatifs a l'objet chargé
$templateClasses = $object->getTemplateClasses();

// Chargement des modeles de consultations du praticien
$modelesCompat    = array();
$modelesNonCompat = array();

// Chargement des modeles pour chaque classe, pour les praticiens et leur fonction
foreach ($templateClasses as $class => $id) {
  $modeles = CCompteRendu::loadAllModelesFor($praticien->_id, "prat", $class, "body", true, "", 1);
  if ($id) {
    $modelesCompat[$class] = $modeles;
    continue;
  }
  $modelesNonCompat[$class] = $modeles;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("praticien"       , $praticien);
$smarty->assign("target_id"       , $object->_id);
$smarty->assign("target_class"    , $object->_class);

$smarty->assign("modelesCompat"   , $modelesCompat);
$smarty->assign("modelesNonCompat", $modelesNonCompat);
$smarty->assign("modelesId"       , $templateClasses);
$smarty->assign("appfine"         , $appfine);
$smarty->assign("order_id"        , $order_id);

$smarty->display("modele_selector");
