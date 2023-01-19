<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CAideSaisie;

/**
 * Autocomplete d'aides à la saisie
 */
$object_class = CValue::get("object_class");
$user_id      = CValue::get("user_id");
$property     = CValue::get("property");

$depend_value_1   = CValue::post("depend_value_1", null);
$depend_value_2   = CValue::post("depend_value_2", null);
$needle           = CValue::post("_search");
$hide_empty_list  = CValue::post("hide_empty_list");
$hide_exact_match = CValue::post("hide_exact_match");
$strict           = CValue::post("strict");

CView::enableSlave();

$object = new $object_class;
/** @var $object CMbObject */
$object->loadAides($user_id, $needle, $depend_value_1, $depend_value_2, $property, $strict);

// On supprime les aides dont le text est exactement le meme que ce quon vient de taper
if ($hide_exact_match) {
  /** @var $_aide CAideSaisie */
  foreach ($object->_aides_new as $_id => $_aide) {
    if (trim($needle) === trim($_aide->text)) {
      unset($object->_aides_new[$_id]);
    }
  }
}

CStoredObject::massLoadBackRefs($object->_aides_new, "hypertext_links");
foreach ($object->_aides_new as $_id => $_aide) {
  $_aide->loadRefsHyperTextLink();
}

// Tableau de depend value
@list($depend_field_1, $depend_field_2) = $object->_specs[$property]->helped;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("object", $object);
$smarty->assign("property", $property);
$smarty->assign("needle", $needle);
$smarty->assign("nodebug", true);
$smarty->assign("depend_field_1", $depend_field_1);
$smarty->assign("depend_field_2", $depend_field_2);
$smarty->assign("hide_empty_list", $hide_empty_list);

$smarty->display("httpreq_do_aide_autocomplete");
