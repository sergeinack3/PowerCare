<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CModeleToPack;

/**
 * Ajout de modèles à un pack de modèles
 */

CCanDo::checkRead();

$object_guid  = CValue::get("object_guid");
$pack_id      = CValue::get("pack_id");
$filter_class = CValue::get("filter_class");

$object = $object_guid === "instance" ? CCompteRendu::getInstanceObject() : CMbObject::loadFromGuid($object_guid, true);

$module = CModule::getActive("dPcompteRendu");
$is_admin = $module && $module->canAdmin();
$access_function = $is_admin || CAppUI::gconf("dPcompteRendu CCompteRenduAcces access_function");
$access_group    = $is_admin || CAppUI::gconf("dPcompteRendu CCompteRenduAcces access_group");

$owner_types = array(
  "CMediusers" => "prat",
  "CFunctions" => "func",
  "CGroups"    => "etab",
  "CMbObject"  => "instance"
);

$modeles = CCompteRendu::loadAllModelesFor($object->_id, $owner_types[$object->_class], $filter_class, "body");
$nb_modeles = count($modeles["prat"]);
$nb_modeles += isset($modeles["func"]) ? count($modeles["func"]) : 0;
$nb_modeles += isset($modeles["etab"]) ? count($modeles["etab"]) : 0;
$nb_modeles += isset($modeles["instance"]) ? count($modeles["instance"]) : 0;

$link = new CModeleToPack();
$link->pack_id = $pack_id;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("link"            , $link);
$smarty->assign("modeles"         , $modeles);
$smarty->assign("pack_id"         , $pack_id);
$smarty->assign("access_function" , $access_function);
$smarty->assign("access_group"    , $access_group);
$smarty->assign("nb_modeles"      , $nb_modeles);

$smarty->display("inc_form_modeles_links");
