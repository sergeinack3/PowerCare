<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Pmsi\CRelancePMSI;
use Ox\Mediboard\Pmsi\PmsiExporter;
use Ox\Mediboard\Pmsi\PMSIService;
use Ox\Mediboard\Pmsi\RelancePMSIService;
use Ox\Mediboard\Pmsi\Services\PMSIRelanceService;
use Ox\Mediboard\System\Forms\CExClassEvent;

/**
 * Legacy Controller PMSI
 */
class CPMSILegacyController extends CLegacyController
{
    /**
     * Export des opérations planifiées
     *
     * @throws Exception
     */
    public function exportCsvPlannedOperations(): void
    {
        $this->checkPermRead();

        $date_min = CView::get("date_min", "date default|" . CMbDT::date('-1 day'), true);
        $date_max = CView::get("date_max", "date default|now", true);
        $types    = CView::get("types", "str", true);

        CView::checkin();

        try {
            (new PMSIExporter())->exportOperationsToCsv($date_min, $date_max, $types, PMSIExporter::CURRENT_OPERATION);
        } catch (Exception $e) {
            CAppUI::stepAjax($e->getMessage());
        }

        CApp::rip();
    }

    /**
     * Displays a patient's stay
     *
     * @return void
     * @throws CMbModelNotFoundException
     */
    public function viewStayDossier(): void
    {
        $this->checkPermEdit();

        $patient_id = CView::get('patient_id', 'ref class|CPatient');
        $sejour_id  = CView::get('sejour_id', 'ref class|CSejour');

        CView::checkin();

        $pmsi_service  = (new PMSIService())->getStayDossierPMSI($patient_id, $sejour_id);
        $patient       = $pmsi_service["patient"];
        $sejour        = $pmsi_service["sejour"];
        $sejour_maman  = $pmsi_service["sejour_maman"];
        $naissance_enf = $pmsi_service["naissance_enf"];

        $form_tabs = [];
        if (CModule::getActive("forms")) {
            $objects = [
                [
                    "tab_dossier_soins",
                    $sejour,
                ],
            ];

            $form_tabs = CExClassEvent::getTabEvents($objects);
        }

        if (is_array($sejour->_ref_suivi_medical)) {
            krsort($sejour->_ref_suivi_medical);
        }

        $this->renderSmarty(
            "inc_vw_dossier_sejour",
            [
                "patient"          => $patient,
                "sejour"           => $sejour,
                "sejour_maman"     => $sejour_maman,
                "naissance"        => $naissance_enf,
                "form_tabs"        => $form_tabs,
                "canPatients"      => CModule::getCanDo("dPpatients"),
                "hprim21installed" => CModule::getActive("hprim21"),
                "isImedsInstalled" => (CModule::getActive("dPImeds") && CImeds::getTagCIDC(CGroups::loadCurrent())),
            ]
        );
    }

    public function ajax_vw_relances()
    {
        $this->checkPermRead();

        $chir_id     = CView::get("chir_id", "ref class|CMediusers");
        $function_id = CView::get("function_id", "ref class|CFunctions");

        CView::checkin();

        $relance_pmsi_service = new RelancePMSIService();
        if ($chir_id) {
            $relances = $relance_pmsi_service->getRelanceFromUserOrFunction("user", $chir_id);
        } else {
            $relances = $relance_pmsi_service->getRelanceFromUserOrFunction("function", $function_id);
        }

        $this->renderSmarty(
            "inc_vw_relances",
            [
                "relances" => $relances,
            ]
        );
    }
}
