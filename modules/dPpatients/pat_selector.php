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
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$nom           = CView::get("nom", "str");
$prenom        = CView::get("prenom", "str");
$dateNaissance = CView::get("dateNaissance", "date");
$patient_year  = CView::get("Date_Year", "num");
$patient_month = CView::get("Date_Month", "num");
$patient_day   = CView::get("Date_Day", "num");
$patient_ipp   = CView::get("patient_ipp", "str");
$useVitale     = CView::get("useVitale", "bool default|0");
$parturiente   = CView::get("parturiente", "bool default|0");
$patient_id    = CView::get("patient_id", "ref class|CPatient");

CView::checkin();

$patient = new CPatient();
if (!$patient_id || !$patient->load($patient_id)) {
  $patient->_IPP      = $patient_ipp;
  $patient->nom       = $nom;
  $patient->prenom    = $prenom;
  $patient->naissance = $dateNaissance ? $dateNaissance : "$patient_year-$patient_month-$patient_day";
}

$patVitale = null;

// Gestion du cas vitale
if ($useVitale && CModule::getActive("fse") && CAppUI::pref('LogicielLectureVitale') === 'none') {
  $patVitale = new CPatient();
  $cv        = CFseFactory::createCV();
  if ($cv) {
    $cv->loadFromIdVitale($patVitale);
    $cv->getPropertiesFromVitale($patVitale);
    $patient->nom       = $patVitale->nom;
    $patient->prenom    = $patVitale->prenom;
    $patient->naissance = $patVitale->naissance;
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("useVitale", $useVitale);
$smarty->assign("patVitale", $patVitale);
$smarty->assign("parturiente", $parturiente);
$smarty->assign("patient", $patient);
$smarty->display("pat_selector.tpl");
