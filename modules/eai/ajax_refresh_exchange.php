<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

/**
 * Refresh exchange
 */
CCanDo::checkRead();

$exchange_guid = CValue::get("exchange_guid");

// Chargement de l'échange demandé
$exchange = CMbObject::loadFromGuid($exchange_guid);

if (!$exchange) {
  // Création du template
  $smarty = new CSmartyDP();
  $smarty->assign("object", null);
  $smarty->display("inc_exchange.tpl");
  
  return;
}

$exchange->loadRefs(); 
$exchange->loadRefsInteropActor();
$exchange->getObservations();
$exchange->loadRefsNotes();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("object", $exchange);
$smarty->display("inc_exchange.tpl");

