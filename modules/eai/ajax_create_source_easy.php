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
use Ox\Mediboard\System\CExchangeSource;

$actor_guid = CView::get("actor_guid", "str");
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
$smarty->assign("object"            , null);
$smarty->assign("source_reference"  , new CExchangeSource());
$smarty->assign("types_source"      , CExchangeSource::$typeToClass);
$smarty->assign("tabs_menu"         , "source");
$smarty->assign("source"            , $source);
$smarty->assign("messages_supported", $messages_supported);
$smarty->display("inc_choose_sources.tpl");