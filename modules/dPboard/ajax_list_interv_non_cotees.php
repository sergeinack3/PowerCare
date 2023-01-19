<?php

/**
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Ccam\CFilterCotation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$chir_id                  = CView::get("praticien_id", 'ref class|CMediusers');
$function_id              = CView::get('function_id', 'ref class|CFunctions');
$all_prats                = CView::get("all_prats", 'bool default|0', false);
$end_date                 = CView::get("end_date", 'date default|now', false);
$begin_date               = CView::get("begin_date", 'date default|' . CMbDT::date("-1 week", $end_date), false);
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
$excess_fee_chir_status   = CView::get('excess_fee_chir_status', 'enum list|non_regle|cb|cheque|espece|virement');
$excess_fee_anesth_status = CView::get('excess_fee_anesth_status', 'enum list|non_regle|cb|cheque|espece|virement');

CView::checkin();
CView::enforceSlave();

if ($object_classes != '') {
    $object_classes = str_replace(",", "|", $object_classes);
    $object_classes = explode('|', $object_classes);
} else {
    $object_classes = ['CConsultation', 'COperation', 'CSejour', 'CSejour-seance'];
}

$mediuser = CMediusers::get();
if ($mediuser->isProfessionnelDeSante() && !$chir_id && !$function_id) {
    $chir_id = $mediuser->_id;
}

$user = new CMediusers();
$user->load($chir_id);

if ($all_prats) {
    $chir_id = 0;
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

$filter  = new CFilterCotation($filters);
$objects = $filter->getCotationDetails();

$interventions               = [];
$total_operations_non_cotees = 0;
if (array_key_exists('COperation', $objects)) {
    $interventions = $objects['COperation'];
}

/* Calcul du nombre d'interventions sans codes pour les 3 derniers mois */
if (!$all_prats) {
    $operation = new COperation();
    if ($user->_id) {
        $chirs = [$user->_id];
    } else {
        $user  = CMediusers::get();
        $chirs = array_keys($user->loadPraticiens(PERM_READ));
    }

    $total_operations_non_cotees = CFilterCotation::countUncodedCodable($user, 'COperation', $chirs);
}

$consultations                  = [];
$total_consultations_non_cotees = 0;
if (array_key_exists('CConsultation', $objects)) {
    $consultations = $objects['CConsultation'];
}

/* Calcul du nombre d'interventions sans codes pour les 3 derniers mois */
if (!$all_prats) {
    $total_consultations_non_cotees = CFilterCotation::countUncodedCodable($user, 'CConsultation');
}

$sejours                 = [];
$total_sejours_non_cotes = 0;
if (array_key_exists('CSejour', $objects)) {
    $sejours = $objects['CSejour'];
}

/* Calcul du nombre de séjours sans codes pour les 3 derniers mois */
if (!$all_prats) {
    $total_sejours_non_cotes = CFilterCotation::countUncodedCodable($user, 'CSejour');
}

$seances                  = [];
$total_seances_non_cotees = 0;
if (array_key_exists('CSejour-seance', $objects)) {
    $seances = $objects['CSejour-seance'];
}

/* Calcul du nombre de séjours de type séance sans codes pour les 3 derniers mois */
if (!$all_prats) {
    $total_seances_non_cotees = CFilterCotation::countUncodedCodable($user, 'CSejour-seance');
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

if (!$export) {
    $smarty = new CSmartyDP("modules/dPboard");

    $smarty->assign("totals", $objects['totals']);
    $smarty->assign("interventions", $interventions);
    $smarty->assign("consultations", $consultations);
    $smarty->assign("sejours", $sejours);
    $smarty->assign("seances", $seances);
    $smarty->assign("begin_date", $begin_date);
    $smarty->assign("end_date", $end_date);
    $smarty->assign("all_prats", $all_prats);
    $smarty->assign("board", $board);
    $smarty->assign('objects_whithout_codes', $objects_whithout_codes);
    $smarty->assign('show_unexported_acts', $show_unexported_acts);
    $smarty->assign('ccam_codes', $ccam_codes);
    $smarty->assign('libelle', $libelle);
    $smarty->assign('display_operations', $display_operations);
    $smarty->assign('display_consultations', $display_consultations);
    $smarty->assign('display_sejours', $display_sejours);
    $smarty->assign('display_seances', $display_seances);
    $smarty->assign('nda', $nda);
    $smarty->assign('patient_id', $patient_id);
    $smarty->assign('codage_lock_status', $codage_lock_status);
    $smarty->assign('total_operations_non_cotees', $total_operations_non_cotees);
    $smarty->assign('total_consultations_non_cotees', $total_consultations_non_cotees);
    $smarty->assign('total_sejours_non_cotes', $total_sejours_non_cotes);
    $smarty->assign('total_seances_non_cotees', $total_seances_non_cotees);
    $smarty->assign('date_begin_op_non_cotees', CMbDT::date('-3 MONTHS'));
    $smarty->assign('date_end_op_non_cotees', CMbDT::date());
    $smarty->assign('object_classes', $object_classes);
    if ($user->_id && $user->isProfessionnelDeSante()) {
        $smarty->assign('chirSel', $user);
    }

    $smarty->display("inc_list_interv_non_cotees");
} else {
    $csv = new CCSVFile();

    /** @var array $line */
    $line = [
        "Praticiens",
        "Patient",
        "Evènement",
        "Actes Non cotés",
        "Codes prévus",
        "Actes cotés",
    ];
    if (!$all_prats) {
        unset($line[0]);
    }
    $csv->writeLine($line);

    foreach ($interventions as $_interv) {
        $line = [];
        if ($all_prats) {
            $chir = $_interv->_ref_chir->_view;
            if ($_interv->_ref_anesth->_id) {
                $chir .= "\n" . $_interv->_ref_anesth->_view;
            }
            $line[] = $chir;
        }
        $line[] = $_interv->_ref_patient->_view;

        $interv = $_interv->_view;
        if ($_interv->_ref_sejour->libelle) {
            $interv .= "\n" . $_interv->_ref_sejour->libelle;
        }
        if ($_interv->libelle) {
            $interv .= "\n" . $_interv->libelle;
        }
        $line[] = $interv;

        $line[] = (!$_interv->_count_actes && !$_interv->_ext_codes_ccam)
            ? "Aucun prévu"
            : $_interv->_actes_non_cotes . "acte(s)";

        $actes = "";
        foreach ($_interv->_ext_codes_ccam as $code) {
            $actes .= $actes == "" ? "" : "\n";
            $actes .= "$code->code";
        }
        $line[] = $actes;

        $actes_cotes = "";
        $code = "";
        foreach ($_interv->_ref_actes_ccam as $_acte) {
            $code .= $actes_cotes == "" ? "" : "\n";
            $code .= $_acte->code_acte . "-" . $_acte->code_activite . "-" . $_acte->code_phase;
            if ($_acte->modificateurs) {
                $code .= " MD:" . $_acte->modificateurs;
            }
            if ($_acte->montant_depassement) {
                $code .= " DH:" . $_acte->montant_depassement;
            }
            $actes_cotes .= "$code";
        }
        $line[] = $actes_cotes;

        $csv->writeLine($line);
    }

    foreach ($consultations as $_consult) {
        $line = [];

        if ($all_prats) {
            $line[] = $_consult->_ref_chir->_view;
        }

        $line[] = $_consult->_ref_patient->_view;

        $view = "Consultation le " . CMbDT::format($_consult->_datetime, CAppUI::conf("date"));
        if ($_consult->_ref_sejour && $_consult->_ref_sejour->libelle) {
            $view .= $_consult->_ref_sejour->libelle;
        }
        $line[] = $view;

        $line[] = (!$_consult->_count_actes && !$_consult->_ext_codes_ccam)
            ? "Aucun prévu"
            : $_consult->_actes_non_cotes . "acte(s)";

        $actes = "";
        foreach ($_consult->_ext_codes_ccam as $code) {
            $actes .= $actes == "" ? "" : "\n";
            $actes .= "$code->code";
        }
        $line[] = $actes;

        $actes_cotes = "";
        $code = "";
        foreach ($_consult->_ref_actes_ccam as $_acte) {
            $code .= $actes_cotes == "" ? "" : "\n";
            $code .= $_acte->code_acte . "-" . $_acte->code_activite . "-" . $_acte->code_phase;
            if ($_acte->modificateurs) {
                $code .= " MD:" . $_acte->modificateurs;
            }
            if ($_acte->montant_depassement) {
                $code .= " DH:" . $_acte->montant_depassement;
            }
            $actes_cotes .= "$code";
        }
        $code = "";
        foreach ($_consult->_ref_actes_ngap as $_acte) {
            $code .= $actes_cotes == "" ? "" : "\n";
            if ($_acte->quantite > 1) {
                $actes_cotes .= "$_acte->quantite x ";
            }
            $actes_cotes .= $_acte->code;
            if ($_acte->coefficient != 1) {
                $actes_cotes .= " ($_acte->coefficient)";
            }
            if ($_acte->complement) {
                $actes_cotes .= " $_acte->complement";
            }
            $actes_cotes .= " $_acte->_tarif";
        }
        $line[] = $actes_cotes;

        $csv->writeLine($line);
    }

    foreach ($sejours as $_sejour) {
        $line = [];

        if ($all_prats) {
            $line[] = $_sejour->_ref_chir->_view;
        }

        $line[] = $_sejour->_ref_patient->_view;

        $line[] = $_sejour->_view;

        $line[] = (!$_sejour->_count_actes && !$_sejour->_ext_codes_ccam)
            ? "Aucun prévu"
            : $_sejour->_actes_non_cotes . "acte(s)";

        $actes = "";
        foreach ($_sejour->_ext_codes_ccam as $code) {
            $actes .= $actes == "" ? "" : "\n";
            $actes .= "$code->code";
        }
        $line[] = $actes;

        $actes_cotes = "";
        $code = "";
        foreach ($_sejour->_ref_actes_ccam as $_acte) {
            $code .= $actes_cotes == "" ? "" : "\n";
            $code .= $_acte->code_acte . "-" . $_acte->code_activite . "-" . $_acte->code_phase;
            if ($_acte->modificateurs) {
                $code .= " MD:" . $_acte->modificateurs;
            }
            if ($_acte->montant_depassement) {
                $code .= " DH:" . $_acte->montant_depassement;
            }
            $actes_cotes .= "$code";
        }
        $code = "";
        foreach ($_sejour->_ref_actes_ngap as $_acte) {
            $code .= $actes_cotes == "" ? "" : "\n";
            if ($_acte->quantite > 1) {
                $actes_cotes .= "$_acte->quantite x ";
            }
            $actes_cotes .= $_acte->code;
            if ($_acte->coefficient != 1) {
                $actes_cotes .= " ($_acte->coefficient)";
            }
            if ($_acte->complement) {
                $actes_cotes .= " $_acte->complement";
            }
            $actes_cotes .= " $_acte->_tarif";
        }
        $line[] = $actes_cotes;

        $csv->writeLine($line);
    }

    $csv->stream("export-intervention_non_cotes-" . $begin_date . "-" . $end_date);
}
