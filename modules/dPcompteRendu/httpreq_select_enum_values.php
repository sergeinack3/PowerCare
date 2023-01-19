<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\FieldSpecs\CEnumSpec;

/**
 * Liste déroulante des depend values des aides à a saisie
 */
CCanDo::checkRead();

$object_class = CValue::get("object_class");
$field        = CValue::get("field");

$object = new $object_class;
$list = array();

if ($object->_specs[$field] instanceof CEnumSpec) {
  $list = $object->_specs[$field]->_locales;
}

array_unshift($list, " - ".CAppUI::tr("None"));

ksort($list);

$smarty = new CSmartyDP();

$smarty->assign("list", $list);

$smarty->display("inc_select_enum_values");
