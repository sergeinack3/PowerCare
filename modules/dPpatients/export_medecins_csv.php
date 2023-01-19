<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CPDOMySQLDataSource;
use Ox\Core\CRequest;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CCSVImportCorrespondantMedical;
use Ox\Mediboard\Patients\CMedecin;

CCanDo::checkEdit();

$only_with_emails    = CView::get("emails", "bool default|0");
$medecin_nom         = CView::get("nom", "str", true);
$medecin_prenom      = CView::get("prenom", "str", true);
$medecin_function_id = CView::get("function_id", "ref class|CFunctions", true);
$medecin_cp          = CView::get("cp", "str", true);
$medecin_ville       = CView::get("ville", "str", true);
$medecin_type        = CView::get("type", "str default|medecin", true);
$medecin_disciplines = CView::get("disciplines", "str", true);
$actif               = CView::get("actif", "enum list|0|1|2 default|1", true);
CView::checkin();

$csv = new CCSVFile();

$medecin = new CMedecin();
$ds      = $medecin->getDS();

$curr_user = CMediusers::get();

$line = CCSVImportCorrespondantMedical::$FIELDS;

$csv->writeLine($line);

$where = array();

if ($only_with_emails) {
  $where[] = "(email IS NOT NULL OR email_apicrypt IS NOT NULL) AND email <> ''";
}
else {
  if ($medecin_nom) {
    $medecin_nom = stripslashes($medecin_nom);
    $where[]     = "nom LIKE '$medecin_nom%'";
  }

  if ($medecin_prenom) {
    $where[] = "prenom LIKE '%$medecin_prenom%'";
  }

  if ($medecin_disciplines) {
    $where[] = "disciplines LIKE '%$medecin_disciplines%'";
  }

  if ($medecin_function_id) {
    $where[] = "function_id = '$medecin_function_id'";
  }

  if ($medecin_cp && $medecin_cp != "00") {
    $cps = preg_split("/\s*[\s\|,]\s*/", $medecin_cp);

    CMbArray::removeValue("", $cps);

    $where_cp = array();
    foreach ($cps as $cp) {
      $where_cp[] = "cp LIKE '" . $cp . "%'";
    }

    $where[] = implode(" OR ", $where_cp);
  }

  if ($medecin_ville) {
    $where[] = "ville LIKE '%$medecin_ville%'";
  }

  if ($medecin_type) {
    $where["type"] = "= '$medecin_type'";
  }

  if ($actif !== '2') {
    $where["actif"] = "= '$actif'";
  }
}

$order    = "nom, prenom";

$request = new CRequest();
if (!$curr_user->isAdmin()) {
  if (CAppUI::isCabinet()) {
    $where["function_id"] = "= '" . $curr_user->function_id . "'";
  }
  elseif (CAppUI::isGroup()) {
    $where["group_id"] = "= '" . $curr_user->loadRefFunction()->group_id . "'";
  }
}
$request->addWhere($where);
$request->addOrder($order);

// Disable query buffer, to save memory
if ($ds instanceof CPDOMySQLDataSource) {
  $ds->link->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
}

$query = $request->makeSelect($medecin);
$res   = $ds->exec($query);

while ($data = $ds->fetchAssoc($res)) {
  $_new_data = array();
  foreach ($line as $_field) {
    $_new_data[] = $data[$_field];
  }

  $csv->writeLine($_new_data);
}

$name = "Correspondants médicaux - " . CMbDT::format(CMbDT::dateTime(), "%d-%m-%Y %H:%M:%S");
$csv->stream($name, true);
