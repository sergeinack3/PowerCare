<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$cn_receiver_guid = CView::get("cn_receiver_guid", "guid class|CReceiverFHIR", true);
CView::checkin();

$receiver           = (new CInteropActorFactory())->receiver()->makeFHIR();
$receiver->group_id = CGroups::loadCurrent()->_id;
$receiver->actif    = "1";
$receivers          = $receiver->loadMatchingList();

$smarty = new CSmartyDP();
$smarty->assign("receivers", $receivers);
$smarty->assign("cn_receiver_guid", $cn_receiver_guid);
$smarty->display("vw_test_fhir.tpl");
