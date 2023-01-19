<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Controllers\Legacy;

use DateTimeImmutable;
use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\Repositories\ConsultationRepository;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CPreferences;

class ConsultationLegacyController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function getConsultation(): void
    {
        $this->checkPermRead();

        $consult_id = CView::get('consult_id', 'num');

        CView::checkin();

        $consultation = CConsultation::findOrNew($consult_id);

        $this->renderJson($consultation);
    }

    public function weeklyPlanning(): void
    {
        $this->checkPermRead();

        if (!CAppUI::pref('new_semainier')) {
            CAppUI::redirect('m=cabinet&tab=vw_planning');
        }

        $user_id = CView::get('chirSel', 'ref class|CMediusers', true);
        $function_id = CView::get('function_id', 'ref class|CFunctions', true);
        $debut = CView::get('debut', 'date default|' . CMbDT::date(), true);

        CView::checkin();

        $group = CGroups::loadCurrent();

        $user = CMediusers::get();
        /* Select the connecter user if it's a practitioner and no user is selected */
        if ($user->isProfessionnelDeSante() && !$user_id) {
            $user_id = $user->_id;
        } else {
            $user = CMediusers::findOrNew($user_id);
        }

        $listChir = [];
        $function = CFunctions::findOrNew($function_id);
        if ($function_id) {
            $listChir = CConsultation::loadPraticiens(PERM_EDIT, $function_id, null, true);
        }

        // get desistements
        $count_si_desistement = 0;
        if (count($listChir) || $user_id) {
            $count_si_desistement = CConsultation::countDesistementsForDay(
                count($listChir) ? array_keys($listChir) : [$user_id],
                CMbDT::date()
            );
        }

        $debut = CMbDT::date('last sunday', $debut);

        $this->renderSmarty('vw_planning_new.tpl', [
            'listChirs'            => $listChir,
            'today'                => CMbDT::date(),
            'debut'                => CMbDT::date('+1 day', $debut),
            'fin'                  => CMbDT::date('next sunday', $debut),
            'prev'                 => CMbDT::date('-1 week', $debut),
            'next'                 => CMbDT::date('+1 week', $debut),
            'chirSel'              => $user_id,
            'user'                 => $user,
            'function'             => $function,
            'canEditPlage'         => (new CPlageconsult())->getPerm(PERM_EDIT),
            'count_si_desistement' => $count_si_desistement,
        ]);
    }

    public function refreshListConsultationsSejour()
    {
        $this->checkPermRead();
        $sejour_id = CView::get("sejour_id", "ref class|CSejour", true);
        CView::checkin();

        /** @var CSejour $sejour */
        $sejour = CSejour::findOrNew($sejour_id);
        if (isset($sejour->_id)) {
            $sejour->getPerm(PERM_READ);
            $sejour->loadRefsFwd();
            $sejour->loadRefsConsultations();

            CStoredObject::massLoadBackRefs($sejour->_ref_consultations, "context_ref_brancardages");

            /** @var CConsultation $_consultation */
            foreach ($sejour->_ref_consultations as $_consultation) {
                $_consultation->loadRefPraticien();
                $_consultation->loadRefsBrancardages();
            }
        }

        $params = [];
        $params["sejour"] = $sejour;
        $this->renderSmarty("inc_infos_consultation_sejour", $params);
    }

    public function addConsultationSuiviPatient(): void
    {

        $this->checkPermRead();
        $patient_id    = CView::get("patient_id", "ref class|CPatient");
        $callback      = CView::get("callback", "str");
        CView::checkin();

        $patient = new CPatient();
        $patient->load($patient_id);

        $consult = new CConsultation();
        $consult->_datetime = CMbDT::dateTime();
        $consult->type_consultation = "suivi_patient";
        $consult->patient_id = $patient->_id;

        $praticiens = CConsultation::loadPraticiens(PERM_EDIT);

        $this->renderSmarty(
            "inc_consult_suivi_patient.tpl",
            [
                "patient"    => $patient,
                "consult"    => $consult,
                "praticiens" => $praticiens,
                "callback"   => $callback,

            ]
        );
    }

    /**
     * Check if there are consultations for the practitioner on the same date
     * @return void
     * @throws Exception
     */
    public function consultValidation(): void
    {
        $this->checkPermEdit();

        $datetime    = CView::get("datetime", "dateTime default|now");
        $prat_id     = CView::get("prat_id", "ref class|CMediusers");
        $patient_id  = CView::get("patient_id", "ref class|CPatient");
        $action      = CView::get("action", "str default|immediat");
        $callback    = CView::get("callback", "str");

        CView::checkin();

        $date = CMbDT::format($datetime, "%Y-%m-%d");

        $consultations = [];
        $praticien = CMediusers::findOrNew($prat_id);

        if ($datetime && $prat_id && $patient_id) {
            $patient = CPatient::findOrFail($patient_id);
            $date = new DateTimeImmutable($date);

            $repository = new ConsultationRepository();
            $consultations = $repository->getListConsultByDateAndPraticianForPatient($patient, $praticien, $date);
            CMbObject::massLoadBackRefs($consultations, "consult_anesth");

            foreach ($consultations as $_consultation) {
                $_consultation->loadRefConsultAnesth();
            }
        }

        $template = "inc_consult_immediate_validation";
        if ($action === "duplicate") {
            $template = "consultation/inc_duplicate_consult_validation";
        }

        $this->renderSmarty(
            $template,
            [
                "selected_date"      => $datetime,
                "selected_praticien" => $praticien,
                "consultations"      => $consultations,
                "callback"           => $callback,
            ]
        );
    }

    /**
     * Show modal with date and practitioner for duplication
     * @return void
     * @throws \Ox\Core\CMbModelNotFoundException
     */
    public function showDuplicateRdv(): void
    {
        $this->checkPermRead();

        $patient_id    = CView::get("patient_id", "ref class|CPatient");
        $callback      = CView::get("callback", "str");
        $consult_id    = CView::get("consult_id", "ref class|CConsultation");

        CView::checkin();

        $patient = CPatient::findOrFail($patient_id);
        $consult = CConsultation::findOrFail($consult_id);
        $ds = $consult->getDS();
        $consult->loadRefPlageConsult();

        $dateConsult  = $consult->_date;
        $heureConsult = $consult->heure;

        $where = [
            "plageconsult.date" => $ds->prepare("= ?", CMbDT::date($dateConsult)),
            "consultation.type_consultation" => $ds->prepare(" = ?", "consultation")
        ];

        $patient->loadRefsConsultations($where);
        CStoredObject::massLoadFwdRef($patient->_ref_consultations, "plageconsult_id");

        foreach ($patient->_ref_consultations as $_consult) {
            $plage = $_consult->loadRefPlageConsult();
            $plage->_ref_chir->loadRefFunction();
        }

        $newConsult = new CConsultation();
        $newConsult->_datetime  = CMbDT::dateTime($dateConsult." ".$heureConsult);
        $newConsult->patient_id = $patient->_id;

        $praticiens = CConsultation::loadPraticiens(PERM_EDIT);
        $prefs      = CPreferences::getAllPrefsUsers($praticiens);

        foreach ($praticiens as $_prat) {
            if ($prefs[$_prat->user_id]["allowed_new_consultation"] == 0) {
                unset($praticiens[$_prat->_id]);
            }
        }

        CConsultation::guessUfMedicaleMandatory($praticiens);

        $this->renderSmarty(
            "consultation/inc_duplicate_consult.tpl",
            [
                "patient"    => $patient,
                "consult"    => $consult,
                "newConsult" => $newConsult,
                "praticiens" => $praticiens,
                "callback"   => $callback,
            ]
        );
    }

    /**
     * Duplicate consultation
     * @return void
     * @throws \Ox\Core\CMbModelNotFoundException
     */
    public function duplicateConsultation(): void
    {
        $this->checkPermEdit();

        $prat_id     = CView::get("_prat_id", "ref class|CMediusers");
        $patient_id  = CView::get("patient_id", "ref class|CPatient");
        $_datetime   = CView::get("_datetime", "dateTime default|now");
        $consult_id  = CView::get("consult_id", "ref class|CConsultation");
        $function_id = CView::get('_function_id', "ref class|CFunctions");
        $callback    = CView::get('callback', "str");

        CView::checkin();

        if (!$_datetime || $_datetime == "now") {
            $_datetime = CMbDT::dateTime();
        }
        
        $chir = CMediusers::findOrFail($prat_id);

        if (!$chir->_id) {
            CAppUI::setMsg("CConsultation-msg-choose a doctor", UI_MSG_ERROR);
        }

        $consult = CConsultation::findOrFail($consult_id);

        $newConsult = new CConsultation();
        $newConsult->createByDatetime(
            $_datetime,
            $chir->_id,
            $patient_id,
            1,
            CConsultation::PATIENT_ARRIVE,
            1,
            null,
            null,
            null,
            $function_id
        );
        $newConsult->motif = $consult->motif;

        if ($msg = $newConsult->store()) {
            CAppUI::setMsg($msg, UI_MSG_ERROR);
        } else {
            CAppUI::setMsg("CConsultation-msg-duplicate", UI_MSG_OK);
        }

        echo CAppUI::getMsg();
        if ($callback && $newConsult->_id) {
            CAppUI::callbackAjax($callback, $newConsult->_id, $newConsult->getProperties());
        }
        CApp::rip();
    }
}
