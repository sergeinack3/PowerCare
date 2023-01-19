<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CMergeLog;
use Throwable;

class CHospitalizeController extends CLegacyController
{
    /**
     * Hospitalize the patient
     */
    public function ajax_hospitalize(): void
    {
        CCanDo::checkEdit();

        $consult_id = CView::get("consult_id", "ref class|CConsultation");

        CView::checkin();

        $consult = new CConsultation();
        $consult->load($consult_id);

        CAccessMedicalData::logAccess($consult);

        $sejour  = $consult->loadRefSejour();
        $patient = $consult->loadRefPatient();

        $sejour->loadRefPraticien();

        if ($sejour->_ref_praticien->isSageFemme()) {
            $sejour->praticien_id   = "";
            $sejour->_ref_praticien = new CMediusers();
        }

        $use_custom_mode_entree = CAppUI::conf("dPplanningOp CSejour use_custom_mode_entree");

        $modes_entree = CModeEntreeSejour::listModeEntree($sejour->group_id);

        if ($use_custom_mode_entree && count($modes_entree)) {
            foreach ($modes_entree as $_mode_entree) {
                if ($_mode_entree->code == "8") {
                    $sejour->mode_entree_id = $_mode_entree->_id;
                    break;
                }
            }
        } else {
            $sejour->mode_entree = "8";
        }

        $sejour->uf_soins_id = CAppUI::gconf("maternite placement uf_soins_id_dhe");

        $sejour->type = "comp";
        $collisions   = $sejour->getCollisions();

        $sejours_futur    = [];
        $count_collision  = count($collisions);
        $sejour_collision = null;
        $check_merge      = null;

        if ($count_collision == 1) {
            $sejour_collision = current($collisions);
            $sejour_collision->loadRefPraticien();

            try {
                $sejour->checkMerge($collisions);
                $check_merge = null;
            } catch (Throwable $t) {
                $check_merge = $t->getMessage();
            }
        } else {
            $where = [
                "entree_reelle" => "IS NULL",
                "sejour_id"     => "!= '$sejour->_id'",
                "patient_id"    => "= '$patient->_id'",
                "grossesse_id"  => "IS NOT NULL",
                "annule"        => "= '0'",
            ];

            /** @var CSejour[] $sejours_futur */
            $sejours_futur = $sejour->loadList($where, "entree DESC");
            foreach ($sejours_futur as $_sejour_futur) {
                $_sejour_futur->loadRefPraticien()->loadRefFunction();
            }
        }

        $this->renderSmarty(
            'inc_hospitalize',
            [
                'sejour'           => $sejour,
                'modes_entree'     => $modes_entree,
                'count_collision'  => $count_collision,
                'sejours_futur'    => $sejours_futur,
                'sejour_collision' => $sejour_collision,
                'check_merge'      => $check_merge,
                'affectations'     => [],
                'ufs'              => CUniteFonctionnelle::getUFs($sejour),
            ]
        );
    }

    public function hospitalizeParturiente()
    {
        $this->checkPermRead();

        $sejour_id       = CView::post("sejour_id", "ref class|CSejour");
        $sejour_id_merge = CView::post("sejour_id_merge", "ref class|CSejour");
        $praticien_id    = CView::post("praticien_id", "ref class|CMediusers");
        $uf_soins_id     = CView::post("uf_soins_id", "ref class|CUniteFonctionnelle");
        $mode_entree     = CView::post("mode_entree", "str");
        $mode_entree_id  = CView::post("mode_entree_id", "ref class|CModeEntreeSejour");
        $ATNC            = CView::post("ATNC", "bool");

        CView::checkin();

        $sejour = CSejour::findOrFail($sejour_id);

        $sejour->_create_affectations = false;
        $sejour->_apply_sectorisation = false;

        $sejour->type           = "comp";
        $sejour->praticien_id   = $praticien_id;
        $sejour->uf_soins_id    = $uf_soins_id;
        $sejour->mode_entree    = $mode_entree;
        $sejour->mode_entree_id = $mode_entree_id;

        if ($ATNC !== null) {
            $sejour->ATNC = $ATNC;
        }

        if (!$sejour->mode_entree && !$sejour->mode_entree_id) {
            $use_custom_mode_entree = CAppUI::conf("dPplanningOp CSejour use_custom_mode_entree");

            $modes_entree = CModeEntreeSejour::listModeEntree($sejour->group_id);

            if ($use_custom_mode_entree && count($modes_entree)) {
                foreach ($modes_entree as $_mode_entree) {
                    if ($_mode_entree->code == "8") {
                        $sejour->mode_entree_id = $_mode_entree->_id;
                        break;
                    }
                }
            } else {
                $sejour->mode_entree = "8";
            }
        }

        if ($sejour_id_merge) {
            $sejour_merge = CSejour::findOrFail($sejour_id_merge);

            $sejour_merge->_create_affectations = false;
            $sejour_merge->_apply_sectorisation = false;

            foreach ($sejour_merge->loadRefsAffectations() as $_affectation) {
                $_affectation->delete();
            }

            $duree                 = CMbDT::daysRelative($sejour_merge->entree, $sejour_merge->sortie);
            $sejour->sortie_prevue = CMbDT::dateTime("+$duree days", $sejour->entree_prevue);

            $merge_log = CMergeLog::logStart(CUser::get()->_id, $sejour, [$sejour_merge], false);

            try {
                $sejour->merge([$sejour_merge], false, $merge_log);
                $merge_log->logEnd();
            } catch (Throwable $t) {
                $merge_log->logFromThrowable($t);

                CAppUI::setMsg($t->getMessage(), UI_MSG_ERROR);
                echo CAppUI::getMsg();
                CApp::rip();
            }

            CAppUI::setMsg("CSejour-Sejours merged", UI_MSG_OK);
        } else {
            $msg = $sejour->store();

            CAppUI::setMsg($msg ?: CAppUI::tr("CSejour-msg-modify"), $msg ? UI_MSG_ALERT : UI_MSG_OK);
        }

        echo CAppUI::getMsg();

        CAppUI::callbackAjax("Control.Modal.close");
        CAppUI::callbackAjax("Sejour.editModal", $sejour->_id);
    }
}
