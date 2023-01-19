<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admissions\CSejourLoader;
use Ox\Mediboard\Board\CalendarExporter;
use Ox\Mediboard\Board\ExamCompFinder;
use Ox\Mediboard\Board\TdbSaisieCodages;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Ccam\CFilterCotation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CStatutCompteRendu;
use Ox\Mediboard\Hospi\CObservationMedicale;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Mpm\CPrescriptionLineMix;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\Soins\Controllers\Legacy\DossierSoinsController;
use Ox\Mediboard\System\CSourcePOP;

class BoardLegacyController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function ajaxListDocuments(): void
    {
        $this->checkPermRead();

        $chir_id          = CView::get("chir_id", "ref class|CMediusers");
        $statut           = CView::get("statut", 'str default|');
        $function_id      = CView::get("function_id", "ref class|CFunctions");

        CView::checkin();
        CView::enforceSlave();
        $pref_select_view = CAppUI::pref("select_view");
        $view_praticioner = $pref_select_view != "view_praticioner" ? 0 : 1;
        $view_secretary   = $pref_select_view != "view_secretary" ? 0 : 1;

        $cr        = new CCompteRendu();
        $users_ids = [];
        if ($chir_id) {
            $user      = CMediusers::get($chir_id);
            $users_ids = [$user->_id];
        } elseif ($function_id) {
            $function = CFunctions::findOrFail($function_id);
            $function->loadRefsUsers();
            $users_ids = CMbArray::pluck($function->_ref_users, '_id');
        }

        $date_limite = CMbDT::date("-30 days");

        $where = [
            "signataire_id" => CSQLDataSource::prepareIn($users_ids),
            "creation_date" => $cr->getDS()->prepare(">= ?", $date_limite),
        ];

        if (!CAppUI::pref("show_all_docs")) {
            $where["signature_mandatory"] = $cr->getDS()->prepare("= '1'");
        }

        $crs = $cr->loadList($where, 'creation_date DESC', 100);

        CStoredObject::massLoadFwdRef($crs, "object_id");
        CStoredObject::massLoadFwdRef($crs, "content_id");

        if (!$view_praticioner && !$view_secretary) {
            $view_praticioner = 1;
            $view_secretary   = 1;
        }
        $affichageDocs = [];
        /** @var CCompteRendu $_cr */
        foreach ($crs as $_cr) {
            $context = $_cr->loadTargetObject();
            switch ($context->_class) {
                default:
                    $context_cancelled = false;
                    break;
                case "CConsultation":
                case "CSejour":
                    /** @var  $context CSejour | CConsultation */
                    $context_cancelled = $context->annule;
                    break;
                case "CConsultAnesth":
                    /** @var  $context CConsultAnesth */
                    $context_cancelled = $context->loadRefConsultation()->annule;
                    break;
                case "COperation":
                    /** @var  $context COperation */
                    $context_cancelled = $context->annulee;
            }

            if ($_cr->isAutoLock() || $context_cancelled) {
                unset($crs[$_cr->_id]);
                continue;
            }

            $_cr->_ref_patient = $_cr->getIndexablePatient();
            $_cr->loadLastRefStatutCompteRendu();

            if ($_cr->_ref_last_statut_compte_rendu) {
                $_cr->_ref_last_statut_compte_rendu->loadRefUtilisateur();
                if ($statut) {
                    if ($_cr->_ref_last_statut_compte_rendu->statut != $statut) {
                        unset($crs[$_cr->_id]);
                        continue;
                    }
                }
                if (
                    ($view_praticioner && !$view_secretary)
                    && $_cr->_ref_last_statut_compte_rendu->statut != "attente_validation_praticien"
                ) {
                    unset($crs[$_cr->_id]);
                    continue;
                }
                if (
                    (!$view_praticioner && $view_secretary)
                    && $_cr->_ref_last_statut_compte_rendu->statut == "attente_validation_praticien"
                ) {
                    unset($crs[$_cr->_id]);
                    continue;
                }
            }

            $cat_id = $_cr->file_category_id ?: 0;

            $affichageDocs[$cat_id]["items"][$_cr->nom . "-$_cr->_guid"] = $_cr;
            if (!isset($affichageDocs[$cat_id]["name"])) {
                $affichageDocs[$cat_id]["name"] = $cat_id ? $_cr->_ref_category->nom : CAppUI::tr(
                    "CFilesCategory.none"
                );
            }
        }

        foreach ($affichageDocs as $docs) {
            CMbArray::pluckSort($docs['items'], SORT_DESC, "creation_date");
        }

        $this->renderSmarty(
            'inc_list_documents',
            [
                'affichageDocs'    => $affichageDocs,
                'crs'              => $crs,
                'statut'           => $statut,
                'view_praticioner' => $view_praticioner,
                'view_secretary'   => $view_secretary,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function sejoursOtherResponsable(): void
    {
        $ds = CSQLDataSource::get("std");
        // get
        $user             = CUser::get();
        $date             = CView::get("date", "date default|" . CMbDT::date(), true);
        $board            = CView::get("board", "bool default|0");
        $praticien_id_sel = CView::get("pratSel", "ref class|CMediusers default|" . $user->_id);
        $function_id_sel  = CView::get("functionSel", "ref class|CFunctions");

        CView::checkin();

        $date_min = $date . " 00:00:00";
        $date_max = $date . " 23:59:59";

        $userSel             = new CMediusers();
        $function            = new CFunctions();
        $users               = [];
        $print_content_class = null;
        $print_content_id    = null;

        if ($praticien_id_sel) {
            // Si un praticien est sélectionné, filtre sur le praticien
            $print_content_class = "CMediusers";
            $print_content_id    = $praticien_id_sel;

            $userSel->load($praticien_id_sel);
            $users = [$userSel];
        } elseif ($function_id_sel) {
            // Si un cabinet est sélectionné, filtre sur le cabinet
            $print_content_class = "CFunctions";
            $print_content_id    = $function_id_sel;

            $function->load($function_id_sel);
            $users = $function->loadRefsUsers();
        }

        $sejour       = new CSejour();
        $prescription = new CPrescription();
        $observation  = new CObservationMedicale();

        $where_prescription_line = [
            "praticien_id" => CSQLDataSource::prepareIn(CMbArray::pluck($users, '_id')),
            'debut'        => $ds->prepareBetween(CMbDT::date('-1 month', $date), $date),
        ];
        $where_observation       = [
            "user_id" => CSQLDataSource::prepareIn(CMbArray::pluck($users, '_id')),
            'date'    => $ds->prepareBetween(CMbDT::dateTime('-1 month', $date_min), $date_max),
        ];

        $prescriptions_ids = (new CPrescriptionLineElement())->loadColumn(
            "prescription_id",
            $where_prescription_line
        );
        if (CPrescription::isMPMActive()) {
            $prescriptions_ids = array_merge(
                $prescriptions_ids,
                (new CPrescriptionLineMedicament())->loadColumn(
                    "prescription_id",
                    $where_prescription_line
                )
            );

            $where_prescription_line['date_debut'] = $where_prescription_line['debut'];
            unset($where_prescription_line['debut']);

            $prescriptions_ids = array_merge(
                $prescriptions_ids,
                (new CPrescriptionLineMix())->loadColumn("prescription_id", $where_prescription_line)
            );
        }

        $where_prescription = [
            "prescription_id" => CSQLDataSource::prepareIn($prescriptions_ids),
            "object_class"    => "= 'CSejour'",
        ];

        $sejours_ids = $prescription->loadColumn("object_id", $where_prescription);

        //Récupération des séjours liés à une observation
        $sejours_ids = array_merge($sejours_ids, $observation->loadColumn("sejour_id", $where_observation));

        //Filtre des séjours ayant le même responsable, et au moment de la date sélectionnée
        $where                 = [];
        $where["praticien_id"] = CSQLDataSource::prepareNotIn(CMbArray::pluck($users, '_id'));
        $where["sejour_id"]    = $ds->prepareIn($sejours_ids);
        $sejours_ids           = $sejour->loadIds($where);
        $sejours_ids           = array_unique($sejours_ids);

        $sejours = $sejour->loadAll($sejours_ids);
        $sejours = CSejourLoader::loadSejoursForSejoursView($sejours, $users, $date, false);

        $this->renderSmarty(
            'inc_list_sejours_global',
            [
                "date"                  => $date,
                "praticien"             => $userSel,
                "sejours"               => $sejours,
                "board"                 => $board,
                "service"               => new CService(),
                "service_id"            => null,
                "etats_patient"         => [],
                "show_affectation"      => false,
                "function"              => $function,
                "sejour_id"             => null,
                "show_full_affectation" => true,
                "only_non_checked"      => false,
                "print"                 => false,
                "_sejour"               => new CSejour(),
                'ecap'                  => false,
                'services_selected'     => [],
                'visites'               => [],
                "discipline"            => new CDiscipline(),
                "lite_view"             => true,
                "print_content_class"   => $print_content_class,
                "print_content_id"      => $print_content_id,
                "allow_edit_cleanup"    => 0,
                "tab_to_update"         => "tab-autre-responsable",
                "my_patient"            => false,
                "count_my_patient"      => 0,
            ],
            'modules/soins'
        );
    }

    /**
     * @throws Exception
     */
    public function askCorrection(): void
    {
        $compte_rendu_id = CView::get("compte_rendu_id", "ref class|CCompteRendu");
        CView::checkin();
        $statut_compte_rendu                  = new CStatutCompteRendu();
        $statut_compte_rendu->compte_rendu_id = $compte_rendu_id;
        $statut_compte_rendu->statut          = "attente_correction_secretariat";
        $statut_compte_rendu->datetime        = CMbDT::dateTime();
        $statut_compte_rendu->user_id         = CMediusers::get()->_id;

        $this->renderSmarty(
            'vw_add_commentaire',
            [
                'statut' => $statut_compte_rendu,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function ajaxTabsPrescription(): void
    {
        $this->checkPermRead();

        $chirSel     = CView::get("chirSel", "ref class|CMediusers", true);
        $date        = CView::get("date", "date default|now", true);
        $function_id = CView::get("function_id", "ref class|CFunctions", true);

        CView::checkin();

        $this->renderSmarty(
            'inc_tabs_prescription',
            [
                'date'        => $date,
                'chirSel'     => $chirSel,
                'function_id' => $function_id,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function ajaxWorklist(): void
    {
        $this->checkPermRead();

        // Récupération des paramètres
        $chirSel     = CView::get("chirSel", "ref class|CMediusers", true);
        $date        = CView::get("date", "date default|now", true);
        $function_id = CView::get("functionSel", "ref class|CFunctions", true);

        CView::checkin();

        $account               = new CSourcePOP();
        $account->object_class = "CMediusers";
        $account->object_id    = $chirSel;
        $account->loadMatchingObject();

        $this->renderSmarty(
            'inc_worklist',
            [
                'account'     => $account,
                'date'        => $date,
                'chirSel'     => $chirSel,
                'function_id' => $function_id,

            ]
        );
    }

    /**
     * @throws Exception
     */
    public function viewSearch(): void
    {
        $this->checkPermRead();

        CView::checkin();

        $date  = CMbDT::date("-1 month");
        $types = [];
        if ($conf = CAppUI::gconf("search active_handler active_handler_search_types")) {
            $types = explode("|", $conf);
        }

        $this->renderSmarty(
            "vw_search",
            [
                "date"  => $date,
                "types" => $types,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function exportIcal(): void
    {
        $this->checkPermRead();

        $prat_id = CView::get(
            "prat_id",
            "ref class|CMediusers default|"
            . CMediusers::get()->_id
        );

        // Group: 0 - No grouping & 1 - Grouping per day
        $group        = CView::get("group", "enum list|0|1 default|0");
        $details      = CView::get("details", "bool default|1");
        $anonymize    = CView::get("anonymize", "bool");
        $export       = CValue::get("export", ["consult"]);
        $weeks_before = CView::get("weeks_before", "num default|1");
        $weeks_after  = CView::get("weeks_after", "num default|4");
        $date         = CView::get("date", "date default|now");

        CView::checkin();

        $praticien         = CMediusers::findOrFail($prat_id);
        $context_to_export = explode("|", $export);

        $exporter = new CalendarExporter(
            $date,
            $weeks_before,
            $weeks_after,
            $praticien,
            $context_to_export,
            $group,
            $details,
            $anonymize,
        );

        $exporter->exportCalendar();
        CApp::rip();
    }

    /**
     * @throws Exception
     */
    public function viewExamComp(): void
    {
        $this->checkPermRead();

        $_date_min = CView::get("_date_min", "date default|now");
        $date      = CView::get("date", "date");
        $list      = CView::get("list", "bool default|0");

        CView::checkin();

        $exam_finder = new ExamCompFinder(CMediusers::get(), $_date_min, $date);

        $exam_finder->loadExamensComplementaires();

        $examens = $exam_finder->getExamensComplementaires();

        $filter            = new CConsultation();
        $filter->_date_min = $_date_min;

        $tpl = $list ? "vw_list_exams_comp" : "vw_exams_comp";

        $this->renderSmarty($tpl, [
            "filter"  => $filter,
            "examens" => $examens,
            "date"    => $date,
        ]);
    }

    /**
     * @throws Exception
     */
    public function viewInterventionsNonCotees(): void
    {
        $this->checkPermEdit();

        $chir     = new CMediusers();
        $mediuser = CMediusers::get();

        if ($mediuser->isProfessionnelDeSante()) {
            $chir = $mediuser;
        }

        $chirSel                  = CView::get(
            "praticien_id",
            'ref class|CMediusers' . $chir->_id ? " default|$chir->_id" : '',
            true
        );
        $function_id              = CView::get('function_id', 'ref class|CFunctions', true);
        $all_prats                = CView::get("all_prats", 'bool', false);
        $end_date                 = CView::get("end_date", 'date default|' . CMbDT::date());
        $begin_date               = CView::get("begin_date", 'date default|' . CMbDT::date("-1 week", $end_date));
        $objects_whithout_codes   = CView::get('objects_whithout_codes', 'bool default|1');
        $show_unexported_acts     = CView::get('show_unexported_acts', 'bool default|0');
        $display_operations       = CView::get('display_operations', 'bool default|1');
        $display_consultations    = CView::get('display_consultations', 'bool default|0');
        $display_sejours          = CView::get('display_sejours', 'bool default|0');
        $display_seances          = CView::get('display_seances', 'bool default|0');
        $libelle                  = CView::get('libelle', 'str');
        $protocole_id             = CView::get('protocole_id', 'ref class|CProtocole');
        $ccam_codes               = CView::get('ccam_codes', 'str');
        $nda                      = CView::get('nda', 'str');
        $patient_id               = CView::get('patient_id', 'bool default|0');
        $codage_lock_status       = CView::get('codage_lock_status', 'enum list|unlocked|locked_by_chir|locked');
        $excess_fee_chir_status   = CView::get(
            'excess_fee_chir_status',
            'enum list|non_regle|cb|cheque|espece|virement'
        );
        $excess_fee_anesth_status = CView::get(
            'excess_fee_anesth_status',
            'enum list|non_regle|cb|cheque|espece|virement'
        );

        $check_all_interventions = CAppUI::pref("check_all_interventions");
        $display_all             = CView::get("display_all", "bool default|$check_all_interventions");

        CView::checkin();

        $patient = new CPatient();
        $patient->load($patient_id);

        $function = new CFunctions();
        if ($chirSel) {
            $chir = CMediusers::get($chirSel);
        } elseif ($function_id) {
            $function->load($function_id);
            $chir = new CMediusers();
        }

        $object_classes = [];
        if ($display_consultations) {
            $object_classes[] = 'CConsultation';
        }
        if ($display_operations) {
            $object_classes[] = 'COperation';
        }
        if ($display_sejours) {
            $object_classes[] = 'CSejour';
        }
        if ($display_seances) {
            $object_classes[] = 'CSejour-seances';
        }

        $date             = CMbDT::date('now');
        $curr_week_start  = CMbDT::date('monday this week', $date);
        $curr_week_end    = CMbDT::date('sunday this week', $date);
        $curr_month_start = CMbDT::date('first day of this month', $date);
        $curr_month_end   = CMbDT::date('last day of this month', $date);
        $last_month_start = CMbDT::date('first day of previous month', $date);
        $last_month_end   = CMbDT::date('last day of previous month', $date);

        $list_excess_fee_payment_status = [
            "non_regle",
            "cb",
            "cheque",
            "espece",
            "virement",
        ];

        $this->renderSmarty("inc_vw_interv_non_cotees", [

            "chirSel"                        => $chirSel,
            'chir'                           => $chir,
            'function'                       => $function,
            'user'                           => CMediusers::get(),
            'perm_fonct'                     => CAppUI::pref("allow_other_users_board"),
            "begin_date"                     => $begin_date,
            "end_date"                       => $end_date,
            "all_prats"                      => $all_prats,
            "today"                          => $date,
            "curr_week_start"                => $curr_week_start,
            "curr_week_end"                  => $curr_week_end,
            "curr_month_start"               => $curr_month_start,
            "curr_month_end"                 => $curr_month_end,
            "last_month_start"               => $last_month_start,
            "last_month_end"                 => $last_month_end,
            'objects_whithout_codes'         => $objects_whithout_codes,
            'show_unexported_acts'           => $show_unexported_acts,
            'display_operations'             => $display_operations,
            'display_consultations'          => $display_consultations,
            'display_sejours'                => $display_sejours,
            'display_seances'                => $display_seances,
            'libelle'                        => $libelle,
            'protocole_id'                   => $protocole_id,
            'ccam_codes'                     => $ccam_codes != '' ? explode('|', $ccam_codes) : [],
            'nda'                            => $nda,
            'patient'                        => $patient,
            'codage_lock_status'             => $codage_lock_status,
            'excess_fee_chir_status'         => $excess_fee_chir_status,
            'excess_fee_anesth_status'       => $excess_fee_anesth_status,
            'display_all'                    => $display_all,
            'list_codage_lock_status'        => CFilterCotation::$list_codage_lock_status,
            'list_excess_fee_payment_status' => $list_excess_fee_payment_status,
            'object_classes'                 => $object_classes,
        ]);
    }

    /**
     * @throws Exception
     */
    public function listInterventionNonCotees(): void
    {
        $this->checkPermRead();

        $chir_id                  = CView::get("praticien_id", 'ref class|CMediusers');
        $function_id              = CView::get('function_id', 'ref class|CFunctions');
        $all_prats                = CView::get("all_prats", 'bool default|0', false);
        $end_date                 = CView::get("end_date", 'date default|now', false);
        $begin_date               = CView::get(
            "begin_date",
            'date default|' . CMbDT::date("-1 week", $end_date),
            false
        );
        $export                   = CView::get("export", 'bool default|0', false);
        $objects_whithout_codes   = CView::get('objects_whithout_codes', 'bool default|1', false);
        $show_unexported_acts     = CView::get('show_unexported_acts', 'bool default|0', false);
        $object_classes           = CView::get('object_classes', 'str');
        $ccam_codes               = CView::get('ccam_codes', 'str', false);
        $libelle                  = CView::get('libelle', 'str', false);
        $display_operations       = CView::get('display_operations', 'bool default|1', false);
        $display_consultations    = CView::get('display_consultations', 'bool default|1', false);
        $display_sejours          = CView::get('display_sejours', 'bool default|1', false);
        $display_seances          = CView::get('display_seances', 'bool default|1', false);
        $nda                      = CView::get('nda', 'str', false);
        $patient_id               = CView::get('patient_id', 'ref class|CPatient', false);
        $codage_lock_status       = CView::get('codage_lock_status', 'enum list|unlocked|locked_by_chir|locked', false);
        $board                    = CView::get("board", 'bool default|0', false);
        $display_all              = CView::get('display_all', 'bool default|0');
        $excess_fee_chir_status   = CView::get(
            'excess_fee_chir_status',
            'enum list|non_regle|cb|cheque|espece|virement'
        );
        $excess_fee_anesth_status = CView::get(
            'excess_fee_anesth_status',
            'enum list|non_regle|cb|cheque|espece|virement'
        );

        CView::checkin();
        CView::enforceSlave();

        $mediuser = CMediusers::get();
        $user     = new CMediusers();
        $user->load($chir_id);

        if ($all_prats) {
            $chir_id = 0;
        }
        if ($object_classes && $object_classes !== '') {
            $object_classes = str_replace(",", "|", $object_classes);
            $object_classes = explode('|', $object_classes);
        } else {
            $object_classes = ['CConsultation', 'COperation', 'CSejour', 'CSejour-seance'];
        }

        $filters = [
            'chir_id'               => $user->_id,
            'begin_date'            => $begin_date,
            'end_date'              => $end_date,
            'object_classes'        => $object_classes,
            'show_unexported_acts'  => $show_unexported_acts,
            'objects_without_codes' => $objects_whithout_codes,
            'display_all'           => $display_all,
        ];

        if ($function_id) {
            $filters['function_id'] = $function_id;
        }

        if ($ccam_codes != '') {
            $filters['ccam_codes'] = $ccam_codes;
        }

        if ($libelle != '') {
            $filters['libelle'] = $libelle;
        }

        if ($nda) {
            $filters['nda'] = $nda;
        }

        if ($patient_id) {
            $filters['patient_id'] = $patient_id;
        }

        if ($codage_lock_status) {
            $filters['codage_lock_status'] = $codage_lock_status;
        }

        if ($excess_fee_chir_status && $mediuser->isChirurgien()) {
            $filters['excess_fee_chir_status'] = $excess_fee_chir_status;
        }
        if ($excess_fee_anesth_status && $mediuser->isAnesth()) {
            $filters['excess_fee_anesth_status'] = $excess_fee_anesth_status;
        }

        $tdb_saisie_codage = new TdbSaisieCodages(CMediusers::get(), $filters);

        $tdb_saisie_codage->loadObjetsNonCotes();

        $interventions                  = $tdb_saisie_codage->getInterventions();
        $sejours                        = $tdb_saisie_codage->getSejours();
        $consultations                  = $tdb_saisie_codage->getConsultations();
        $seances                        = $tdb_saisie_codage->getSeances();
        $total                          = $tdb_saisie_codage->getTotal();
        $total_operations_non_cotees    = $tdb_saisie_codage->getTotalOperationsNonCotees();
        $total_consultations_non_cotees = $tdb_saisie_codage->getTotalConsultationsNonCotees();
        $total_sejours_non_cotes        = $tdb_saisie_codage->getTotalSejoursNonCotes();
        $total_seances_non_cotees       = $tdb_saisie_codage->getTotalSeancesNonCotees();

        if (!$export) {
            $vars = [
                "totals"                         => $total,
                "interventions"                  => $interventions,
                "consultations"                  => $consultations,
                "sejours"                        => $sejours,
                "seances"                        => $seances,
                "begin_date"                     => $begin_date,
                "end_date"                       => $end_date,
                "all_prats"                      => $all_prats,
                "board"                          => $board,
                "objects_whithout_codes"         => $objects_whithout_codes,
                "show_unexported_acts"           => $show_unexported_acts,
                "ccam_codes"                     => $ccam_codes,
                "libelle"                        => $libelle,
                "display_operations"             => $display_operations,
                "display_consultations"          => $display_consultations,
                "display_sejours"                => $display_sejours,
                "display_seances"                => $display_seances,
                "nda"                            => $nda,
                "patient_id"                     => $patient_id,
                "codage_lock_status"             => $codage_lock_status,
                "total_operations_non_cotees"    => $total_operations_non_cotees,
                "total_consultations_non_cotees" => $total_consultations_non_cotees,
                "total_sejours_non_cotes"        => $total_sejours_non_cotes,
                "total_seances_non_cotees"       => $total_seances_non_cotees,
                "date_begin_op_non_cotees"       => CMbDT::date("-3 MONTHS"),
                "date_end_op_non_cotees"         => CMbDT::date(),
                "object_classes"                 => $object_classes,
            ];
            if ($user->_id && $user->isProfessionnelDeSante()) {
                $vars["chirSel"] = $user;
            }

            $this->renderSmarty("inc_list_interv_non_cotees", $vars);
        } else {
            $tdb_saisie_codage->exportToCsv();
        }
    }

    /**
     * @throws Exception
     */
    public function viewHospitalisation(): void
    {
        (new DossierSoinsController())->viewIndexSejour();
    }
}
