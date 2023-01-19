<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CDiscipline;

CAppUI::requireModuleFile("dPhospi", "inc_vw_affectations");

$affichage_patho = CValue::postOrSession("affichage_patho", "non_complet");

$date       = CValue::getOrSession("date", CMbDT::date());
$pathos     = new CDiscipline();
$heureLimit = "16:00:00";

// Si la date en session vient de la vue temporelle (datetime), on retransforme en date
if (strpos($date, " ") !== false) {
  $date = CMbDT::date($date);
}

// Liste des patients à placer
$groupSejourNonAffectes = array();

if (CCanDo::edit()) {
  // Admissions de la veille
  $dayBefore = CMbDT::date("-1 days", $date);
  $where     = array(
    "entree_prevue" => "BETWEEN '$dayBefore 00:00:00' AND '$date 00:00:00'",
    "type"          => "!= 'exte'",
    "annule"        => "= '0'"
  );

  $groupSejourNonAffectes["veille"] = loadSejourNonAffectes($where);

  // Admissions du matin
  $where = array(
    "entree_prevue" => "BETWEEN '$date 00:00:00' AND '$date " . CMbDT::time("-1 second", $heureLimit) . "'",
    "type"          => "!= 'exte'",
    "annule"        => "= '0'"
  );

  $groupSejourNonAffectes["matin"] = loadSejourNonAffectes($where);

  // Admissions du soir
  $where = array(
    "entree_prevue" => "BETWEEN '$date $heureLimit' AND '$date 23:59:59'",
    "type"          => "!= 'exte'",
    "annule"        => "= '0'"
  );

  $groupSejourNonAffectes["soir"] = loadSejourNonAffectes($where);

  // Admissions antérieures
  $twoDaysBefore = CMbDT::date("-2 days", $date);
  $where         = array(
    "annule" => "= '0'",
    "'$twoDaysBefore' BETWEEN entree_prevue AND sortie_prevue"
  );

  $groupSejourNonAffectes["avant"] = loadSejourNonAffectes($where);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("affichage_patho", $affichage_patho);
$smarty->assign("pathos", $pathos);
$smarty->assign("date", $date);
$smarty->assign("yesterday", CMbDT::date("-1 day", $date));
$smarty->assign("tomorow", CMbDT::date("+1 day", $date));
$smarty->assign("heureLimit", $heureLimit);
$smarty->assign("groupSejourNonAffectes", $groupSejourNonAffectes);
$smarty->display("vw_idx_pathologies.tpl");

