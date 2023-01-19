<?php
/**
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Interop\Eai\CInteropActorFactory;

CCanDo::checkAdmin();

$receiver =  (new CInteropActorFactory())->receiver()->makeHprimXML();
$receivers = $receiver->loadMatchingList();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("receivers", $receivers);
$smarty->display("configure.tpl");

