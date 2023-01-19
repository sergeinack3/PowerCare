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
use Ox\Interop\Hl7\CHL7v2TableEntry;

CCanDo::checkAdmin();

$table_number = CValue::getOrSession("table_number");

$table_entry         = new CHL7v2TableEntry();
$table_entry->number = $table_number;
$table_entries       = $table_entry->loadMatchingList("user");
$table_entry->user   = 1;

$table_description         = new CHL7v2TableDescription();
$table_description->number = $table_number;
$table_description->loadMatchingObject();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("table_entry"      , $table_entry);
$smarty->assign("table_description", $table_description);
$smarty->assign("table_entries"    , $table_entries);
$smarty->display("inc_refresh_modal_HL7_table.tpl");

