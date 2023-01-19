<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CInteropReceiver;

$actor_guid = CView::get('actor_guid', "str");
CView::checkin();

/** @var CInteropReceiver $actor */
$actor = CMbObject::loadFromGuid($actor_guid);
if (!$actor->_id) {
  CAppUI::stepAjax("CInteropActor-back-domains.empty", UI_MSG_ERROR);
}

$sources = $actor->loadRefsExchangesSources();
$messages_supported = $actor->getMessagesSupportedSort($actor);
$source = false;
foreach ($actor->_ref_msg_supported_family as $_msg_supported) {
  $_source = $actor->_ref_exchanges_sources[$_msg_supported];
  if ($_source->_id) {
    $source = true;
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("actor"             , $actor);
$smarty->assign("source"            , $source);
$smarty->assign("messages_supported", $messages_supported);
$smarty->display("inc_summary_actor.tpl");