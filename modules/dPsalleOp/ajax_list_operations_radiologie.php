<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Récuperation du précédent filtre
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;

$step  = 30;

$operation    = new COperation();
$_date_min = CView::get("_date_min", "date", true);
$_date_max = CView::get("_date_max", "date", true);
$chir_id   = CView::get("chir_id", "ref class|CMediusers", true);
$protocole_id   = CView::get("protocole_id", "ref class|CProtocole");
$salle_ids  = CView::get("salle_ids", "str", true);
$ampli_ids  = CView::get("ampli_ids", "str", true);
$ccam_codes  = CView::get("ccam_codes", "str", true);

$page          = intval(CView::get('page', 'num default|0', true));
$order_way     = CView::get("order_way", "enum list|ASC|DESC default|ASC", true);
$order_col     = CView::get("order_col", "enum list|_IPP|_taille|_poids|dose_recue_scopie|temps_rayons_x|dose_recue_graphie", true);
$export    = CView::get("export", "str");

CView::checkin();

$group_id = CGroups::loadCurrent()->_id;

$ds = CSQLDataSource::get('std');

if (is_array($salle_ids)) {
    $salle_ids = array_filter($salle_ids);
}
if (is_array($ampli_ids)) {
    $ampli_ids = array_filter($ampli_ids);
}

if ($ccam_codes) {
    $ccam_codes = array_filter(explode('|', $ccam_codes));
}

$where = $ljoin = [];
if ($_date_min && $_date_max) {
    $where["operations.date"] = $ds->prepare("BETWEEN ?1 AND ?2", $_date_min, $_date_max);
}
if ($chir_id) {
    $where["operations.chir_id"] = $ds->prepare("= ?", $chir_id);
}
if ($salle_ids && count($salle_ids)) {
    $where["operations.salle_id"] = $ds->prepareIn($salle_ids);
}
if ($ampli_ids && count($ampli_ids)) {
    $where["operations.ampli_id"] = $ds->prepareIn($ampli_ids);
}
if ($protocole_id) {
    $where["operations.protocole_id"] = $ds->prepare("= ?", $protocole_id);
}

$ljoin["sejour"] = "sejour.sejour_id = operations.sejour_id";
$where["sejour.group_id"] = $ds->prepare("= ?", $group_id);

if ($ccam_codes && count($ccam_codes)) {
    $ljoin["acte_ccam"] = "acte_ccam.object_id = operations.operation_id AND acte_ccam.object_class = 'COperation'";
    $where["acte_ccam.code_acte"] = $ds->prepareIn($ccam_codes);
}

$order = $limit = null;

if ($order_col) {
    $order = "$order_col $order_way";
}

if (!$export && is_int($page)) {
    $limit = "$page, $step";
}

$operations = $operation->loadList($where, $order, $limit, "operations.operation_id", $ljoin);
$count_operations = $operation->countList($where, null, $ljoin);


/** @var CSejour[] $sejours */
$sejours = CMbObject::massLoadFwdRef($operations, "sejour_id");
CMbObject::massLoadFwdRef($operations, "chir_id");
CMbObject::massLoadFwdRef($operations, "ampli_id");
/** @var CPatient[] $patients */
$patients = CMbObject::massLoadFwdRef($sejours, "patient_id");
CPatient::massLoadIPP($patients);
foreach ($operations as $_operation) {
    $_operation->loadRefChir();
    $_operation->loadRefAmpli();
    $_sejour = $_operation->loadRefSejour();
    $_patient = $_operation->loadRefPatient();

    $_patient->loadIPP();
    $_patient->loadRefLatestConstantes(null, array("poids", "taille"));
    $const_med = $_patient->_ref_constantes_medicales;

    if ($const_med) {
        $_patient->_poids  = $const_med->poids;
        $_patient->_taille = $const_med->taille;
    }
}

if ($export == "csv") {
    $csv = new CCSVFile();

    $line = [
        CAppUI::tr("COperation"),
        CAppUI::tr("COperation-chir_id"),
        CAppUI::tr("CSejour"),
        CAppUI::tr("CAmpli"),
        CAppUI::tr("CPatient.IPP"),
        CAppUI::tr("CPatient-_poids"),
        CAppUI::tr("CPatient-_taille"),
        CAppUI::tr("COperation-dose_recue_scopie"),
        CAppUI::tr("COperation-temps_rayons_x"),
        CAppUI::tr("COperation-dose_recue_graphie"),
        CAppUI::tr("COperation-pds"),
        CAppUI::tr("COperation-kerma-court"),
    ];
    $csv->writeLine($line);

    foreach ($operations as $_operation) {
        $line = [];
        $line[] = $_operation->libelle;
        $line[] = $_operation->_ref_chir->_view;
        $line[] = $_operation->_ref_sejour->_id ? $_operation->_ref_sejour->_view : CAppUI::tr("CSejour.none");
        $line[] = $_operation->_ref_ampli->_view;
        $line[] = $_operation->_ref_patient->_IPP;
        $line[] = $_operation->_ref_patient->_poids;
        $line[] = $_operation->_ref_patient->_taille;
        $line[] = $_operation->dose_recue_scopie." ".CAppUI::tr("COperation.unite_rayons_x.$_operation->unite_rayons_x");
        $line[] = $_operation->temps_rayons_x;
        $line[] = $_operation->dose_recue_graphie." ".CAppUI::tr("COperation.unite_rayons_x.$_operation->unite_rayons_x");
        $line[] = $_operation->pds." ".CAppUI::tr("COperation.unite_pds.$_operation->unite_pds");
        $line[] = $_operation->kerma." ".CAppUI::tr("COperation.unite_kerma.mGy");

        $csv->writeLine($line);
    }

    $csv->stream(CAppUI::tr("COperation|pl"));

    CApp::rip();
}

$smarty = new CSmartyDP();

$smarty->assign("operation", $operation);
$smarty->assign("operations", $operations);
$smarty->assign("total_interv", $count_operations);
$smarty->assign("page", $page);
$smarty->assign("order_way", $order_way);
$smarty->assign("order_col", $order_col);

$smarty->display("inc_list_operations_radiologie");
