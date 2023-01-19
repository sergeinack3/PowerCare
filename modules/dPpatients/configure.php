<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Cli\Console\CZipCodeImport;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

// Types et Appareils
$active_types        = explode('|', CAppUI::conf("dPpatients CAntecedent types"));
$mandatory_types     = explode('|', CAppUI::conf("dPpatients CAntecedent mandatory_types"));
$active_appareils    = explode('|', CAppUI::conf("dPpatients CAntecedent appareils"));
$all_types           = array_unique(array_merge(CAntecedent::$types, $active_types));
$all_mandatory_types = array_unique(array_merge(CAntecedent::$types, $mandatory_types));
$all_appareils       = array_unique(array_merge(CAntecedent::$appareils, $active_appareils));

// Départements des correspondants
$departements = array();
for ($i = 1; $i < 96; $i++) {
  $departements[] = str_pad($i, 2, "0", STR_PAD_LEFT);
}

// Ajout des DOM-TOM
$departements[] = "CS"; // Corse du Sud
$departements[] = "GD"; // Guadeloupe
$departements[] = "GY"; // Guyanne
$departements[] = "HC"; // Haute Corse
$departements[] = "MA"; // Martinique
$departements[] = "MY"; // Mayotte
$departements[] = "PS"; // Nouvelle Calédonie
$departements[] = "PF"; // Polynésie française
$departements[] = "RE"; // Réunion
$departements[] = "PM"; // Saitn Pierre et Miquelon
$departements[] = "WF"; // Wallis et Futuna

// Nombre de patients
$patient     = new CPatient();
$nb_patients = $patient->countList();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("nb_patients", $nb_patients);

$smarty->assign("active_types", $active_types);
$smarty->assign("mandatory_types", $mandatory_types);
$smarty->assign("active_appareils", $active_appareils);
$smarty->assign("all_types", $all_types);
$smarty->assign("all_mandatory_types", $all_types);
$smarty->assign("all_appareils", $all_appareils);

$smarty->assign("pass", CValue::get("pass"));
$smarty->assign("departements", $departements);
$smarty->assign("country_cp", CZipCodeImport::$countries);
$smarty->assign('separation_cab', CAppUI::isCabinet());
$smarty->assign('separation_group', CAppUI::isGroup());

$smarty->display("configure.tpl");
