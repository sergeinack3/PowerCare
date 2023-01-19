<?php

/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CReadFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Pmsi\CRelancePMSI;

CCanDo::checkRead();

$date_min = CView::get("date_min", "date default|" . CMbDT::date('-1 day'), true);
$date_max = CView::get("date_max", "date default|now", true);
$page     = CView::get("pageOp", "num default|0");
$types    = CView::get("types", "str", true);
CView::checkin();

// Selection des salles
$listSalles        = new CSalle();
$listSalles        = $listSalles->loadGroupList();
$plage             = new CPlageOp();
$where             = [];
$where["date"]     = "BETWEEN '$date_min' AND '$date_max'";
$where["salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));
$order             = "debut";
$step              = 30;
$limit             = "$page,$step";

/** @var CPlageOp[] $plages */
$plages    = $plage->loadList($where, $order);
$operation = new COperation();

$where               = [];
$where["plageop_id"] = CSQLDataSource::prepareIn(array_keys($plages));
$where["annulee"]    = "= '0'";
$ljoin               = [];
if ($types && !in_array("", $types)) {
    $ljoin["sejour"]      = "sejour.sejour_id = operations.sejour_id";
    $where["sejour.type"] = CSQLDataSource::prepareIn($types);
}

$count      = $operation->countList($where, null, $ljoin);
$operations = $operation->loadList($where, null, $limit, null, $ljoin);

/** @var CSejour[] $sejours */
$sejours = COperation::massLoadFwdRef($operations, "sejour_id");

/** @var CPatient[] $patients */
$patients = CSejour::massLoadFwdRef($sejours, "patient_id");

CSejour::massLoadNDA($sejours);
CPatient::massLoadIPP($patients);
CSejour::massCountDocItems($sejours);
COperation::massCountDocItems($operations);
$chirurgiens = COperation::massLoadFwdRef($operations, "chir_id");
CMediusers::massLoadFwdRef($chirurgiens, "function_id");

$counter_type_documents = [];

/** @var COperation[] $operations */
foreach ($operations as $_operation) {
    // Détails de l'opérations
    $_operation->loadRefChir()->loadRefFunction();
    $_operation->loadExtCodesCCAM();
    $operation_docs = $_operation->loadRefsDocItems();

    // Détails du séjour
    $_operation->_ref_sejour               = $sejours[$_operation->sejour_id];
    $_operation->_ref_sejour->_ref_patient = $patients[$_operation->_ref_sejour->patient_id];
    $sejour_docs                           = $_operation->_ref_sejour->loadRefsDocItems();

    // count docs by category
    foreach (CRelancePMSI::$docs as $_doc) {
        $counter_type_documents[$_operation->sejour_id][$_doc]["counter"]       = 0;
        $counter_type_documents[$_operation->sejour_id][$_doc]["categorie_ids"] = [];
        $counter_type_documents[$_operation->sejour_id][$_doc]["files"]         = [];

        if ($categories_file = CAppUI::gconf("dPpmsi type_document $_doc")) {
            $counter_type_documents[$_operation->sejour_id][$_doc]["categorie_ids"] = explode("|", $categories_file);

            foreach ($operation_docs as $_operation_doc) {
                if ($_operation_doc instanceof CCompteRendu) {
                    $_operation_doc->loadFile();
                }

                $_operation_doc->canDo();

                if (
                    $_operation_doc->file_category_id
                    && in_array(
                        $_operation_doc->file_category_id,
                        $counter_type_documents[$_operation->sejour_id][$_doc]["categorie_ids"]
                    )
                ) {
                    $counter_type_documents[$_operation->sejour_id][$_doc]["counter"]++;
                    $counter_type_documents[$_operation->sejour_id][$_doc]["files"][$_operation_doc->_id] = $_operation_doc;
                }
            }

            foreach ($sejour_docs as $_sejour_doc) {
                if ($_sejour_doc instanceof CCompteRendu) {
                    $_sejour_doc->loadFile();
                }

                $_sejour_doc->canDo();

                if (
                    $_sejour_doc->file_category_id
                    && in_array(
                        $_sejour_doc->file_category_id,
                        $counter_type_documents[$_operation->sejour_id][$_doc]["categorie_ids"]
                    )
                ) {
                    $counter_type_documents[$_operation->sejour_id][$_doc]["counter"]++;
                    $counter_type_documents[$_operation->sejour_id][$_doc]["files"][$_sejour_doc->_id] = $_sejour_doc;
                }
            }
        }
    }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("operations", $operations);
$smarty->assign('notReadFiles', CReadFile::getUnread($operations));
$smarty->assign("pageOp", $page);
$smarty->assign("countOp", $count);
$smarty->assign("step", $step);
$smarty->assign("counter_type_documents", $counter_type_documents);
$smarty->display("current_dossiers/inc_current_operations");
