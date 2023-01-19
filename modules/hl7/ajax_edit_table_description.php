<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Hl7\CHL7v2TableDescription;

CCanDo::checkAdmin();

$table_id = CValue::get("table_id");

$table_description = new CHL7v2TableDescription();
$table_description->load($table_id);

if (!$table_description->_id) {
  $table_description->user = 1;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("table_description", $table_description);
$smarty->display("inc_edit_table_description.tpl");

