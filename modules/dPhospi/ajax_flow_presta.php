<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$flow = CPrestation::generateFlow($sejour);

foreach ($sejour->_ref_prestations as $date => $prestations) {
    foreach ($prestations as $key => $_prestation) {
        $sejour->_ref_prestations[$date][$key]["item"] = $sejour->_ref_prestations[$date][$key]["item"]->nom;

        if ($sejour->_ref_prestations[$date][$key]["sous_item_facture"]) {
            $sejour->_ref_prestations[$date][$key]["sous_item_facture"] =
                $sejour->_ref_prestations[$date][$key]["sous_item_facture"]->nom;
        }
    }
}

CApp::log("Prestations", $sejour->_ref_prestations);
CApp::log("Flux", $flow);
