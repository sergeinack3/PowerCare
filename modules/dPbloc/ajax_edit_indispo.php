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
use Ox\Mediboard\Bloc\CIndispoRessource;

CCanDo::checkEdit();

$indispo_ressource_id = CValue::get("indispo_ressource_id");

$indispo = new CIndispoRessource;
$indispo->load($indispo_ressource_id);
$indispo->loadRefRessource();

$smarty = new CSmartyDP;

$smarty->assign("indispo", $indispo);

$smarty->display("inc_edit_indispo.tpl");
