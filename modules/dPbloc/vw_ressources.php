<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

/**
 * dPbloc
 */
CCanDo::checkEdit();

$indispo_ressource_id = CValue::getOrSession("indispo_ressource_id");
$type_ressource_id = CValue::getOrSession("type_ressource_id");
$date_indispo      = CValue::getOrSession("date_indispo");

$smarty = new CSmartyDP;

$smarty->assign("indispo_ressource_id", $indispo_ressource_id);
$smarty->assign("type_ressource_id", $type_ressource_id);
$smarty->assign("date_indispo", $date_indispo);

$smarty->display("vw_ressources.tpl");
