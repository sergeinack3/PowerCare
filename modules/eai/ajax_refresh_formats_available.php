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
use Ox\Interop\Eai\CEchangeXML;
use Ox\Interop\Eai\CExchangeBinary;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CExchangeTabular;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CInteropSender;

/**
 * Formats available
 */
CCanDo::checkRead();

$actor_guid = CValue::getOrSession("actor_guid");

$formats_xml  = $formats_tabular  = $formats_binary  = array();
$messages_xml = $messages_tabular = $messages_binary = array();

/** @var CInteropActor $actor */
$actor = CMbObject::loadFromGuid($actor_guid);

// Expéditeur d'intégration
if ($actor instanceof CInteropSender) {
  $formats_xml = CExchangeDataFormat::getAll(CEchangeXML::class);
  foreach ($formats_xml as &$_format_xml) {
    /** @var CEchangeXML $_format_xml */
    $_format_xml = new $_format_xml;

    $temp = $_format_xml->getMessagesSupported($actor_guid, false, null, true);
    $messages_xml = array_merge($messages_xml, $temp);
  }

  $formats_tabular = CExchangeDataFormat::getAll(CExchangeTabular::class);
  foreach ($formats_tabular as &$_format_tabular) {
    /** @var CExchangeTabular $_format_tabular */
    $_format_tabular = new $_format_tabular;

    $temp = $_format_tabular->getMessagesSupported($actor_guid, false, null, true);
    $messages_tabular = array_merge($messages_tabular, $temp);
  }

  $formats_binary = CExchangeDataFormat::getAll(CExchangeBinary::class);
  foreach ($formats_binary as &$_format_binary) {
    /** @var CExchangeBinary $_format_binary */
    $_format_binary = new $_format_binary;
    
    $temp = $_format_binary->getMessagesSupported($actor_guid, false, null, true);
    $messages_binary = array_merge($messages_binary, $temp);
  }
}
// Destinataire d'intégration 
else if ($actor instanceof CInteropReceiver) {
  $exchanges = $actor->makeBackSpec("echanges");
  $actor->_backSpecs["echanges"];
  $data_format = new $exchanges->class;

  if ($data_format instanceof CExchangeBinary) {
    $formats_binary[] = $data_format;
    $temp = $data_format->getMessagesSupported($actor_guid, false, null, true);
    $messages_binary = array_merge($messages_tabular, $temp);
  }

  if ($data_format instanceof CExchangeTabular) {
    $formats_tabular[] = $data_format;
    $temp = $data_format->getMessagesSupported($actor_guid, false, null, true);
    $messages_tabular = array_merge($messages_tabular, $temp);
  }

  if ($data_format instanceof CEchangeXML) {
    $formats_xml[] = $data_format;
    $temp = $data_format->getMessagesSupported($actor_guid, false, null, true);
    $messages_xml = array_merge($messages_xml, $temp);
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("actor"           , $actor);
$smarty->assign("actor_guid"      , $actor_guid);
$smarty->assign("formats_xml"     , $formats_xml);
$smarty->assign("messages_xml"    , $messages_xml);
$smarty->assign("formats_tabular" , $formats_tabular);
$smarty->assign("messages_tabular", $messages_tabular);
$smarty->assign("formats_binary"  , $formats_binary);
$smarty->assign("messages_binary" , $messages_binary);

$smarty->display("inc_formats_available.tpl");

