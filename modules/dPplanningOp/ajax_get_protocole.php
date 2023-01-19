<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Mediboard\PlanningOp\CProtocole;

$protocole_id = CValue::get("protocole_id");
$chir_id      = CValue::get("chir_id");

$protocole = new CProtocole();
$protocole->load($protocole_id);
$protocole->loadRefsFwd();

if (CAppUI::gconf("dPbloc CPlageOp systeme_materiel")) {
  $protocole->_types_ressources_ids = implode(",", CMbArray::pluck($protocole->loadRefsBesoins(), "type_ressource_id"));
}

$protocole->loadRefsDocItemsGuids();
$protocole->loadRefsProtocolesOp();

if (CModule::getActive("eds") && CAppUI::gconf("eds CSejour allow_eds_input")) {
    $protocole->loadEpisodeSoin();
}

$smarty = new CSmartyDP();

$smarty->assign("chir_id"  , $chir_id);
$smarty->assign("protocole", $protocole);

$smarty->display("inc_get_protocole");
