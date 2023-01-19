<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkRead();

$canSante400 = CModule::getCanDo("dPsante400");

$idex = new CIdSante400();
$idex->load(CValue::get("idex_id"));
$idex->loadTargetObject();

// Chargement du filtre
$filter               = new CIdSante400();
$filter->object_id    = CValue::get("object_id");
$filter->object_class = CValue::get("object_class");
$filter->tag          = CValue::get("tag");
$filter->id400        = CValue::get("id400");
$filter->nullifyEmptyFields();

$filter->last_update     = CValue::first($idex->last_update, CMbDT::dateTime());

// Rester sur le même filtre en mode dialogue
$dialog = CValue::get("dialog");
if ($dialog && $idex->_id) {
    $filter->object_class = $idex->object_class;
    $filter->object_id    = $idex->object_id;
}

// Récupération de la liste des classes disponibles
if ($filter->object_class && $filter->object_id) {
    $listClasses = [$filter->object_class];
} else {
    $listClasses = CApp::getInstalledClasses([], true);
}

// Chargement de la cible si oBjet unique
$target = null;
if ($filter->object_id && $filter->object_class) {
    $target = CMbObject::getInstance($filter->object_class);
    $target->load($filter->object_id);
}

if (!$idex->_id) {
    $idex = $filter;
}

// Si on veut un chargement unique on charge le premier identifiant pour le tag, object_id et object_class
if (CValue::get("load_unique") && $idex->tag && $idex->object_id && $idex->object_class) {
    $idex->last_update = null;
    $idex->loadMatchingObject();
}

$smarty = new CSmartyDP();

$smarty->assign("idex", $idex);
$smarty->assign("canSante400", $canSante400);
$smarty->assign("filter", $filter);
$smarty->assign("target", $target);
$smarty->assign("listClasses", $listClasses);

$smarty->display("inc_edit_identifiant.tpl");
