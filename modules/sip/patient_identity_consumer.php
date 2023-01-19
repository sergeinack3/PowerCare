<?php
/**
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Mediboard\Patients\CPatient;

/**
 * Patient identity consumer
 */
CCanDo::checkAdmin();

$cn_receiver_guid = trim(CValue::getOrSessionAbs("cn_receiver_guid"));

$receiver  = (new CInteropActorFactory())->receiver()->makeHL7v2();
$objects = CReceiverHL7v2::getObjectsBySupportedEvents(array("CHL7EventQBPQ23"), $receiver);

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

$smarty->assign("receivers"       , $receivers);
$smarty->assign("patient"         , new CPatient());
$smarty->assign("cn_receiver_guid", $cn_receiver_guid);

$smarty->display("patient_identity_consumer.tpl");
