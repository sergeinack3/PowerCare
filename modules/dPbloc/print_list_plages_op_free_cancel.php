<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();
$datetime_min  = CView::get("_datetime_min", "dateTime", true);
$datetime_max  = CView::get("_datetime_max", "dateTime", true);
$praticien_id  = CView::get("_prat_id", 'ref class|CMediusers', true);
$specialite_id = CView::get("_specialite", 'ref class|CFunctions', true);
$bloc_ids      = CView::get("_bloc_id", 'str', true); /* Can be an array of bloc ids */
$salle_ids     = CView::get("_salle_id", 'str', true); /* Can be an array  of salle ids */
$_page_break   = CView::get('_page_break', 'bool default|0', true);
$export        = CView::get('export', 'bool default|0');
CView::checkin();

$group = CGroups::loadCurrent();

if (is_array($bloc_ids)) {
  CMbArray::removeValue("", $bloc_ids);
  CMbArray::removeValue("0", $bloc_ids);
}

if (is_array($salle_ids)) {
  CMbArray::removeValue("", $salle_ids);
  CMbArray::removeValue("0", $salle_ids);
}

$date_min = CMbDT::date($datetime_min);
$date_max = CMbDT::date($datetime_max);

$where           = array();
$where["status"] = " NOT IN ('occupied')";
$where["date"]   = " BETWEEN '$date_min' AND '$date_max'";

if ($praticien_id) {

  $praticien = new CMediusers();
  $praticien->load($praticien_id);

  if ($praticien->isAnesth()) {
    $where["anesth_id"] = " = '$praticien->_id'";
  }
  else {
    $where["prat_id"] = " = '$praticien->_id'";
  }
}

if ($specialite_id) {
  $where["spec_id"] = " = '$specialite_id'";
}

// En fonction des blocs et salles
$listBlocs = $group->loadBlocs(PERM_READ, false, "nom", array("actif" => "= '1'"));

$whereSalle                       = array();
$whereSalle["sallesbloc.bloc_id"] = CSQLDataSource::prepareIn(count($bloc_ids) ? $bloc_ids : array_keys($listBlocs));

if ($salle_ids && !in_array(0, $salle_ids)) {
  $whereSalle["sallesbloc.salle_id"] = CSQLDataSource::prepareIn($salle_ids);
}

$salle      = new CSalle();
$listSalles = $salle->loadListWithPerms(PERM_READ, $whereSalle);

$where["salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));

$order = "date, debut";

// Liste des plage opératoires libres et annulées
$plage  = new CPlageOp();
$plages = $plage->loadList($where, $order);

CStoredObject::massLoadFwdRef($plages, "salle_id");
CStoredObject::massLoadFwdRef($plages, "chir_id");
CStoredObject::massLoadFwdRef($plages, "anesth_id");
CStoredObject::massLoadFwdRef($plages, "spec_id");

foreach ($plages as $_plage) {
  $_plage->loadRefSalle()->loadRefBloc();
  $_plage->loadRefChir();
  $_plage->loadRefAnesth();
  $_plage->loadRefSpec();
}

if ($export) {
  $file = new CCSVFile();

  $file->writeLine(
    array(
      CAppUI::tr("COperation-_bloc_id-court"),
      CAppUI::tr("CPlageOp-salle_id"),
      CAppUI::tr("common-Date"),
      CAppUI::tr("common-Start"),
      CAppUI::tr("end"),
      CAppUI::tr("common-Practitioner"),
      CAppUI::tr("CPlageOp-anesth_id"),
      CAppUI::tr("common-Status")
    )
  );

  foreach ($plages as $_plage) {
    $file->writeLine(
      array(
        $_plage->_ref_salle->_ref_bloc->_view,
        $_plage->_ref_salle->nom,
        $_plage->date,
        $_plage->debut,
        $_plage->fin,
        $_plage->_ref_chir->_view,
        $_plage->_ref_anesth->_view,
        CAppUI::tr("CPlageOp.status.$_plage->status")
      )
    );
  }

  $file->stream(CAppUI::tr('CPlageOp-Free vacations and canceled from %s to %s', CMbDT::format($datetime_min, CAppUI::conf("datetime")), CMbDT::format($datetime_max, CAppUI::conf("datetime"))));
  CApp::rip();
}

$smarty = new CSmartyDP;
$smarty->assign("datetime_min", $datetime_min);
$smarty->assign("datetime_max", $datetime_max);
$smarty->assign("plages"      , $plages);
$smarty->assign("_page_break" , $_page_break);
$smarty->display("print_list_plages_op_free_cancel.tpl");
