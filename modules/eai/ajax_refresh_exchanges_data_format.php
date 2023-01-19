<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Refresh exchanges
 */
CCanDo::checkRead();

$exchange_class = CView::get("exchange_class", "str", true);
$exchange_type  = CView::get("exchange_type" , "str", true);
$group_id       = CView::get("group_id"      , "ref class|CGroups default|" . CGroups::loadCurrent()->_id, true);
$actor_guid     = CView::get("actor_guid"    , "guid class|CInteropActor");
$keywords_msg   = CView::get("keywords_msg"  , "str", true);
$keywords_ack   = CView::get("keywords_ack"  , "str", true);
$modal          = CView::get("modal"         , "bool default|0");
$page           = CView::get('page'          , "num default|0");
$_date_min      = CView::get('_date_min'     , array("dateTime", "default" => CMbDT::dateTime("-7 day")), true);
$_date_max      = CView::get('_date_max'     , array("dateTime", "default" => CMbDT::dateTime("+1 day")), true);
CView::checkin();
CView::enforceSlave();

// Types filtres qu'on peut prendre en compte
$filtre_types = array(
  'ok'    => array('emetteur', 'destinataire'),
  'error' => array('no_date_echange','message_invalide', 'acquittement_invalide', 'no_ack')
);

$types = array();
foreach ($filtre_types as $status_type => $_type) {
  foreach ($_type as $type) {
    $types[$status_type][$type] = !isset($t) || in_array($type, $t);
  }
}

$actor = null;
if ($actor_guid) {
  /** @var CInteropActor $actor */
  $actor = CMbObject::loadFromGuid($actor_guid);
}

$group_id = $actor ? $actor->group_id : $group_id;

/** @var CExchangeDataFormat $exchange */
$exchange = new $exchange_class;
$exchange->_date_min = $_date_min;
$exchange->_date_max = $_date_max;
$exchange->type      = $exchange_type;
$exchange->group_id  = $group_id;

$messages = $exchange->getFamily();
$evenements = array();
foreach ($messages as $_message => $_evt_class) { 
  $evt  = new $_evt_class;
  $evts = $evt->getEvenements(); 
  $keys       = array_map_recursive(array(CMbString::class, "removeDiacritics"), array_keys($evts));
  $values     = array_values($evts);
  $evenements[$_message] = ($keys && $values) ? array_combine($keys, $values) : array();
}

$senders = array();
$sender_class = CMbArray::get($exchange->_specs, "sender_class");

if ($sender_class) {
  foreach ($sender_class->_list as $_sender_class) {
      if (!class_exists($_sender_class)) {
          continue;
      }
      /** @var CInteropSender $sender */
      $sender = new $_sender_class();
      $sender->group_id = $group_id;
      $sender->actif = 1;
      if ($lists = $sender->countMatchingList() == 0) {
          continue;
      }

      $senders[$_sender_class] = $sender->loadMatchingList();
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("exchange"    , $exchange);
$smarty->assign("types"       , $types);
$smarty->assign("page"        , $page);
$smarty->assign("actor_guid"  , $actor_guid);
$smarty->assign("modal"       , $modal);
$smarty->assign("messages"    , $messages);
$smarty->assign("senders"     , $senders);
$smarty->assign("evenements"  , $evenements);
$smarty->assign("keywords_msg", $keywords_msg);
$smarty->assign("keywords_ack", $keywords_ack);
$smarty->assign("actor"       , $actor);
$smarty->display("inc_filters_exchanges_data_format.tpl");

