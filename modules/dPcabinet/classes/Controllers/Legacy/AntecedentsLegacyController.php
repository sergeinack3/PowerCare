<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Controllers\Legacy;

use Exception;
use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CDossierTiers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CTraitement;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;

class AntecedentsLegacyController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function listAntecedents(): void
    {
        $this->checkPerm();
        $sejour_id         = CView::get("sejour_id", "ref class|CSejour", true);
        $grossesse_id      = CView::get("grossesse_id", "ref class|CGrossesse");
        $patient_id        = CView::get("patient_id", "ref class|CPatient", true);
        $show_header       = CView::get("show_header", "bool default|0", true);
        $dossier_anesth_id = CView::get("dossier_anesth_id", "num", true);
        $context_date_min  = CView::get('context_date_min', 'date');
        $context_date_max  = CView::get('context_date_max', 'date');
        $object_class      = CView::get('object_class', 'str');
        $object_id         = CView::get('object_id', 'ref meta|object_class');
        $readonly          = CView::get("readonly", "bool");

        CView::checkin();

        $object = null;
        if ($object_class && $object_id) {
            $object = CMbObject::loadFromGuid("$object_class-$object_id");
        }

        $sejour  = new CSejour();
        $patient = new CPatient();

        if ($sejour_id && !$grossesse_id) {
            $sejour->load($sejour_id);
            CAccessMedicalData::logAccess($sejour);
            $patient = $sejour->loadRefPatient();
        } elseif ($grossesse_id) {
            $grossesse  = CGrossesse::findOrFail($grossesse_id);
            $patient    = $grossesse->loadRelPatient();
            $patient_id = $patient->_id;
        } elseif ($patient_id) {
            $patient->load($patient_id);
        }

        $patient->loadRefPhotoIdentite();
        $patient->loadRefDossierMedical()->loadRefsAntecedents();
        $patient->loadRefFamilyPatient();

        // Read-only view if user cannot edit
        if ($readonly || ($patient->_ref_dossier_medical && $patient->_ref_dossier_medical->_id)) {
            if (!$patient->_ref_dossier_medical || !$patient->_ref_dossier_medical->_id) {
                $patient->_ref_dossier_medical = new CDossierMedical();
            }

            $patient->_ref_dossier_medical->canDo();

            if ($readonly || !$patient->_ref_dossier_medical->canDo()->edit) {
                $patient->_ref_dossier_medical->loadRefPrescription();
                $smarty = new CSmartyDP("modules/dPpatients");
                $smarty->assign("object", $patient->_ref_dossier_medical);
                $smarty->display("CDossierMedical_complete.tpl");

                return;
            }
        }

        $patient->loadLastGrossesse();
        $patient->loadLastAllaitement();
        $patient->canDo();

        $isPrescriptionInstalled = CModule::getActive("dPprescription") && CPrescription::isMPMActive();
        $count_new_antecedents   = 0;

        // AppFine dossier tiers
        $appfine_module = CModule::getActive("appFineClient");

        if ($appfine_module) {
            $patient->loadRefStatusPatientUser();

            $name_dossier_tiers_appFine = CAppFineClient::NAME_DOSSIER_TIERS;
            $patient->loadRefsDossierTiers(["name" => " = '$name_dossier_tiers_appFine'"]);
            $dossier_tiers_appFine = reset($patient->_ref_dossiers_tiers);

            // Si le patient n'a pas de dossier tiers AppFine, on lui en créé un
            if (!$dossier_tiers_appFine) {
                $dossier_tiers_appFine               = new CDossierTiers();
                $dossier_tiers_appFine->object_id    = $patient->_id;
                $dossier_tiers_appFine->object_class = $patient->_class;
                $dossier_tiers_appFine->name         = "$name_dossier_tiers_appFine";

                $dossier_tiers_appFine->store();
            }

            if ($dossier_tiers_appFine && $dossier_tiers_appFine->_id) {
                $antecedent                  = new CAntecedent();
                $where                       = [];
                $where["dossier_tiers_id"]   = " = '$dossier_tiers_appFine->_id '";
                $where["dossier_medical_id"] = " IS NULL";

                $count_new_antecedents = $antecedent->countList($where);
            } else {
                $count_new_antecedents = 0;
            }

            // Récupération du nombre de constantes dans le sas
            $constanteMedicale      = new CConstantesMedicales();
            $where                  = [];
            $where["patient_id"]    = " = '$patient_id'";
            $where["context_id"]    = " = '$dossier_tiers_appFine->_id'";
            $where["context_class"] = "= '$dossier_tiers_appFine->_class'";
            $countConstantes        = $constanteMedicale->countList($where);
            if ($countConstantes) {
                $count_new_antecedents += $countConstantes;
            }

            $prescription               = new CPrescription();
            $prescription->object_class = $dossier_tiers_appFine->_class;
            $prescription->object_id    = $dossier_tiers_appFine->_id;
            $prescription->loadMatchingObject();

            $prescription->loadRefsLinesMed();

            if ($dossier_tiers_appFine->absence_traitement) {
                $count_new_antecedents++;
            } else {
                $count_new_antecedents = $count_new_antecedents + count($prescription->_ref_prescription_lines);
            }
        }

        $this->renderSmarty(
            'inc_ant_consult.tpl',
            [
                'line'                    => $isPrescriptionInstalled ? new CPrescriptionLineMedicament() : null,
                'current_m'               => 'dPcabinet',
                'sejour_id'               => $sejour->_id,
                'patient'                 => $patient,
                'antecedent'              => new CAntecedent(),
                'traitement'              => new CTraitement(),
                '_is_anesth'              => '1',
                'userSel'                 => CMediusers::get(),
                'today'                   => CMbDT::date(),
                'isPrescriptionInstalled' => $isPrescriptionInstalled,
                'sejour'                  => $sejour,
                'show_header'             => $show_header,
                'dossier_anesth_id'       => $dossier_anesth_id,
                'context_date_min'        => $context_date_min,
                'context_date_max'        => $context_date_max,
                'object'                  => $object,
                'drc'                     => array_key_exists('drc', CAppUI::conf('db')),
                'cisp'                    => array_key_exists('cisp', CAppUI::conf('db')),
                'count_new_antecedents'   => $appfine_module ? $count_new_antecedents : null,
            ]
        );
    }
}
