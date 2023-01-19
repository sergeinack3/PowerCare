<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

$object_class = CValue::get('object_class');
$keywords     = CValue::post('keywords_code_pmsi');
$limit        = CValue::get('limit', 30);

/** @var CMbObject $object */
$object = new $object_class;
$ds = $object->_spec->ds;
if ($keywords == "") {
  $keywords = "%";
}
$codes    = $object->getAutocompleteList($keywords, null, $limit, null);
// Création du template
$smarty = new CSmartyDP();

$smarty->assign('codes', $codes);

$smarty->display('nomenclature_cim/inc_autocomplete_cim10');