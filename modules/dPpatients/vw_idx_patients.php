<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\CViewHistory;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();
$mediuser = CMediusers::get();

// Chargement du patient sélectionné
$patient_id = CView::get("patient_id", "ref class|CPatient", true);
$new        = CView::get("new", "bool");
$patient    = new CPatient;
if ($new) {
  $patient->load(null);
  CView::setSession("patient_id", null);
  CView::setSession("selClass", null);
  CView::setSession("selKey", null);
}
else {
  $patient->load($patient_id);
}

$patient_nom           = trim(CView::get("nom", "str", true));
$patient_prenom        = trim(CView::get("prenom", "str", true));
$patient_ville         = CView::get("ville", "str");
$patient_cp            = CView::get("cp", "numchar");
$patient_day           = CView::get("Date_Day", "numchar", true);
$patient_month         = CView::get("Date_Month", "numchar", true);
$patient_year          = CView::get("Date_Year", "num", true);
$patient_naissance     = "$patient_year-$patient_month-$patient_day";
$patient_ipp           = CView::get("patient_ipp", "numchar");
$patient_nda           = CView::get("patient_nda", "numchar");
$default_use_vital     = CModule::getActive("fse") && CAppUI::pref('LogicielLectureVitale') != 'none' ? 1 : 0;
$useVitale             = CView::get("useVitale", "bool default|$default_use_vital");
$prat_id               = CView::get("prat_id", "ref class|CMediusers");
$patient_sexe          = CView::get("sexe", "enum list|m|f");
$board                 = CView::get("board", "bool default|0");
$see_link_prat         = CView::get("see_link_prat", "bool default|0");
$patient_nom_search    = null;
$patient_prenom_search = null;

// Save history
$params = array(
  "new"         => $new,
  "patient_id"  => $patient_id,
  "nom"         => $patient_nom,
  "prenom"      => $patient_prenom,
  "ville"       => $patient_ville,
  "cp"          => $patient_cp,
  "Date_Day"    => $patient_day,
  "Date_Month"  => $patient_month,
  "Date_Year"   => $patient_year,
  "patient_ipp" => $patient_ipp,
  "patient_nda" => $patient_nda,
  "prat_id"     => $prat_id,
  "sexe"        => $patient_sexe,
);
CViewHistory::save($patient, CViewHistory::TYPE_SEARCH, $params);
CView::checkin();
$patVitale = new CPatient();

// Liste des praticiens
$prats = $mediuser->loadPraticiens();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("dPsanteInstalled", CModule::getInstalled("dPsante400"));

$smarty->assign("nom", $patient_nom);
$smarty->assign("prenom", $patient_prenom);
$smarty->assign("naissance", $patient_naissance);
$smarty->assign("ville", $patient_ville);
$smarty->assign("cp", $patient_cp);
$smarty->assign("nom_search", $patient_nom_search);
$smarty->assign("prenom_search", $patient_prenom_search);
$smarty->assign("sexe", $patient_sexe);
$smarty->assign("prat_id", $prat_id);
$smarty->assign("prats", $prats);
$smarty->assign("patient", $patient);

$smarty->assign("useVitale", $useVitale);
$smarty->assign("patVitale", $patVitale);

$smarty->assign("patient", $patient);
$smarty->assign("board", $board);
$smarty->assign("patient_ipp", $patient_ipp);
$smarty->assign("patient_nda", $patient_nda);
$smarty->assign("see_link_prat", $see_link_prat);

$smarty->display("vw_idx_patients.tpl");
