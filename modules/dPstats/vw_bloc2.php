<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\COperationWorkflow;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$mode            = CValue::get("mode", "html");
$deblist         = CValue::getOrSession("deblistbloc", CMbDT::date("-1 DAY"));
$finlist         = max(CValue::get("finlistbloc", $deblist), $deblist);
$bloc_id         = CValue::getOrSession("bloc_id");
$type            = CValue::get("type", "prevue");
$show_constantes = CValue::get('show_constantes');

$display_form = true;

CView::enforceSlave();

if ($mode == "html") {
    $miner = new COperationWorkflow();
    $miner->warnUsage();
}

$operation_ids = CValue::post('operation_ids');
if ($operation_ids) {
    $operation_ids = explode('-', $operation_ids);

    $type            = 'all';
    $display_form    = false;
    $show_constantes = true;

    $operation  = new COperation();
    $operations = $operation->loadGroupList(['operation_id' => CSQLDataSource::prepareIn($operation_ids)]);

    COperation::massLoadFwdRef($operations, 'plageop_id');
    $nb_interv = 1;
    /** @var COperation $_operation */
    foreach ($operations as $_operation) {
        $_operation->_rank_reel = $_operation->entree_salle ? $nb_interv : '';
        $_operation->_pat_next  = null;
        $nb_interv++;
    }
} else {
    $blocs = CGroups::loadCurrent()->loadBlocs(PERM_READ, true, "nom", ["actif" => "= '1'"], ["actif" => "= '1'"]);
    $bloc  = new CBlocOperatoire();
    $bloc->load($bloc_id);

    $where          = [];
    $where["stats"] = "= '1'";

    if ($bloc->_id) {
        $where["bloc_id"] = "= '$bloc->_id'";
        $where["actif"]   = "= '1'";
    }

    $salle  = new CSalle();
    $salles = $salle->loadGroupList($where);

    // Récupération des plages
    $where = [
        "date"     => "BETWEEN '$deblist 00:00:00' AND '$finlist 23:59:59'",
        "salle_id" => CSQLDataSource::prepareIn(array_keys($salles)),
    ];

    /** @var CPlageOp[] $plages */
    $plages = [];

    /** @var COperation[] $operations */
    $operations = [];

    /** @var int $nb_interv */
    $nb_interv = 1;

    if ($type == "prevue") {
        $plage  = new CPlageOp();
        $order  = "date, salle_id, debut, chir_id";
        $plages = $plage->loadList($where, $order);
        CStoredObject::massLoadFwdRef($plages, "chir_id");
        CStoredObject::massLoadFwdRef($plages, "spec_id");

        // Récupération des interventions
        foreach ($plages as $_plage) {
            $_plage->loadRefOwner();
            $_plage->loadRefAnesth();
            $_plage->loadRefSalle();
            $_plage->loadRefsOperations(false, "entree_salle");

            $nb_interv = 1;
            foreach ($_plage->_ref_operations as $_operation) {
                // Calcul du rang
                $_operation->_rank_reel = $_operation->entree_salle ? $nb_interv : "";
                $nb_interv++;
                $next                  = next($_plage->_ref_operations);
                $_operation->_pat_next = (($next !== false) ? $next->entree_salle : null);

                $_operation->_ref_plageop = $_plage;

                $operations[$_operation->_id] = $_operation;
            }
        }
    } else {
        // Récupération des interventions
        $where   = [];
        $where[] = "annulee = '0'";
        $where[] = "date BETWEEN '$deblist' AND '$finlist'";
        $where[] = "salle_id " . CSQLDataSource::prepareIn(array_keys($salles)) . " OR salle_id IS NULL";

        if ($type != "all") {
            $where["plageop_id"] = "IS NULL";
        }
        $order      = "date, salle_id, entree_salle, chir_id";
        $operation  = new COperation();
        $operations = $operation->loadGroupList($where, $order);

        $salle_id = null;
        foreach ($operations as $_operation) {
            // Calcul du rang
            if ($salle_id != $_operation->salle_id) {
                $salle_id  = $_operation->salle_id;
                $nb_interv = 1;
            }
            $_operation->_rank_reel = $_operation->entree_salle ? $nb_interv : "";
            $nb_interv++;
            $_operation->_pat_next = null;
        }
    }
}

// Chargement exhaustif
CStoredObject::massLoadFwdRef($operations, "anesth_id");
CStoredObject::massLoadFwdRef($operations, "chir_id");
$sejours  = CStoredObject::massLoadFwdRef($operations, "sejour_id");
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");

CSejour::massLoadNDA($sejours);
CPatient::massLoadIPP($patients);

CStoredObject::massLoadBackRefs($operations, 'workflow');

foreach ($operations as $_operation) {
    $_operation->updateDatetimes();
    $_operation->loadRefAnesth();
    $_operation->loadRefPlageOp();
    $_operation->updateSalle();
    $_operation->loadRefChir()->loadRefFunction();
    $_operation->loadRefPatient();
    $_operation->loadRefWorkflow();
    $_operation->loadRefTypeAnesth();

    // Ajustements ad hoc
    if ($plage = $_operation->_ref_plageop) {
        $_operation->_ref_salle_prevue = $plage->_ref_salle;
        $_operation->_ref_salle_reelle = $_operation->_ref_salle;
        $_operation->_deb_plage        = $plage->debut;
        $_operation->_fin_plage        = $plage->fin;
    } else {
        $_operation->_ref_salle_prevue = $_operation->_ref_salle;
        $_operation->_ref_salle_reelle = $_operation->_ref_salle;
        $_operation->_deb_plage        = $_operation->date;
        $_operation->_fin_plage        = $_operation->date;
    }

    $_patient = $_operation->_ref_sejour->_ref_patient;
    $_patient->evalAge($_operation->date);

    if ($show_constantes) {
        $_constantes       = $_patient->getFastPoidsTaille($_operation->_datetime);
        $_patient->_poids  = $_constantes['poids'];
        $_patient->_taille = $_constantes['taille'];
    }
}

if ($mode == "csv") {
    $csvName = "stats_bloc_" . $deblist . "_" . $finlist . "_" . $bloc_id;
    $csv     = new CCSVFile();
    $title   = [
        "Date",
        "Salle prévue",
        "Salle réelle",
        "Date début vacation",
        "Heure début vacation",
        "Date fin vacation",
        "Heure fin vacation",
        "N° d'ordre prévu",
        CAppUI::tr('COperation-time_operation'),
        "N° d'ordre réel",
        CAppUI::tr('COperation-entree_salle'),
        "IPP",
        "Patient",
        "Âge",
        "Poids",
        "Taille",
        "NDA",
        "Prise en charge",
        "Date entrée prévue",
        "Heure entrée prévue",
        "Date entrée réelle",
        "Heure entrée réelle",
        "Date sortie prévue",
        "Heure sortie prévue",
        "Date sortie réelle",
        "Heure sortie réelle",
        "Durée séjour",
        "Chirurgien",
        "Anesthésiste",
        "Libellé",
        "DP",
        "CCAM",
        "Type d'anesthésie",
        "Côté opéré",
        "Code ASA",
        "Date placement programme",
        "Heure placement programme",
        "Date entrée bloc",
        "Heure entrée bloc",
        "Date entrée salle",
        "Heure entrée salle",
        "Date début d'induction",
        "Heure début d'induction",
        "Date fin d'induction",
        "Heure fin d'induction",
        "Date début d'intervention",
        "Heure début d'intervention",
        "Date fin d'intervention",
        "Heure fin d'intervention",
        "Date sortie salle",
        "Heure sortie salle",
        "patient suivant",
        "Date entrée reveil",
        "Heure entrée reveil",
        "Date sortie reveil",
        "Heure sortie reveil",
    ];

    // Si on cache les constantes, suppression des colonnes poids et taille
    if (!$show_constantes) {
        if (($key = array_search("Poids", $title)) !== false) {
            unset($title[$key]);
        }
        if (($key = array_search("Taille", $title)) !== false) {
            unset($title[$key]);
        }
    }
    $csv->writeLine($title);

    foreach ($operations as $_operation) {
        $_sejour  = $_operation->_ref_sejour;
        $_patient = $_sejour->_ref_patient;
        $_patient->evalAge($_operation->date);

        if ($show_constantes) {
            $_constantes       = $_patient->getFastPoidsTaille($_operation->_datetime);
            $_patient->_poids  = $_constantes['poids'];
            $_patient->_taille = $_constantes['taille'];
        }

        $line_op = [
            CMbDT::date($_operation->_datetime),
            $_operation->_ref_salle_prevue,
            $_operation->_ref_salle_reelle,
            $_operation->_ref_plageop->date,
            $_operation->_deb_plage,
            $_operation->_ref_plageop->date,
            $_operation->_fin_plage,
            $_operation->rank,
            $_operation->time_operation,
            $_operation->_rank_reel,
            $_operation->entree_salle,
            $_patient->_IPP,
            $_patient->_view,
            $_patient->_annees,
            $_patient->_poids,
            $_patient->_taille,
            $_sejour->_NDA,
            $_sejour->type,
            ($_sejour->entree_prevue) ? CMbDT::date($_sejour->entree_prevue) : "",
            ($_sejour->entree_prevue) ? CMbDT::time($_sejour->entree_prevue) : "",
            ($_sejour->entree_reelle) ? CMbDT::date($_sejour->entree_reelle) : "",
            ($_sejour->entree_reelle) ? CMbDT::time($_sejour->entree_reelle) : "",
            ($_sejour->sortie_prevue) ? CMbDT::date($_sejour->sortie_prevue) : "",
            ($_sejour->sortie_prevue) ? CMbDT::time($_sejour->sortie_prevue) : "",
            ($_sejour->sortie_reelle) ? CMbDT::date($_sejour->sortie_reelle) : "",
            ($_sejour->sortie_reelle) ? CMbDT::time($_sejour->sortie_reelle) : "",
            $_sejour->_duree_reelle,
            $_operation->_ref_chir->_view,
            $_operation->_ref_anesth->_view,
            $_operation->libelle,
            $_sejour->DP,
            $_operation->codes_ccam,
            $_operation->_lu_type_anesth,
            CAppUI::tr("COperation.cote.$_operation->cote"),
            $_operation->ASA,
            ($_operation->_ref_workflow->date_creation) ? CMbDT::date($_operation->_ref_workflow->date_creation) : "",
            ($_operation->_ref_workflow->date_creation) ? CMbDT::time($_operation->_ref_workflow->date_creation) : "",
            ($_operation->entree_bloc) ? CMbDT::date($_operation->entree_bloc) : "",
            ($_operation->entree_bloc) ? CMbDT::time($_operation->entree_bloc) : "",
            ($_operation->entree_salle) ? CMbDT::date($_operation->entree_salle) : "",
            ($_operation->entree_salle) ? CMbDT::time($_operation->entree_salle) : "",
            ($_operation->induction_debut) ? CMbDT::date($_operation->induction_debut) : "",
            ($_operation->induction_debut) ? CMbDT::time($_operation->induction_debut) : "",
            ($_operation->induction_fin) ? CMbDT::date($_operation->induction_fin) : "",
            ($_operation->induction_fin) ? CMbDT::time($_operation->induction_fin) : "",
            ($_operation->debut_op) ? CMbDT::date($_operation->debut_op) : "",
            ($_operation->debut_op) ? CMbDT::time($_operation->debut_op) : "",
            ($_operation->fin_op) ? CMbDT::date($_operation->fin_op) : "",
            ($_operation->fin_op) ? CMbDT::time($_operation->fin_op) : "",
            ($_operation->sortie_salle) ? CMbDT::date($_operation->sortie_salle) : "",
            ($_operation->sortie_salle) ? CMbDT::time($_operation->sortie_salle) : "",
            ($_operation->_pat_next) ? CMbDT::time($_operation->_pat_next) : "",
            ($_operation->entree_reveil) ? CMbDT::date($_operation->entree_reveil) : "",
            ($_operation->entree_reveil) ? CMbDT::time($_operation->entree_reveil) : "",
            ($_operation->sortie_reveil_reel) ? CMbDT::date($_operation->sortie_reveil_reel) : "",
            ($_operation->sortie_reveil_reel) ? CMbDT::time($_operation->sortie_reveil_reel) : "",
        ];

        // Si on cache les constantes, suppression des valeurs poids et taille
        if (!$show_constantes) {
            // ATTENTION : EN CAS DE RAJOUT D'INDEX, BIEN VERIFIER LA VALEUR DES CLES DU POIDS ET DE LA TAILLE
            $index_poids  = 14;
            $index_taille = 15;
            // Suppression de la valeur du poids
            unset($line_op[$index_poids]);
            // Suppression de la valeur de la taille
            unset($line_op[$index_taille]);
        }

        $csv->writeLine($line_op);
    }

    $csv->stream($csvName);

    return;
} else {
    $smarty = new CSmartyDP();
    $smarty->assign("deblist", $deblist);
    $smarty->assign("finlist", $finlist);

    if ($operation_ids) {
        $smarty->assign('operation_ids', implode('-', $operation_ids));
    } else {
        $smarty->assign("plages", $plages);
        $smarty->assign("blocs", $blocs);
        $smarty->assign("bloc", $bloc);
    }

    $smarty->assign("operations", $operations);
    $smarty->assign("nb_interv", $nb_interv);
    $smarty->assign("type", $type);
    $smarty->assign("display_form", $display_form);
    $smarty->assign("show_constantes", $show_constantes);
    $smarty->display("vw_bloc2.tpl");
}
