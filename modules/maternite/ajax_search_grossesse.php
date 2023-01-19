<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$page          = CView::get("page", "num default|0");
$lastname      = CView::get("nom", "str");
$firstname     = CView::get("prenom", "str");
$patient_day   = CView::get("Date_Day", "numchar");
$patient_month = CView::get("Date_Month", "numchar");
$patient_year  = CView::get("Date_Year", "numchar");
$cp            = CView::get("cp", "numchar");
$ville         = CView::get("ville", "str");
$patient_ipp   = CView::get("patient_ipp", 'str');
$patient_nda   = trim(CView::get("patient_nda", 'str'));

$terme       = CView::get("terme_date", "date");
$terme_start = CView::get("terme_start", "date");
$terme_end   = CView::get("terme_end", "date");

$num_semaines = CView::get("num_semaines", "num");
$multiple     = CView::get("multiple", "bool");

CView::checkin();

// where
$where = array();
if ($patient_ipp || $patient_nda) {
  $oatient = new CPatient();
  $patient->getByIPPNDA($patient_ipp, $patient_nda);

  $where["parturiente_id"] = "= '$patient->_id'";
}
else {
  if ($lastname) {
    $where["nom"] = "LIKE '$lastname%'";
  }
  if ($firstname) {
    $where["prenom"] = "LIKE '$firstname%'";
  }

  if ($patient_year || $patient_month || $patient_day) {
    $patient_naissance =
      CValue::first($patient_year, "%") . "-" .
      CValue::first($patient_month, "%") . "-" .
      CValue::first($patient_day, "%");

    $where["naissance"] = "LIKE '$patient_naissance'";
  }
}

if ($terme) {
  $where["terme_prevu"] = " = '$terme'";
}
else {
  if ($terme_start && $terme_end) {
    $where["terme_prevu"] = "BETWEEN '$terme_start' AND '$terme_end' ";
  }
  elseif ($terme_start && !$terme_end) {
    $where["terme_prevu"] = ">= '$terme_start'";
  }
  elseif (!$terme_start && $terme_end) {
    $where["terme_prevu"] = "<= '$terme_end'";
  }
}

if ($num_semaines !== '') {
  $where["num_semaines"] = "= '$num_semaines'";
}
if ($multiple !== '') {
  $where["multiple"] = "= '$multiple'";
}

$grossesse = new CGrossesse();
$ljoin     = array("patients" => "patients.patient_id = grossesse.parturiente_id");

$nb_grossesses = $grossesse->countList($where, null, $ljoin);
$grossesses    = $grossesse->loadList($where, "nom, prenom", "$page, 30", null, $ljoin);

CStoredObject::massLoadFwdRef($grossesses, "parturiente_id");

$ljoin = array(
  "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id"
);
CStoredObject::massLoadBackRefs($grossesses, "consultations", "date DESC, heure DESC", null, $ljoin);

/** @var CGrossesse[] $grossesses */
foreach ($grossesses as $_grossesse) {
  $_grossesse->loadRefParturiente();
  $_grossesse->loadLastConsult();
}

$prats = array();


// Création du template
$smarty = new CSmartyDP();

$smarty->assign("grossesses", $grossesses);
$smarty->assign("nb_grossesses", $nb_grossesses);
$smarty->assign("page", $page);

$smarty->display("inc_list_search_grossesse");