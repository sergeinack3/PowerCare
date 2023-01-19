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

$page         = intval(CValue::get('page', 0));
$table_number = CValue::getOrSession("table_number", 1);
$keywords     = CValue::getOrSession("keywords", "%");

$table_description = new CHL7v2TableDescription();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("page"             , $page);
$smarty->assign("table_description", $table_description);
$smarty->assign("keywords"         , $keywords);
$smarty->display("vw_hl7v2_tables.tpl");

