<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CAntecedent;

CCanDo::checkRead();
$antecedent_id = CValue::post("antecedent_id");
$callback      = CValue::post("reload");

$antecedent = new CAntecedent();
$antecedent->load($antecedent_id);
$antecedent->annule = 1;
if ($msg = $antecedent->store()) {
  CAppUI::stepAjax($msg, UI_MSG_WARNING);
}

$atcd_new                   = $antecedent;
$atcd_new->_id              = null;
$atcd_new->dossier_tiers_id = null;
$atcd_new->owner_id         = CMediusers::get()->_id;
$atcd_new->creation_date    = "now";
$atcd_new->annule           = 0;
if ($msg = $atcd_new->store()) {
  CAppUI::stepAjax($msg, UI_MSG_WARNING);
}

$dossier_medical = $atcd_new->loadRefDossierMedical();
CAppUI::callbackAjax("Antecedent.editAntecedents", $dossier_medical->object_id, '', $callback, $atcd_new->_id);

echo CAppUI::getMsg();
CApp::rip();