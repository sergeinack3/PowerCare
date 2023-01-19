<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CPlageGroupePatient;

CCanDo::checkEdit();
$plage_groupe_patient_id = CView::get("plage_groupe_patient_id", "ref class|CPlageGroupePatient");
$date                    = CView::get("day_used", "date default|now", true);
CView::checkin();
$plage_groupe_patient = CPlageGroupePatient::find($plage_groupe_patient_id);

$sejours          = $plage_groupe_patient->loadRefSejoursAssocies($date);
$sejours_associes = [];

foreach ($sejours as $_sejour) {
    $where = [
        "plage_groupe_patient_id" => " = '$plage_groupe_patient->_id'",
        "DATE(debut) = '" . $date . "'"
    ];
    $events_ssr = $_sejour->loadRefsEvtsSSRSejour($where);

    foreach ($events_ssr as $_event) {
        $line_element = $_event->loadRefPrescriptionLineElement();
        $codes_csarr  = $_event->loadRefsActesCsARR();

        foreach ($codes_csarr as $_csarr) {
            $sejours_associes[$_sejour->_id][$line_element->_id][$_event->_id][$_csarr->_id]  = $_csarr;
            $sejours_associes[$_sejour->_id][$line_element->_id][$_event->_id]["duree"]       = $_event->duree;
            $sejours_associes[$_sejour->_id][$line_element->_id][$_event->_id]["type_seance"] = $_event->type_seance;
            $sejours_associes[$_sejour->_id][$line_element->_id][$_event->_id]["executant"]   = $_event->therapeute_id;
        }
    }
}

CApp::json($sejours_associes);
