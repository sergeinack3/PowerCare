<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Cabinet\Vaccination\CVaccinRepository;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id = CView::getRefCheckEdit("patient_id", "ref class|CPatient");

CView::checkin();

$vaccinated = [];
$patient    = CPatient::findOrFail($patient_id);

$full_age = $patient->getRestAge();

$vaccs_rep        = new CVaccinRepository();
$vaccines         = $vaccs_rep->getAll();
$vaccines_dates   = $vaccs_rep->getDates();
$vaccines_colors  = $vaccs_rep->getColorsPerType();
$vaccines_recalls = $vaccs_rep->getRecalls();

$injections = $patient->loadRefInjections();
foreach ($injections as $_injection) {
    $_injection->loadRefVaccinations();

    foreach ($_injection->_ref_vaccinations as $_vacination) {
        $_vacination->loadRefVaccine();
        $_vacination->_ref_injection = $_injection;

        if ($_vacination->_ref_vaccine) {
            foreach ($_vacination->_ref_vaccine->recall as $_recall) {
                $_recall->getRecallAge();
            }
        }
    }
    if ($_injection->speciality != "N/A" || $_injection->batch != "N/A") {
        $vaccinated[] = $_injection->_id;
    }
}

$vaccinations_tab = CInjection::generateArray($injections, $vaccs_rep->getAll(), $vaccs_rep->getRecalls());

$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);
$smarty->assign("full_age", $full_age);
$smarty->assign("vaccines", $vaccines);
$smarty->assign("vaccines_dates", $vaccines_dates);
$smarty->assign("vaccines_colors", $vaccines_colors);
$smarty->assign("vaccinations_array", $vaccinations_tab);
$smarty->assign("vaccinated", $vaccinated);
$smarty->assign("vaccination_rep", $vaccs_rep);

$smarty->display("vaccination/vw_vaccins");
