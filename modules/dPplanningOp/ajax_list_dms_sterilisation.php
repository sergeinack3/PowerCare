<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CMaterielOperatoire;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();

$operation = new COperation();

$date = CView::get("date", "date default|" . CMbDT::date());
$csv  = CView::get("csv", "bool default|0");

$operation->_prepa_dt_min            = CView::get("_prepa_dt_min", ["dateTime", "default" => "$date 00:00:00"], true);
$operation->_prepa_dt_max            = CView::get("_prepa_dt_max", ["dateTime", "default" => "$date 23:59:59"], true);
$operation->_prepa_chir_id           = CView::get("_prepa_chir_id", "ref class|CMediusers", true);
$operation->_prepa_spec_id           = CView::get("_prepa_spec_id", "ref class|CFunctions", true);
$operation->_prepa_bloc_id           = CView::get("_prepa_bloc_id", "ref class|CBlocOperatoire", true);
$operation->_prepa_salle_id          = CView::get("_prepa_salle_id", "ref class|CSalle", true);
$operation->_prepa_urgence           = CView::get("_prepa_urgence", "bool default|0", true);
$operation->_prepa_libelle           = CView::get("_prepa_libelle", "str", true);
$operation->_prepa_libelle_prot      = CView::get("_prepa_libelle_prot", "str", true);
$spec_prepa_type_intervention        = [
  "enum",
  "list"    => "hors_plage|avec_plage|tous",
  "default" => "tous"
];
$operation->_prepa_type_intervention = CView::get("_prepa_type_intervention", $spec_prepa_type_intervention, true);

CView::checkin();

$group = CGroups::loadCurrent();

$ds = $operation->getDS();

$operation_time_sql = $ds->prepare(
  "CONCAT(operations.date, ' ', operations.time_operation) BETWEEN ?1 AND ?2",
  $operation->_prepa_dt_min,
  $operation->_prepa_dt_max
);

$where = [
  "sejour.group_id"                            => "= '$group->_id'",
  $operation_time_sql,
  "operations.urgence"                         => $ds->prepare("= ?", $operation->_prepa_urgence),
  "materiel_operatoire.materiel_operatoire_id" => "IS NOT NULL",
];

$ljoin = [
  "sejour"              => "sejour.sejour_id = operations.sejour_id",
  "materiel_operatoire" => "materiel_operatoire.operation_id = operations.operation_id"
];

if ($operation->_prepa_chir_id) {
  $where["operations.chir_id"] = $ds->prepare("= ?", $operation->_prepa_chir_id);
}
elseif ($operation->_prepa_spec_id) {
  $user  = new CMediusers();
  $chirs = $user->loadPraticiens(PERM_READ, $operation->_prepa_spec_id);

  $where["operations.chir_id"] = CSQLDataSource::prepareIn(array_keys($chirs));
}

if ($operation->_prepa_salle_id) {
  $where["operations.salle_id"] = $ds->prepare("= ?", $operation->_prepa_salle_id);
}
elseif ($operation->_prepa_bloc_id) {
  $bloc = CBlocOperatoire::findOrNew($operation->_prepa_bloc_id);

  $salles = $bloc->loadRefsSalles();

  if (count($salles)) {
    $where["operations.salle_id"] = CSQLDataSource::prepareIn(CMbArray::pluck($salles, "_id"));
  }
}

if ($operation->_prepa_libelle) {
  $where[] = $ds->prepareLikeMulti(addslashes($operation->_prepa_libelle), "libelle");
}

switch ($operation->_prepa_type_intervention) {
  case "avec_plage":
    $where["operations.plageop_id"] = "IS NOT NULL";
    break;
  case "hors_plage":
    $where["operations.plageop_id"] = "IS NULL";
    break;
  default:
    /* Do nothing */
    break;
}

$operations_ids = $operation->loadIds($where, null, null, "operations.operation_id", $ljoin);

$materiel_op = new CMaterielOperatoire();

$where = [
  "operation_id"              => CSQLDataSource::prepareIn($operations_ids),
  "materiel_operatoire.dm_id" => "IS NOT NULL",
  "type_usage"                => "= 'sterilisable'"
];

$ljoin = [
  "dm" => "dm.dm_id = materiel_operatoire.dm_id"
];

$materiels_op = $materiel_op->loadList($where, null, null, null, $ljoin);

$dms = [];

foreach ($materiels_op as $_materiel_op) {
  if (!isset($dms[$_materiel_op->dm_id])) {
    $dms[$_materiel_op->dm_id] = [
      "dm"       => $_materiel_op->loadRefDM(),
      "quantite" => 0
    ];
  }

  $dms[$_materiel_op->dm_id]["quantite"] += $_materiel_op->qte_prevue;
}

CMbArray::pluckSort($dms, SORT_ASC, "dm", "_ref_product", "name");

if ($csv) {
  $csv = new CCSVFile();

  $csv->writeLine(
    [
      CAppUI::tr("CMaterielOperatoire-dm_id"),
      CAppUI::tr("CMaterielOperatoire-qte_prevue"),
    ]
  );

  foreach ($dms as $_dm) {
    $csv->writeLine(
      [
        $_dm["dm"]->_ref_product->name,
        $_dm["quantite"]
      ]
    );
  }

  $csv->stream(CAppUI::tr("planningOp-preparation_sterilisations"));

  return;
}

$smarty = new CSmartyDP();

$smarty->assign("dms", $dms);

$smarty->display("inc_list_dms_sterilisation");
