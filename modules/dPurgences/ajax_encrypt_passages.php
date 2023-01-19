<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Ror\CRORException;
use Ox\Interop\Ror\CRORFactory;
use Ox\Mediboard\Urgences\CExtractPassages;

CCanDo::checkAdmin();

$extract_passages_id = CValue::get("extract_passages_id");
$view                = CValue::get("view", 0);

if (isset($extractPassages) && $extractPassages->_id) {
  $extract_passages_id = $extractPassages->_id;
}

$extractPassages = new CExtractPassages();
if ($extract_passages_id) {
  $extractPassages->load($extract_passages_id);
}

if (!$extractPassages->_id) {
  CAppUI::stepAjax("Impossible de charger le document XML.", UI_MSG_ERROR);
}

if (!$extractPassages->message_valide) {
  CAppUI::stepAjax("Impossible d'encrypter le message XML car le message n'est pas valide.", UI_MSG_ERROR);
}

// Appel de la fonction d'extraction du RPUSender
try {
  $rpuSender = CRORFactory::getSender();
  if ($extractPassages->type == "activite") {
    $extractPassages = $rpuSender->encryptActivite($extractPassages);
  }
  elseif ($extractPassages->type == "urg") {
    $extractPassages = $rpuSender->encryptUrg($extractPassages);
  }
  elseif ($extractPassages->type == "deces") {
    $extractPassages = $rpuSender->encryptDeces($extractPassages);
  }
  elseif ($extractPassages->type == "tension") {
    $extractPassages = $rpuSender->encryptTension($extractPassages);
  }
  elseif ($extractPassages->type == "litsChauds") {
    $extractPassages = $rpuSender->encryptLitsChauds($extractPassages);
  }
  else {
    $extractPassages = $rpuSender->encryptRPU($extractPassages);
  }
}
catch (CRORException|Exception $exception) {
  CAppUI::stepAjax($exception->getMessage(), UI_MSG_WARNING);
  CApp::log($exception->getMessage(), UI_MSG_ERROR);
}

if ($view) {
  $extractPassages->loadRefsFiles();

  // Création du template
  $smarty = new CSmartyDP("modules/dPurgences");
  $smarty->assign("_passage", $extractPassages);

  $smarty->display("inc_extract_file");
}
else {
  echo "<script>RPU_Sender.extract_passages_id = $extractPassages->_id;</script>";
}

