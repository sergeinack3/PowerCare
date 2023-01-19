<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Bcb\CBcbIndication;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Medicament\CMedicament;
use Ox\Mediboard\Medicament\CMedicamentComposant;
use Ox\Mediboard\Medicament\CMedicamentIndication;
use Ox\Mediboard\Medicament\CMedicamentProduit;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CRechercheDossierClinique;
use Ox\Mediboard\Patients\CTraitement;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Mpm\CPrescriptionLineMixItem;

CApp::setMemoryLimit("768M");

$where = [];
$ljoin = [];

$user_id               = CView::get("user_id", "ref class|CMediusers");
$classes_atc           = CView::get("classes_atc", "numchar");
$keywords_atc          = CView::get("keywords_atc", "str");
$code_cis              = CView::get("code_cis", "numchar");
$code_ucd              = CView::get("code_ucd", "numchar");
$libelle_produit       = CView::get("libelle_produit", "str");
$keywords_composant    = CView::get("keywords_composant", "numchar");
$composant             = CView::get("composant", "str");
$keywords_indication   = CView::get("keywords_indication", "str");
$indication            = CView::get("indication", "str");
$type_indication       = CView::get("type_indication", "str");
$commentaire           = CView::get("commentaire", "str");
$section               = CView::get("section_choose", "enum list|consult|sejour|operation|without_medical_folder");
$export                = CView::get("export", "bool default|0");
$produit               = CView::get("produit", "str");
$start                 = CView::get("start", "num default|0");
$only_medecin_traitant = CView::get("only_medecin_traitant", "str");
$libelle_evenement     = CView::get("libelle_evenement", "str");
$function_id           = CView::get("function_id", "ref class|CFunctions");
$type_filter           = CView::get("select_prat_cab", "enum list|prat|cab");
$context               = CView::get('contexte_recherche', "enum list|prescription|traitement");
$ald                   = CView::get('ald', "bool default|0");
$group_by_patient      = CView::get('group_by_patient', "bool default|0");

$user                  = new CMediusers();
$use_function_distinct = CAppUI::isCabinet() && !$user->isAdmin();
$use_group_distinct    = CAppUI::isGroup() && !$user->isAdmin();

$function_id = $use_function_distinct ? CFunctions::getCurrent()->_id : $function_id;

if ($function_id) {
    $prats       = $user->loadPraticiens(PERM_EDIT, $function_id);
    $where_users = CSQLDataSource::prepareIn(array_keys($prats));
} else {
    if ($user_id) {
        $where_users = "= '$user_id'";
    } else {
        if (CMediusers::get()->canDo()->admin) {
            $where_users = "";
        } else {
            CAppUI::displayAjaxMsg("oxCabinet-No practitioner nor function", UI_MSG_ERROR);
            CApp::rip();
        }
    }
}

if ($only_medecin_traitant == 'on') {
    $only_medecin_traitant = 1;
} else {
    $only_medecin_traitant = 0;
}

$mod_tamm = CView::get("mod_tamm", "bool default|0");

$date_min = CView::get("_date_min", "dateTime");
$date_max = CView::get("_date_max", "dateTime");

CView::setSession("produit", $produit);
CView::setSession("user_id", $user_id);
CView::setSession("classes_atc", $classes_atc);
CView::setSession("keywords_atc", $keywords_atc);
CView::setSession("code_cis", $code_cis);
CView::setSession("code_ucd", $code_ucd);
CView::setSession("libelle_produit", $libelle_produit);
CView::setSession("keywords_composant", $keywords_composant);
CView::setSession("composant", $composant);
CView::setSession("keywords_indication", $keywords_indication);
CView::setSession("indication", $indication);
CView::setSession("type_indication", $type_indication);
CView::setSession("commentaire", $commentaire);

$start = intval($start);

$patient = new CPatient();

$ds = $patient->_spec->ds;

// fields
$fields = [
    CPatient::class      => [
        "sexe"             => "=",
        "_age_min"         => null,
        "_age_max"         => null,
        "medecin_traitant" => null,
        "patient_id"       => "=",
        "ald"              => "=",
    ],
    CAntecedent::class   => [
        "hidden_list_antecedents_cim10" => null,
        "antecedents_text"              => null,
        "allergie_text"                 => null,
        "hidden_list_pathologie_cim10"  => null,
        "pathologie_text"               => null,
        "hidden_list_probleme_cim10"    => null,
        "probleme_text"                 => null,
    ],
    CTraitement::class   => [
        "traitement" => "LIKE",
    ],
    CConsultation::class => [
        "motif"                     => "LIKE",
        "_rques_consult"            => null,
        "_examen_consult"           => null,
        //"_traitement_consult" => null,
        "conclusion"                => "LIKE",
        "motif_annulation"          => "=",
        "annule"                    => "=",
        "hidden_codes_ccam_consult" => null,
        "hidden_codes_ngap_consult" => null,
        "type_consultation"         => '=',
    ],
    CSejour::class       => [
        "type"          => "=",
        "convalescence" => "LIKE",
        "_rques_sejour" => null,
        "entree"        => null,
        "sortie"        => null,
        "libelle"       => "LIKE",
    ],
    COperation::class    => [
        "materiel"          => "LIKE",
        "exam_per_op"       => "LIKE",
        "examen"            => "LIKE",
        //"rques" => "LIKE",
        "_libelle_interv"   => null,
        "codes_ccam_interv" => null,
        "_rques_interv"     => null,
    ],
];

$one_field_presc = $code_cis || $code_ucd || $libelle_produit || $classes_atc || $composant || $indication || $commentaire;
$codes_cis       = [];

// Si la recherche concerne un produit, on recherche les codes cis correpondant
if ($libelle_produit) {
    $medicament = new CMedicamentProduit();
    $produits   = $medicament->searchProduitAutocomplete($libelle_produit, "100", null, 0, 1, null);
    $codes_cis  = array_unique(CMbArray::pluck($produits, "code_cis"));
}

$one_field            = false || $user_id || $function_id || $one_field_presc;
$one_field_traitement = false;
$one_field_atcd       = false;
$sejour_filled        = false;
$consult_filled       = false;
$interv_filled        = false;

$from = null;
$to   = null;
$data = [];

foreach ($fields as $_class => $_fields) {
    $data[$_class] = array_intersect_key($_GET, $_fields);
    $object        = new $_class;
    $prefix        = $object->_spec->table;

    foreach ($data[get_class($object)] as $_field => $_value) {
        CView::setSession($_field, $_value);

        if ($_value !== "") {
            $one_field = true;
        }

        if ($_value === "" || !$_fields[$_field]) {
            continue;
        }

        switch ($_fields[$_field]) {
            case "=":
                $where["$prefix.$_field"] = $ds->prepare(" = % ", $_value);
                break;
            default:
                $where["$prefix.$_field"] = $ds->prepareLike("%$_value%");
        }
    }
}

CView::checkin();

// Because of setSession
CView::enforceSlave();

switch ($section) {
    case "consult":
        $consult_data = $data[CConsultation::class];
        $sejour_data  = $data[CSejour::class];

        if (empty($consult_data["motif"])
            && empty($consult_data["_rques_consult"])
            && empty($consult_data["_examen_consult"])
            && empty($consult_data["conclusion"])
            && empty($consult_data["motif_annulation"])
            && !isset($consult_data["annule"])
            && empty($sejour_data["entree"])
            && empty($sejour_data["sortie"])
            && !$one_field_presc && !$mod_tamm
        ) {
            break;
        }

        $consult_filled        = true;
        $ljoin["patients"]     = "patients.patient_id = consultation.patient_id";
        $ljoin["plageconsult"] = "plageconsult.plageconsult_id = consultation.plageconsult_id";

        if ($where_users) {
            $where["plageconsult.chir_id"] = $where_users;
        }

        // The consultation mustn't be a a pause nor a meeting
        $where["consultation.patient_id"] = " IS NOT NULL";

        // CConsultation ---------------------------
        $consult_data = $data[CConsultation::class];

        if (!empty($consult_data["_rques_consult"])) {
            $where["consultation.rques"] = $ds->prepareLike("%{$consult_data['_rques_consult']}%");
        }

        if (!empty($consult_data["_examen_consult"])) {
            $where["consultation.examen"] = $ds->prepareLike("%{$consult_data['_examen_consult']}%");
        }

        if (!empty($sejour_data["_rques_sejour"])) {
            $where["sejour.rques"] = $ds->prepareLike("%{$sejour_data['_rques_sejour']}%");
        }

        $from = !$mod_tamm ? CMbDT::date($sejour_data['entree']) : $date_min;
        $to   = !$mod_tamm ? CMbDT::date($sejour_data['sortie']) : $date_max;

        if (!empty($sejour_data["entree"]) || ($mod_tamm && $date_min)) {
            // Début et fin
            if (!empty($sejour_data["sortie"]) || ($mod_tamm && $date_max)) {
                $where["plageconsult.date"] = "BETWEEN '$from' AND '$to'";
            } // Début
            else {
                $where["plageconsult.date"] = ">= '$from'";
            }
        } // Fin
        else {
            if (!empty($sejour_data["sortie"]) || ($mod_tamm && $date_max)) {
                $where["plageconsult.date"] = "< '$to'";
            }
        }

        $data_patient = $data[CPatient::class];
        if (!empty($data_patient["_age_min"])) {
            $where[] = "DATEDIFF(plageconsult.date, patients.naissance)/365 > {$data_patient['_age_min']}";
        }
        if (!empty($data_patient["_age_max"])) {
            $where[] = "DATEDIFF(plageconsult.date, patients.naissance)/365 <= {$data_patient['_age_max']}";
        }

        // Search by CCAM codes (OR between them)
        if ($consult_data['hidden_codes_ccam_consult'] && $consult_data['type_consultation'] == 'consultation') {
            $codes_consult_ccam = [$consult_data['hidden_codes_ccam_consult']];

            if (strpos($consult_data['hidden_codes_ccam_consult'], '|') !== false) {
                $codes_consult_ccam = explode('|', $consult_data['hidden_codes_ccam_consult']);
            }

            $ljoin["acte_ccam"]              = "acte_ccam.object_id = consultation.consultation_id";
            $where["acte_ccam.object_class"] = "= 'CConsultation'";

            $or_sql = "(";
            $i      = 0;
            foreach ($codes_consult_ccam as $_code_ccam) {
                $or_sql .= "`code_acte` = '$_code_ccam'";
                if ($i < sizeof($codes_consult_ccam) - 1) {
                    $or_sql .= " OR ";
                }
                $i++;
            }
            $or_sql .= ")";

            $where[] = $or_sql;
        }

        // Search by NGAP codes (OR between them)
        if ($consult_data['hidden_codes_ngap_consult'] && $consult_data['type_consultation'] == 'consultation') {
            $codes_consult_ngap = [$consult_data['hidden_codes_ngap_consult']];

            if (strpos($consult_data['hidden_codes_ngap_consult'], '|') !== false) {
                $codes_consult_ngap = explode('|', $consult_data['hidden_codes_ngap_consult']);
            }

            $ljoin["acte_ngap"]              = "acte_ngap.object_id = consultation.consultation_id";
            $where["acte_ngap.object_class"] = "= 'CConsultation'";

            $or_sql = "(";
            $i      = 0;
            foreach ($codes_consult_ngap as $_code_ngap) {
                $or_sql .= "`code` = '$_code_ngap'";
                if ($i < sizeof($codes_consult_ngap) - 1) {
                    $or_sql .= " OR ";
                }
                $i++;
            }
            $or_sql .= ")";

            $where[] = $or_sql;
        }

        break;
    case "sejour":
        $sejour_data = $data[CSejour::class];

        if (empty($sejour_data["libelle"])
            && empty($sejour_data["type"])
            && empty($sejour_data["_rques_sejour"])
            && empty($sejour_data["convalescence"])
            && empty($sejour_data["entree"])
            && empty($sejour_data["sortie"])
            && !$one_field_presc
        ) {
            break;
        }

        $sejour_filled     = true;
        $ljoin["patients"] = "patients.patient_id = sejour.patient_id";

        $where["sejour.praticien_id"] = $where_users;

        // CSejour ----------------------------
        if (!empty($sejour_data["_rques_sejour"])) {
            $where["sejour.rques"] = $ds->prepareLike("%{$sejour_data['_rques_sejour']}%");
        }
        if (!empty($sejour_data["entree"])) {
            $where["sejour.sortie"] = ">  '{$sejour_data['entree']}'";
        }
        if (!empty($sejour_data["sortie"])) {
            $where["sejour.entree"] = "<  '{$sejour_data['sortie']}'";
        }
        $ljoin["dossier_medical"]   = "dossier_medical.object_id = sejour.sejour_id";
        $ljoin["evenement_patient"] = "evenement_patient.dossier_medical_id = dossier_medical.dossier_medical_id";
        $where[]                    = "dossier_medical.object_class = 'CSejour' OR dossier_medical.dossier_medical_id IS NULL";

        $data_patient = $data[CPatient::class];
        if (!empty($data_patient["_age_min"])) {
            $where[] = "DATEDIFF(sejour.entree, patients.naissance)/365 > {$data_patient['_age_min']}";
        }
        if (!empty($data_patient["_age_max"])) {
            $where[] = "DATEDIFF(sejour.entree, patients.naissance)/365 <= {$data_patient['_age_max']}";
        }
        break;
    case "operation":
        // COperations ---------------------------
        $interv_data = $data[COperation::class];
        $sejour_data = $data[CSejour::class];

        if (empty($interv_data["_libelle_interv"])
            && empty($interv_data["_rques_interv"])
            && empty($interv_data["examen"])
            && empty($interv_data["materiel"])
            && empty($interv_data["exam_per_op"])
            && empty($interv_data["codes_ccam_interv"])
            && empty($sejour_data["entree"])
            && empty($sejour_data["sortie"])
            && !$one_field_presc
        ) {
            break;
        }

        $interv_filled = true;

        $ljoin["sejour"]   = "operations.sejour_id = sejour.sejour_id";
        $ljoin["patients"] = "patients.patient_id = sejour.patient_id";

        if (!empty($interv_data["codes_ccam_interv"])) {
            $codes = preg_split("/[\s,]+/", $interv_data["codes_ccam_interv"]);

            $where_code = [];
            foreach ($codes as $_code) {
                $where_code[] = "operations.codes_ccam " . $ds->prepareLike("%$_code%");
            }

            $where[] = implode(" AND ", $where_code);
        }
        if (!empty($interv_data["_rques_interv"])) {
            $where["operations.rques"] = $ds->prepareLike("%{$interv_data['_rques_interv']}%");
        }
        if (!empty($interv_data["_libelle_interv"])) {
            $where["operations.libelle"] = $ds->prepareLike("%{$interv_data['_libelle_interv']}%");
        }

        $where[] = "operations.chir_id " . $where_users . " OR operations.chir_id IS NULL";
        $where[] = "operations.annulee = '0' OR operations.annulee IS NULL";


        if (!empty($sejour_data["entree"])) {
            $from    = CMbDT::date($sejour_data['entree']);
            $where[] = "operations.date  >= '$from'";
        }
        if (!empty($sejour_data["sortie"])) {
            $to      = CMbDT::date($sejour_data['sortie']);
            $where[] = "operations.date  < '$to'";
        }

        $data_patient = $data[CPatient::class];
        if (!empty($data_patient["_age_min"])) {
            $where[] = "DATEDIFF(sejour.entree, patients.naissance)/365 > {$data_patient['_age_min']}";
        }
        if (!empty($data_patient["_age_max"])) {
            $where[] = "DATEDIFF(sejour.entree, patients.naissance)/365 <= {$data_patient['_age_max']}";
        }
        break;
    case "without_medical_folder";
        $is_tamm = (CAppUI::pref("UISTYLE") === "tamm");

        $data_patient = $data[CPatient::class];
        $sejour_data  = $data[CSejour::class];

        $date_min = CMbDT::date($sejour_data['entree']);
        $date_max = CMbDT::date($sejour_data['sortie']);

        $consultation_patient_ids = [];
        $sejour_patient_ids       = [];

        $ljoin_consultation = [];
        $ljoin_sejour       = [];
        $where_consultation = [];
        $where_sejour       = [];

        if ($where_users) {
            $where_consultation["plageconsult.chir_id"] = $where_users;
        }

        $ljoin["consultation"] = "consultation.patient_id = patients.patient_id";
        $ljoin_consultation["plageconsult"] = "plageconsult.plageconsult_id = consultation.plageconsult_id";

        $where_or = null;
        if (!$is_tamm) {
            $where_or = " OR sejour.sejour_id " . $ds->prepare("IS NOT NULL");
        }

        $where[] = "consultation.consultation_id " . $ds->prepare("IS NOT NULL") . $where_or;
        $where_consultation[] = "consultation.consultation_id " . $ds->prepare("IS NOT NULL");

        // Toutes les consultations pour le praticien sélectionné
        $request = new CRequest();
        $request->addColumn("DISTINCT consultation.patient_id");
        $request->addTable("consultation");
        $request->addLJoin($ljoin_consultation);
        $request->addWhere($where_consultation);
        $praticien_consultation_patient_ids = CMbArray::pluck($ds->loadList($request->makeSelect()), "patient_id");

        // CConsultation
        if (!empty($data_patient["_age_min"])) {
            $where_consultation[] = "DATEDIFF(plageconsult.date, patients.naissance)/365 > {$data_patient['_age_min']}";
        }
        if (!empty($data_patient["_age_max"])) {
            $where_consultation[] = "DATEDIFF(plageconsult.date, patients.naissance)/365 <= {$data_patient['_age_max']}";
        }

        if ($date_min) {
            // Début et fin
            if ($date_max) {
                $where_consultation["plageconsult.date"] = "BETWEEN '$date_min' AND '$date_max'";
            } // Début
            else {
                $where_consultation["plageconsult.date"] = ">= '$date_min'";
            }
        } // Fin
        else {
            if ($date_max) {
                $where_consultation["plageconsult.date"] = "< '$date_max'";
            }
        }

        // ALD patient
        if (!empty($data_patient["ald"])) {
            $where_consultation["patients.ald"] = $where_sejour["patients.ald"] = $ds->prepare(
                "= ?",
                $data_patient["ald"]
            );
        }

        // Patient selected
        if (!empty($data_patient["patient_id"])) {
            $where_consultation["patients.patient_id"] = $where_sejour["patients.patient_id"] = $ds->prepare(
                "= ?",
                $data_patient["patient_id"]
            );
        }

        // Sexe patient
        if (!empty($data_patient["sexe"])) {
            $where_consultation["patients.sexe"] = $where_sejour["patients.sexe"] = $ds->prepare(
                "= ?",
                $data_patient["sexe"]
            );
        }

        // Doctor
        if (!empty($data_patient["medecin_traitant"])) {
            $medecin_traitant_id = $data_patient["medecin_traitant"];
            if ($only_medecin_traitant) {
                $where_consultation[] = $where_sejour[] = "patients.medecin_traitant = '$medecin_traitant_id'";
            } else {
                $where_consultation[]                = $where_sejour[] = "patients.medecin_traitant = '$medecin_traitant_id' OR 
                correspondant.medecin_id = '$medecin_traitant_id'";
                $ljoin_consultation["correspondant"] = $ljoin_sejour["correspondant"] = "patients.patient_id = correspondant.patient_id";
            }
        }

        // Ne pas prendre en compte les consultations pour la periode donnée
        $ljoin_consultation["patients"] = "patients.patient_id = consultation.patient_id";

        $request = new CRequest();
        $request->addColumn("DISTINCT consultation.patient_id");
        $request->addTable("consultation");
        $request->addLJoin($ljoin_consultation);
        $request->addWhere($where_consultation);
        $patient_ids = CMbArray::pluck($ds->loadList($request->makeSelect()), "patient_id");

        $consultation_patient_ids = array_diff($praticien_consultation_patient_ids, $patient_ids);

        // CSejour
        if (!$is_tamm) {
            $where_sejour[] = "sejour.sejour_id " . $ds->prepare("IS NOT NULL");

            $ljoin["sejour"] = $ljoin_sejour["sejour"] = "sejour.patient_id = patients.patient_id";

            if ($where_users) {
                $where_sejour["sejour.praticien_id"] = $where_users;
            }

            // Toutes les séjours pour le praticien sélectionné
            $request = new CRequest();
            $request->addColumn("DISTINCT sejour.patient_id");
            $request->addTable("patients");
            $request->addLJoin($ljoin_sejour);
            $request->addWhere($where_sejour);
            $praticien_sejour_patient_ids = CMbArray::pluck($ds->loadList($request->makeSelect()), "patient_id");

            // CSejour

            if (!empty($sejour_data["entree"])) {
                $where_sejour["sejour.sortie"] = $ds->prepare("> ?", $sejour_data['entree']);
            }
            if (!empty($sejour_data["sortie"])) {
                $where_sejour["sejour.entree"] = $ds->prepare("< ?", $sejour_data['sortie']);
            }

            if (!empty($data_patient["_age_min"])) {
                $where_sejour[] = "DATEDIFF(sejour.entree, patients.naissance)/365 > {$data_patient['_age_min']}";
            }
            if (!empty($data_patient["_age_max"])) {
                $where_sejour[] = "DATEDIFF(sejour.entree, patients.naissance)/365 <= {$data_patient['_age_max']}";
            }


            // Séjour pour la periode donné
            $request = new CRequest();
            $request->addColumn("DISTINCT sejour.patient_id");
            $request->addTable("patients");
            $request->addLJoin($ljoin_sejour);
            $request->addWhere($where_sejour);
            $patient_ids = CMbArray::pluck($ds->loadList($request->makeSelect()), "patient_id");

            $sejour_patient_ids = array_diff($praticien_sejour_patient_ids, $patient_ids);
        }

        if (count($consultation_patient_ids) || count($sejour_patient_ids)) {
            $where[] = "patients.patient_id " . $ds->prepareIn(
                    array_unique(array_merge($consultation_patient_ids, $sejour_patient_ids))
                );
        }

        break;
}

// Séparation des patients par fonction
if ($use_function_distinct) {
    $where["function_id"] = "= '$function_id'";
} elseif ($use_group_distinct) {
    $user->user_id = $user_id;
    $user->loadMatchingObject();
    $curr_group_id              = $user->loadRefFunction()->group_id;
    $where["patients.group_id"] = "= '$curr_group_id'";
}
// CPatient ---------------------------
$data_patient = $data[CPatient::class];

if (!$one_field_presc && !$sejour_filled && !$consult_filled && !$interv_filled) {
    if (!empty($data_patient["_age_min"])) {
        $where[] = "DATEDIFF('" . CMbDT::dateTime() . "', patients.naissance)/365 > {$data_patient['_age_min']}";
    }
    if (!empty($data_patient["_age_max"])) {
        $where[] = "DATEDIFF('" . CMbDT::dateTime() . "', patients.naissance)/365 <= {$data_patient['_age_max']}";
    }
}

if (!empty($data_patient["medecin_traitant"])) {
    $one_field           = true;
    $medecin_traitant_id = $data_patient["medecin_traitant"];
    if ($only_medecin_traitant) {
        $where[] = "patients.medecin_traitant = '$medecin_traitant_id'";
    } else {
        $where[]                = "patients.medecin_traitant = '$medecin_traitant_id' OR 
                correspondant.medecin_id = '$medecin_traitant_id'";
        $ljoin["correspondant"] = "patients.patient_id = correspondant.patient_id";
    }
}

if ($libelle_evenement && $section != "sejour") {
    $ljoin["dossier_medical"]   = "dossier_medical.object_id = patients.patient_id";
    $ljoin["evenement_patient"] = "evenement_patient.dossier_medical_id = dossier_medical.dossier_medical_id";
    $where[]                    = "dossier_medical.object_class = 'CPatient' OR dossier_medical.dossier_medical_id IS NULL";
    $where[]                    = "evenement_patient.libelle LIKE '%$libelle_evenement%'";
}

// CAntecedent ---------------------------
$dm_data = $data[CAntecedent::class];

if (!empty($dm_data["rques"])) {
    $ljoin["dossier_medical"] = "dossier_medical.object_id = patients.patient_id";
    $ljoin["antecedent"]      = "antecedent.dossier_medical_id = dossier_medical.dossier_medical_id";
    $where[]                  = "dossier_medical.object_class = 'CPatient' OR dossier_medical.dossier_medical_id IS NULL";
    $one_field_atcd           = true;
}

// Make left join
if (!empty($dm_data["hidden_list_antecedents_cim10"])) {
    $ljoin["dossier_medical"] = "dossier_medical.object_id = patients.patient_id";
}

if (!empty($dm_data["antecedents_text"])
    || !empty($dm_data["allergie_text"])
) {
    $ljoin["dossier_medical"] = "dossier_medical.object_id = patients.patient_id";
    $ljoin["antecedent"]      = "antecedent.dossier_medical_id = dossier_medical.dossier_medical_id";
}
if (!empty($dm_data["hidden_list_pathologie_cim10"])
    || !empty($dm_data["pathologie_text"])
    || !empty($dm_data["hidden_list_probleme_cim10"])
    || !empty($dm_data["probleme_text"])
) {
    $ljoin["dossier_medical"] = "dossier_medical.object_id = patients.patient_id";
    $ljoin["pathologie"]      = "pathologie.dossier_medical_id = dossier_medical.dossier_medical_id";
}

// Antecedents CIM 10
if (!empty($dm_data["hidden_list_antecedents_cim10"])) {
    foreach (explode("|", $dm_data["hidden_list_antecedents_cim10"]) as $_cim10) {
        $where[] = "FIND_IN_SET('" . $_cim10 . "', REPLACE(dossier_medical.codes_cim, '|', ','))";
    }
}
// Antecedents texte libre
if (!empty($dm_data["antecedents_text"])) {
    $where[] = "antecedent.rques LIKE '%" . $dm_data["antecedents_text"] . "%'";
}

// PATHOLOGIES AND PROBLEMS
try {
    $need_op_general = ((!empty($dm_data["hidden_list_pathologie_cim10"]) || !empty($dm_data["pathologie_text"])) &&
        (!empty($dm_data["hidden_list_probleme_cim10"]) || !empty($dm_data["probleme_text"])));

    $need_or_patho = (!empty($dm_data["hidden_list_pathologie_cim10"]) && !empty($dm_data["pathologie_text"]));
    $need_or_prob  = (!empty($dm_data["hidden_list_probleme_cim10"]) && !empty($dm_data["probleme_text"]));

    $pathologie_search_active = (!empty($dm_data["hidden_list_pathologie_cim10"]) || !empty($dm_data["pathologie_text"]));
    $probleme_search_active   = (!empty($dm_data["hidden_list_probleme_cim10"]) || !empty($dm_data["probleme_text"]));

    $path_cim_or_path = "";

    // Pathologies code CIM10
    if (!empty($dm_data["hidden_list_pathologie_cim10"])) {
        $codes_cim        = explode("|", $dm_data["hidden_list_pathologie_cim10"]);
        $path_cim_or_path .= CRechercheDossierClinique::make_query_cim10(
            $codes_cim,
            CRechercheDossierClinique::TYPE_PATHOLOGY
        );
    }

    if (!empty($dm_data["pathologie_text"])) {
        $path_cim_or_path .= CRechercheDossierClinique::make_query_text(
            $dm["pathologie_text"],
            CRechercheDossierClinique::TYPE_PATHOLOGY,
            $need_or_prob
        );
    }

    $path_cim_or_prob = "";
    if (!empty($dm_data["hidden_list_probleme_cim10"])) {
        $codes_cim        = explode("|", $dm_data["hidden_list_probleme_cim10"]);
        $path_cim_or_prob .= CRechercheDossierClinique::make_query_cim10(
            $codes_cim,
            CRechercheDossierClinique::TYPE_PROBLEM
        );
    }

    if (!empty($dm_data["probleme_text"])) {
        $path_cim_or_prob .= CRechercheDossierClinique::make_query_text(
            $dm["problem_text"],
            CRechercheDossierClinique::TYPE_PROBLEM,
            $need_or_prob
        );
    }

    $ds = CSQLDataSource::get("std");

    if ($pathologie_search_active) {
        $create_temp_table_query_path = CRechercheDossierClinique::make_query_temporary_table(
            CRechercheDossierClinique::TYPE_PATHOLOGY,
            $path_cim_or_path
        );

        $ds->exec($create_temp_table_query_path);
    }

    if ($probleme_search_active) {
        $create_temp_table_query_prob = CRechercheDossierClinique::make_query_temporary_table(
            CRechercheDossierClinique::TYPE_PROBLEM,
            $path_cim_or_prob
        );

        $ds->exec($create_temp_table_query_prob);
    }

    $path_cim_and = "";
    if (!empty($dm_data["hidden_list_pathologie_cim10"])) {
        $codes_cim    = explode("|", $dm_data["hidden_list_pathologie_cim10"]);
        $path_cim_and .= CRechercheDossierClinique::make_query_cim10_temp(
            $codes_cim,
            CRechercheDossierClinique::TYPE_PATHOLOGY
        );
    }

    $path_cim_and .= ($need_op_general) ? " AND " : "";

    if (!empty($dm_data["hidden_list_probleme_cim10"])) {
        $codes_cim    = explode("|", $dm_data["hidden_list_probleme_cim10"]);
        $path_cim_and .= CRechercheDossierClinique::make_query_cim10_temp(
            $codes_cim,
            CRechercheDossierClinique::TYPE_PROBLEM
        );
    }

    if ($pathologie_search_active || $probleme_search_active) {
        $medical_files_query = CRechercheDossierClinique::make_query_get_from_temp_table(
            $pathologie_search_active,
            $probleme_search_active
        );

        $r = $ds->exec($medical_files_query);

        if ($pathologie_search_active) {
            $dump_query = CRechercheDossierClinique::make_query_dump_temporary_table(
                CRechercheDossierClinique::TYPE_PATHOLOGY
            );
            $ds->exec($dump_query);
        }
        if ($probleme_search_active) {
            $dump_query = CRechercheDossierClinique::make_query_dump_temporary_table(
                CRechercheDossierClinique::TYPE_PROBLEM
            );
            $ds->exec($dump_query);
        }

        $medical_files_fetch = [];
        while ($fetch = $ds->fetchRow($r)) {
            $medical_files_fetch[] = $fetch[0];
        }
        $medical_files_fetch = array_values($medical_files_fetch);
        $medical_files_query .= "where $path_cim_and";

        if (sizeof($medical_files_fetch) > 0) {
            $where["dossier_medical.dossier_medical_id"] = " in (" . implode(",", $medical_files_fetch) . ")";
        } else {
            $where[] = "1=2"; // If no result are given, "kill" the request
        }

        if (($need_or_patho || $need_or_prob) && sizeof($medical_files_fetch) > 0) {
            $where["dossier_medical.dossier_medical_id"] = " in (" . implode(",", $medical_files_fetch) . ")";
        }
    }
} catch (CMbException $e) {
    CAppUI::setMsg($e->getMessage(), UI_MSG_ERROR);
    echo CAppUI::getMsg();
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    CApp::rip();
}


if (!empty($dm_data["allergie_text"])) {
    $where[] = "antecedent.type = 'alle' AND antecedent.rques LIKE '%" . $dm_data["allergie_text"] . "%'";
}

// CTraitement ---------------------------
$traitement_data = $data[CTraitement::class];

if (!empty($traitement_data["traitement"])) {
    $ljoin["dossier_medical"] = "dossier_medical.object_id = patients.patient_id";
    $ljoin["traitement"]      = "traitement.dossier_medical_id = dossier_medical.dossier_medical_id";
    $where[]                  = "dossier_medical.object_class = 'CPatient' OR dossier_medical.dossier_medical_id IS NULL";
    $one_field_traitement     = true;
}

$list_patient  = [];
$list_group_by_patient = [];
$count_patient = 0;

$rjoinMed = [];
$rjoinMix = [];
$whereMed = [];
$whereMix = [];

// CPrescription ----------------------------
if ($one_field_presc) {
    $one_field_presc = true;
    $one_field       = true;
    switch ($section) {
        case "consult":
            if ($context === "prescription") {
                $ljoin["prescription"]      = "prescription.object_class = 'CConsultation' AND prescription.object_id = consultation.consultation_id";
                $where["prescription.type"] = "= 'externe'";
            } elseif ($context === "traitement") {
                $ljoin["dossier_medical"]   = "dossier_medical.object_id = patients.patient_id";
                $ljoin["prescription"]      = "prescription.object_class = 'CDossierMedical' AND prescription.object_id = dossier_medical.dossier_medical_id";
                $where["prescription.type"] = "= 'traitement'";
            }

            break;
        case "sejour":
        case "operation":
            $ljoin["prescription"]      = "prescription.object_class = 'CSejour' AND prescription.object_id = sejour.sejour_id";
            $where["prescription.type"] = "IN ('pre_admission', 'sejour', 'sortie')";
    }

    $rjoinMed["prescription_line_medicament"] = "prescription_line_medicament.prescription_id = prescription.prescription_id";

    if (!$commentaire) {
        $rjoinMix["prescription_line_mix"]      = "prescription_line_mix.prescription_id = prescription.prescription_id";
        $rjoinMix["prescription_line_mix_item"] =
            "prescription_line_mix_item.prescription_line_mix_id = prescription_line_mix.prescription_line_mix_id";
    }

    $whereMed[] = "prescription_line_medicament.active = '1'";
    $whereMix[] = "prescription_line_mix.active = '1'";

    if ($code_cis) {
        $whereMed[] = "prescription_line_medicament.code_cis = '$code_cis'";
        $whereMix[] = "prescription_line_mix_item.code_cis = '$code_cis'";
    } else {
        if ($code_ucd) {
            $whereMed[] = "prescription_line_medicament.code_ucd = '$code_ucd'" .
                $whereMix[] = "prescription_line_mix_item.code_ucd = '$code_ucd'";
        } elseif ($libelle_produit) {
            $whereMed[] = "prescription_line_medicament.code_cis " . CSQLDataSource::prepareIn($codes_cis);
            $whereMix[] = "prescription_line_mix_item.code_cis " . CSQLDataSource::prepareIn($codes_cis);
        } else {
            if ($classes_atc) {
                $whereMed[] = "prescription_line_medicament.atc RLIKE '(^$classes_atc)'";
                $whereMix[] = "prescription_line_mix_item.atc RLIKE '(^$classes_atc)'";
            } else {
                if ($composant) {
                    $composition_bdm = new CMedicamentComposant();
                    $produits        = $composition_bdm->loadListProduits($composant, 0, 0);
                    switch (CMedicament::getBase()) {
                        default:
                        case "bcb":
                            $codes_cip  = @CMbArray::pluck($produits, "code_cip");
                            $whereMed[] = "prescription_line_medicament.code_cip " . CSQLDataSource::prepareIn(
                                    $codes_cip
                                );
                            $whereMix[] = "prescription_line_mix_item.code_cip " . CSQLDataSource::prepareIn(
                                    $codes_cip
                                );
                            break;
                        case "vidal":
                            $codes_cis  = @CMbArray::pluck($produits, "code_cis");
                            $whereMed[] = "prescription_line_medicament.code_cis " . CSQLDataSource::prepareIn(
                                    $codes_cis
                                );
                            $whereMix[] = "prescription_line_mix_item.code_cis " . CSQLDataSource::prepareIn(
                                    $codes_cis
                                );
                    }
                } else {
                    if ($indication) {
                        switch (CMedicament::getBase()) {
                            default:
                            case "bcb":
                                $bcb_indication = new CBcbIndication();
                                $produits       = $bcb_indication->searchProduits($indication, $type_indication);
                                $codes_cip      = CMbArray::pluck($produits, "Code_CIP");
                                $whereMed[]     = "prescription_line_medicament.code_cip " . CSQLDataSource::prepareIn(
                                        $codes_cip
                                    );
                                $whereMix[]     = "prescription_line_mix_item.code_cip " . CSQLDataSource::prepareIn(
                                        $codes_cip
                                    );
                                break;
                            case "vidal":
                                $indication_bdm = new CMedicamentIndication();
                                $produits       = $indication_bdm->loadListProduits($indication);
                                $codes_cis      = @CMbArray::pluck($produits, "code_cis");
                                $whereMed[]     = "prescription_line_medicament.code_cis " . CSQLDataSource::prepareIn(
                                        $codes_cis
                                    );
                                $whereMix[]     = "prescription_line_mix_item.code_cis " . CSQLDataSource::prepareIn(
                                        $codes_cis
                                    );
                        }
                    } else {
                        if ($commentaire) {
                            $whereMed["prescription_line_medicament.commentaire"] = "LIKE '%" . addslashes(
                                    $commentaire
                                ) . "%'";
                        }
                    }
                }
            }
        }
    }
}

$list_objects = [];

if ($one_field) {
    // Pour la recherche sur la prescription :
    // deux passages obligés (une pour les lignes de médicament, l'autre pour les lignes de prescription)

    $other_fields = "";

    if ($one_field_presc) {
        $other_fields = ", prescription_line_medicament.prescription_line_medicament_id";
    }

    if ($one_field_atcd) {
        $other_fields .= ", antecedent.antecedent_id";
    }

    if ($one_field_traitement) {
        $other_fields .= ", traitement.traitement_id";
    }

    // Première requête (éventuellement pour les lignes de médicament)
    $request = new CRequest();

    if ($consult_filled) {
        $request->addSelect("DISTINCT consultation.consultation_id, patients.patient_id" . $other_fields);
        $request->addTable("consultation");
        $request->addOrder("patients.nom ASC, plageconsult.date ASC");
    } elseif ($sejour_filled) {
        $request->addSelect("DISTINCT sejour.sejour_id, patients.patient_id" . $other_fields);
        $request->addTable("sejour");
        $request->addOrder("patients.nom ASC, sejour.entree_prevue ASC");
    } elseif ($interv_filled) {
        $request->addSelect("DISTINCT operations.operation_id, patients.patient_id" . $other_fields);
        $request->addTable("operations");
        $request->addOrder("patients.nom ASC, operations.date ASC");
    } else {
        $request->addSelect("DISTINCT patients.patient_id");
        $request->addTable("patients");
        $request->addOrder("patients.nom ASC");
    }

    $request->addLJoin($ljoin);
    $request->addRJoin($rjoinMed);
    $request->addWhere($where);
    $request->addWhere($whereMed);

    if (!$export) {
        $request->setLimit("$start,30");
    }

    $results = $ds->loadList($request->makeSelect());

    $request->setLimit("");
    $count_results = $ds->countRows($request->makeSelect());

    // Eventuelle deuxième requête (pour les lines mixes)
    if (!$commentaire && $one_field_presc) {
        $request_b = new CRequest();

        if ($one_field_presc) {
            $other_fields = ", prescription_line_mix_item.prescription_line_mix_item_id";
        }

        if ($one_field_atcd) {
            $other_fields .= ", antecedent.antecedent_id";
        }

        if ($one_field_traitement) {
            $other_fields .= ", traitement.traitement_id";
        }

        if ($consult_filled) {
            $request_b->addSelect("consultation.consultation_id, patients.patient_id" . $other_fields);
            $request_b->addTable("consultation");
            $request_b->addOrder("patients.nom ASC, plageconsult.date ASC");
        } elseif ($sejour_filled) {
            $request_b->addSelect("sejour.sejour_id, patients.patient_id" . $other_fields);
            $request_b->addTable("sejour");
            $request_b->addOrder("patients.nom ASC, sejour.entree_prevue ASC");
        } elseif ($interv_filled) {
            $request_b->addSelect("operations.operation_id, patients.patient_id" . $other_fields);
            $request_b->addTable("operations");
            $request_b->addOrder("patients.nom ASC, operations.date ASC");
        } else {
            $request_b->addSelect("patients.patient_id");
            $request_b->addTable("patients");
            $request_b->addOrder("patients.nom ASC");
        }

        $request_b->addLJoin($ljoin);
        $request_b->addRJoin($rjoinMix);
        $request_b->addWhere($where);
        $request_b->addWhere($whereMix);

        if (!$export) {
            $request_b->setLimit("$start,30");
        }

        $results = array_merge($results, $ds->loadList($request_b->makeSelect()));

        $request_b->setLimit("");
        $count_results += $ds->countRows($request_b->makeSelect());
    }

    foreach ($results as $_result) {
        $_patient_id = $_result["patient_id"];
        $pat         = new CPatient();
        $pat->load($_patient_id);

        // Recherche sur un antécédent
        if (isset($_result["antecedent_id"])) {
            $_atcd = new CAntecedent();
            $_atcd->load($_result["antecedent_id"]);
            $pat->_ref_antecedent = $_atcd;
        } else {
            // On affiche tous les antécédents du patient
            $dossier_medical        = $pat->loadRefDossierMedical(false);
            $pat->_refs_antecedents = $dossier_medical->loadRefsAntecedents();
            $pat->_refs_allergies   = $dossier_medical->loadRefsAllergies();
            $pat->_ext_codes_cim    = $dossier_medical->_ext_codes_cim;
        }

        $pat->_ref_dossier_medical->_ref_pathologies = $pat->_ref_dossier_medical->loadRefsPathologies();

        if (isset($_result["prescription_line_medicament_id"])) {
            $line = new CPrescriptionLineMedicament();
            $line->load($_result["prescription_line_medicament_id"]);
            $pat->_distant_line = $line;
        } else {
            if (isset($_result["prescription_line_mix_item_id"])) {
                $line = new CPrescriptionLineMixItem();
                $line->load($_result["prescription_line_mix_item_id"]);
                $pat->_distant_line = $line;
            }
        }
        if ($sejour_filled) {
            $sejour = new CSejour();
            $sejour->load($_result["sejour_id"]);
            $pat->_distant_object = $sejour;
            $pat->_age_epoque     = intval(CMbDT::daysRelative($pat->naissance, $sejour->entree) / 365);
        } else {
            if ($consult_filled) {
                $consult = new CConsultation();
                $consult->load($_result["consultation_id"]);
                $pat->_distant_object = $consult;
                $consult->loadRefPlageConsult();
                $pat->_age_epoque = intval(
                    CMbDT::daysRelative($pat->naissance, $consult->_ref_plageconsult->date) / 365
                );
            } else {
                if ($interv_filled) {
                    $interv = new COperation();
                    $interv->load($_result["operation_id"]);
                    $interv->loadRefPlageOp();
                    $pat->_distant_object = $interv;
                    $pat->_age_epoque     = intval(CMbDT::daysRelative($pat->naissance, $interv->_datetime_best) / 365);
                }
            }
        }
        $list_patient[] = $pat;
    }

    // Le count total
    $request->select = ["count(distinct patients.patient_id)"];
    $request->limit  = null;
    $count_patient   = $ds->loadResult($request->makeSelect());

    if (!$commentaire && $one_field_presc) {
        $request_b->select = ["count(*)"];
        $request_b->limit  = null;
        $count_patient     += $ds->loadResult($request_b->makeSelect());
    }
}

if($group_by_patient){
    $list_group_by_patient =[];
    foreach ($list_patient as $_pat){
        $list_group_by_patient["$_pat->_id"][] = $_pat;
    }
}

if ($export) {
    $csv = new CCSVFile();

    $titles = [
        "Patient",
        "Age à l'époque",
        "Dossier Médical",
        "Evenement",
        "Prescription",
        "DCI",
        "Code ATC",
        "Libellé ATC",
        "Commentaire / Motif",
    ];
    $csv->writeLine($titles);

    foreach ($list_patient as $_patient) {
        $dossier_medical = "";

        if (isset($_patient->_ref_antecedent)) {
            $dossier_medical .= "Antécédents :\n $_patient->_ref_traitement->_view";
        } elseif (isset($_patient->_refs_antecedents) && count($_patient->_refs_antecedents)) {
            $dossier_medical .= "Antécédents :\n";
            foreach ($_patient->_refs_antecedents as $_antecedent) {
                if ($_antecedent->type == "alle") {
                    continue;
                }
                $dossier_medical .= $_antecedent->_view . "\n";
            }
        }

        if (isset($_patient->_refs_allergies) && count($_patient->_refs_allergies)) {
            $dossier_medical .= "Allergies :\n";
            foreach ($_patient->_refs_allergies as $_allergie) {
                $dossier_medical .= $_allergie->_view . "\n";
            }
        }

        if (isset($_patient->_ext_codes_cim) && count($_patient->_ext_codes_cim)) {
            $dossier_medical .= "Diagnosctics CIM:\n";
            foreach ($_patient->_ext_codes_cim as $_ext_code_cim) {
                $dossier_medical .= "$_ext_code_cim->code: $_ext_code_cim->libelle \n";
            }
        }

        $object_view = "";

        if (isset($_patient->_distant_object)) {
            $object = $_patient->_distant_object;
            switch (CClassMap::getSN($object)) {
                case "CConsultation":
                    $object_view = "Consultation du " . CMbDT::dateToLocale($object->_ref_plageconsult->date) .
                        " à " . CMbDT::format($object->heure, "%Hh:%M");
                    break;
                case "CSejour":
                    $object_view = "Séjour du " .
                        CMbDT::dateToLocale(CMbDT::date($object->entree)) . "au " .
                        CMbDT::dateToLocale(CMbDT::date($object->sortie));
                    break;
                case "COperation":
                    $object_view = "Intervention du " . CMbDT::dateToLocale(CMbDT::date($object->_datetime_best));
            }
        }

        $content_line = "";

        if (isset($_patient->_distant_line)) {
            $content_line = $_patient->_distant_line;
        }

        $data_line = [
            $_patient->_view . " (" . strtoupper($_patient->sexe) . ")",
            $_patient->_age_epoque,
            $dossier_medical,
            $object_view,
            $content_line ? $content_line->_view : "",
            $content_line ? $content_line->_ref_produit->_dci_view : "",
            $content_line ? $content_line->_ref_produit->_ref_ATC_5_code : "",
            $content_line ? $content_line->_ref_produit->_ref_ATC_5_libelle : "",
            $content_line ? $content_line->commentaire : "",
        ];

        $csv->writeLine($data_line);
    }

    $period = "du_" . ($from ? $from : "_") . "_au_" . ($to ? $to : "_");
    $csv->stream("dossiers_clinique_" . $period);
    CApp::rip();
}

// Création du template
$smarty = new CSmartyDP();

if ($group_by_patient) {
    $smarty->assign('list_group_by_patient', $list_group_by_patient);
    $smarty->assign('count_nb_patient', $count_patient);
} else {
    $smarty->assign("list_patient", $list_patient);
}
$smarty->assign("one_field", $one_field);
$smarty->assign("one_field_presc", $one_field_presc);
$smarty->assign("start", $start);
$smarty->assign("user_id", $user_id);
$smarty->assign('group_by_patient', $group_by_patient);
$smarty->assign("count_patient", $count_results);
$smarty->assign("from", $from);
$smarty->assign("to", $to);
$smarty->assign("mod_tamm", $mod_tamm);

$smarty->display("inc_recherche_dossier_clinique_results.tpl");

