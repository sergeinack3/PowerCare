<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CEmplacement;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();

$chambre_id = CValue::getOrSession("chambre_id");
$service_id = CValue::getOrSession("service_id");

$emplacement         = new CEmplacement();
$where               = array();
$where["chambre_id"] = " = '$chambre_id'";
$emplacement->loadObject($where);

$service = new CService();
$service->load($service_id);
$service->loadRefsChambres();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("emplacement", $emplacement);
$smarty->assign("chambres", $service->_ref_chambres);

$smarty->display("inc_vw_emplacement.tpl");
