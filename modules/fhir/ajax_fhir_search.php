<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Fhir\Actors\CReceiverFHIR;
use Ox\Interop\Ihe\CMHD;
use Ox\Interop\Ihe\CPDQm;
use Ox\Interop\Ihe\CPIXm;

CCanDo::checkAdmin();

$cn_receiver_guid = CValue::sessionAbs("cn_receiver_guid");
$std_profile          = CView::get("search_type", "str");
CView::checkin();

/** @var CPIXm|CPDQm|CMHD $class */
$class = new $std_profile();
$receiver = (new CInteropActorFactory())->receiver()->makeFHIR();
$objects  = CReceiverFHIR::getObjectsBySupportedEvents(
  $class::$evenements,
  $receiver,
  true,
  $std_profile
);

/** @var CInteropReceiver[] $receivers */
$receivers = array();
foreach ($objects as $event => $_receivers) {
  if (!$_receivers) {
    continue;
  }

  /** @var CInteropReceiver[] $_receivers */
  foreach ($_receivers as $_receiver) {
    $_receiver->loadRefGroup();
    $receivers[$_receiver->_guid] = $_receiver;
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("receivers", $receivers);
$smarty->assign("search_type", $std_profile);
$smarty->assign("cn_receiver_guid", $cn_receiver_guid);
$smarty->display("inc_vw_$std_profile.tpl");
