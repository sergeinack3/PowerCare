<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Interop\Ror\CRORException;
use Ox\Interop\Ror\CRORFactory;
use Ox\Mediboard\Urgences\CExtractPassages;

$extract_passages_id = CValue::get("extract_passages_id");

$extractPassages = new CExtractPassages();
$extractPassages->load($extract_passages_id);

try {
  $rpu_sender      = CRORFactory::getSender();
  $extractPassages = $rpu_sender->loadExtractPassages($extractPassages);

  $echange = utf8_decode($extractPassages->message_xml);
  header("Content-Disposition: attachment; filename={$extractPassages->type}-{$extract_passages_id}.xml");
  header("Content-Type: text/plain; charset=" . CApp::$encoding);
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Content-Length: " . strlen($echange));
  echo $echange;
}
catch (CRORException $exception) {
  CAppUI::stepAjax($exception->getMessage(), UI_MSG_ERROR);
}
