<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Ccam\CDatedCodeCCAM;

CCanDo::checkRead();

$codeacte = CValue::getOrSession("code");
$callback = CValue::getOrSession("callback");

// Chargement du code
$code = CDatedCodeCCAM::get($codeacte);

if (!$code->code) {
  $tarif = 0;
  CAppUI::stepAjax("$codeacte: code inconnu", UI_MSG_ERROR);
}

// si le code CCAM est complet (activite + phase), on selectionne le tarif correspondant
if ($code->_activite != "" && $code->_phase != "") {
  $tarif = $code->activites[$code->_activite]->phases[$code->_phase]->tarif;
}
// sinon, on prend le tarif par default
else {
  $tarif = $code->_default;
}

CAppUI::callbackAjax($callback, $tarif);
CAppUI::stepAjax("$codeacte: $tarif");
