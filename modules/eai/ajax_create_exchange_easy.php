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
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Mediboard\System\CExchangeSource;

$actor_guid = CView::get('actor_guid', 'str');
CView::checkin();

/** @var CInteropReceiver $actor */
$actor = CMbObject::loadFromGuid($actor_guid);

if (!$actor->_id) {
  CAppUI::stepAjax("CInteropActor-back-domains.empty", UI_MSG_ERROR);
}

$exchanges = $actor->makeBackSpec("echanges");
$actor->_backSpecs["echanges"];

/** @var CExchangeDataFormat $data_format */
$data_format = new $exchanges->class;

$actor->loadRefsExchangesSources();

$messages = $data_format->getMessagesSupported($actor_guid);
$all_messages = array();
foreach ($messages as $_family => $_messages_supported) {
  $family = new $_family;
  $events = $family->getEvenements();

  $categories = array();
  if (isset($family->_categories) && !empty($family->_categories)) {
    foreach ($family->_categories as $_category => $events_name) {
      foreach ($events_name as $_event_name) {
        foreach ($_messages_supported as $_message_supported) {
          if (!array_key_exists($_event_name, $events)) {
            continue;
          }

          if ($_message_supported->message != $events[$_event_name]) {
            continue;
          }

          $categories[$_category][] = $_message_supported;
        }
      }
    }
  }
  else {
    $categories["none"] = $_messages_supported;
  }

  // On reformate un peu le tableau des catégories
  $family->_categories = $categories;

  $domain = $family->domain ? $family->domain : $family->name;

  $all_messages[$domain][] = $family;
}

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
$smarty->assign("actor"       , $actor);
$smarty->assign("source"      , $source);
$smarty->assign("messages"    , $messages);
$smarty->assign("messages_supported", $actor->getMessagesSupportedSort($actor));
$smarty->assign("object"      , null);
$smarty->assign("types_source", array());
$smarty->assign("source_reference", new CExchangeSource());
$smarty->assign("tabs_menu"   , "exchange");
$smarty->assign("all_messages", $all_messages);
$smarty->assign("actor_guid"  , $actor_guid);
$smarty->display("inc_create_receiver_easy.tpl");