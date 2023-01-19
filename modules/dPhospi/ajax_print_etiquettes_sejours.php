<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbPDFMerger;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CModeleEtiquette;
use Ox\Mediboard\PlanningOp\CSejour;

CApp::setMemoryLimit("2024M");
CApp::setTimeLimit(600);

CCanDo::checkRead();

$object_class        = CView::post("object_class", "str");
$sejours_ids         = CView::post("sejours_ids", "str");
$modele_etiquette_id = CView::post("modele_etiquette_id", "ref class|CModeleEtiquette");
$spec_params = array(
  "str",
  "default" => array()
);
$params      = CView::post("params", $spec_params);
CView::checkin();

$sejours_ids = explode("-", $sejours_ids);

$sejour = new CSejour();

$where = array(
  "sejour_id" => CSQLDataSource::prepareIn($sejours_ids)
);

$sejours = $sejour->loadList($where, "FIELD(sejour_id, " . implode(",", $sejours_ids) . ")", null, null, null, null, null, false);

CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadFwdRef($sejours, "praticien_id");

$modele_etiquette = new CModeleEtiquette();
$modele_etiquette->load($modele_etiquette_id);
if (!$modele_etiquette->_id) {
  $where = array();
  $where['object_class'] = " = 'CSejour'";
  $where["group_id"]     = " = '" . CGroups::loadCurrent()->_id . "'";
  $modele_etiquette->loadObject($where);
  if (!$modele_etiquette->_id) {
    CAppUI::stepAjax("Aucun modèle d'étiquette configuré pour l'objet " . CAppUI::tr("CSejour"));
    CApp::rip();
  }
}
$etiquettes = array();

$uniqid = uniqid();

CSejour::massLoadNDA($sejours);
CSejour::massLoadNRA($sejours);

foreach ($sejours as $_sejour) {
  $fields = array();
  $_sejour->completeLabelFields($fields, $params);
  if (isset($params["debut_dispensation"]) && isset($params["fin_dispensation"]) && !$fields["MEDICAMENTS DISPENSES"]) {
    unset($sejours[$_sejour->_id]);
    continue;
  }
  $_modele = unserialize(serialize($modele_etiquette));
  $_modele->completeLabelFields($fields, $params);
  $_modele->replaceFields($fields);
  $etiquettes[$_sejour->_id] = tempnam("", "etiq_$uniqid");
  file_put_contents($etiquettes[$_sejour->_id], $_modele->printEtiquettes(null, 0));
}

$pdf = new CMbPDFMerger();

foreach ($etiquettes as $_etiquette) {
  $pdf->addPDF($_etiquette);
}

try {
  $pdf->merge("browser", "etiquettes.pdf");

  foreach ($etiquettes as $_etiquette) {
    unlink($_etiquette);
  }
}
catch (Exception $e) {
  CApp::rip();
}

