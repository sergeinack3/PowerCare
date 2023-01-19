<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CEchangeXML;
use Ox\Interop\Eai\CExchangeBinary;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CExchangeTabular;

/**
 * Formats available
 */
CCanDo::checkRead();

$actor_guid = CValue::getOrSession("actor_guid");

$formats_xml = CExchangeDataFormat::getAll(CEchangeXML::class);
foreach ($formats_xml as &$_format_xml) {
  $_format_xml = new $_format_xml;
  $_format_xml->getConfigs($actor_guid);
}

$formats_tabular = CExchangeDataFormat::getAll(CExchangeTabular::class);
foreach ($formats_tabular as &$_format_tabular) {
  $_format_tabular = new $_format_tabular;
  $_format_tabular->getConfigs($actor_guid);
}

$formats_binary = CExchangeDataFormat::getAll(CExchangeBinary::class);
foreach ($formats_binary as &$_format_binary) {
  $_format_binary = new $_format_binary;
  $_format_binary->getConfigs($actor_guid);
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("actor_guid"      , $actor_guid);
$smarty->assign("formats_xml"     , $formats_xml);
$smarty->assign("formats_tabular" , $formats_tabular);
$smarty->assign('formats_binary'  , $formats_binary);
$smarty->display("inc_configs_formats.tpl");