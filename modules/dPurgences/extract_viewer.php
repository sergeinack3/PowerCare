<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Ror\CRORException;
use Ox\Interop\Ror\CRORFactory;
use Ox\Mediboard\Urgences\CExtractPassages;

$extract_passages_id = CValue::get("extract_passages_id");

$extractPassages = new CExtractPassages();
$extractPassages->load($extract_passages_id);

try {
  $rpu_sender = CRORFactory::getSender();
  $extractPassages = $rpu_sender->loadExtractPassages($extractPassages);
}
catch (CRORException $exception) {
  CAppUI::stepAjax($exception->getMessage(), UI_MSG_ERROR);
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("extractPassages", $extractPassages);
$smarty->display("extract_viewer");
