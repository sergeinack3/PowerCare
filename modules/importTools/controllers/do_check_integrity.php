<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\Import\CMbObjectExport;
use Ox\Core\CRequest;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$group_id          = CView::post("group_id", "ref class|CGroups notNull");
$class_name        = CView::post("class_name", "str");
$additionals_prats = CView::post("additionnals_prats", "str");
$directory         = CView::post("directory", "str");

CView::checkin();

CView::enforceSlave();

CApp::setMemoryLimit("2048M");
CApp::setTimeLimit(600);

$mediuser       = new CMediusers();
$praticiens_ids = $mediuser->getGroupIds();
list($patients,) = CMbObjectExport::getPatientsToExport($praticiens_ids);
$patient_ids = CMbArray::pluck($patients, 'patient_id');

switch ($class_name) {
  case "CSejour":
    $sejour = new CSejour();
    $ds     = $sejour->getDS();

    $where = array(
      "group_id"     => $ds->prepare('= ?', $group_id),
      "praticien_id" => $ds->prepareIn($praticiens_ids),
      "patient_id"   => $ds->prepareIn($patient_ids),
    );
    $ids   = $sejour->loadIds($where);
    break;
  case "CConsultation":
    $ds      = $mediuser->getDS();
    $request = new CRequest();
    $request->addSelect("C.consultation_id");
    $request->addTable(array("consultation C", "plageconsult P"));
    $request->addWhere(
      array(
        "P.chir_id"         => $ds->prepareIn($praticiens_ids),
        "C.plageconsult_id" => "= P.plageconsult_id",
        'C.patient_id'      => $ds->prepareIn($patient_ids),
      )
    );
    $consult_ids = $ds->loadList($request->makeSelect());
    $ids         = CMbArray::pluck($consult_ids, "consultation_id");
    break;
  case "COperation":
    $op = new COperation();
    $ds = $op->getDS();

    $request = new CRequest();
    $request->addSelect("O.operation_id");
    $request->addTable(array('operations O', 'sejour S'));
    $request->addWhere(
      array(
        "O.sejour_id"    => "= S.sejour_id",
        "O.chir_id"      => $ds->prepareIn($praticiens_ids),
        "S.patient_id"   => $ds->prepareIn($patient_ids),
        "S.praticien_id" => $ds->prepareIn($praticiens_ids),
        "S.group_id"     => $ds->prepare("= ?", $group_id),
      )
    );

    $op_ids = $ds->loadList($request->makeSelect());
    $ids    = CMbArray::pluck($op_ids, "consultation_id");
    break;
  case "CCompteRendu":
    $cr = new CCompteRendu();
    $ds = $cr->getDS();

    $where = array(
      "author_id" => $ds->prepareIn($praticiens_ids),
    );

    $ids = $cr->loadIds($where);
    break;
  case "CFile":
    $file = new CFile();
    $ds   = $file->getDS();

    if ($additionals_prats) {
      $prats_ids = explode('|', $additionals_prats);
      array_merge($praticiens_ids, $prats_ids);
    }

    $where = array(
      "author_id" => $ds->prepareIn($praticiens_ids),
    );

    $ids = $file->loadIds($where);
    break;
  default:
    $ids = array();
}

echo count($ids);

$sql_ids = array();
foreach ($ids as $_id) {
  $sql_ids[$_id] = "";
}

$root_dir = CAppUI::gconf("importTools export root_path", $group_id);

$file_path = rtrim($root_dir, '/\\') . "/" . $directory . '/export.integrity';

$file_stats = file_get_contents($file_path);
$stats      = json_decode($file_stats, true);

if (!isset($stats['SQL'])) {
  $stats['SQL'] = array();
}

$stats['SQL'][$class_name] = $sql_ids;
$json_stats                = json_encode($stats);
file_put_contents($file_path, $json_stats);
