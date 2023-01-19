<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CPrestationJournaliere;

ini_set("memory_limit", "256M");

// PAS DE PASSAGE AU CVIEW CAR SOUCI DE SESSION !!!

$services_ids   = CValue::getOrSession("services_ids");
$granularite    = CValue::getOrSession("granularite", "day");
$date           = CValue::getOrSession("date", CMbDT::dateTime());
$date_min       = CValue::getOrSession("date_min");
$granularites   = ["day", "48hours", "72hours", "week", "4weeks"];
$triAdm         = CValue::getOrSession("triAdm", "praticien");
$mode_vue_tempo = CValue::getOrSession("mode_vue_tempo", "classique");
$readonly       = CValue::get("readonly");
$prestation_id  = CValue::getOrSession("prestation_id", CAppUI::pref("prestation_id_hospi"));

// Si la date en session vient de la vue tableau, on retransforme en datetime
if (strpos($date, " ") === false) {
    $date = $date . " " . CMbDT::time();
}

// Si c'est la préférence utilisateur, il faut la mettre en session
CValue::setSession("prestation_id", $prestation_id);

$prestations_journalieres = CPrestationJournaliere::loadCurrentList();

$smarty = new CSmartyDP();

$smarty->assign("date", $date);
$smarty->assign("date_min", $date_min);
$smarty->assign("granularites", $granularites);
$smarty->assign("granularite", $granularite);
$smarty->assign("mode_vue_tempo", $mode_vue_tempo);
$smarty->assign("prestations_journalieres", $prestations_journalieres);
$smarty->assign("prestation_id", $prestation_id);
$smarty->assign("readonly", $readonly);

$smarty->display("vw_mouvements.tpl");
