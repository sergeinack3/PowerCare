<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$operation_id = CView::get("operation_id", "ref class|COperation");
$type         = CView::get("type", "enum list|ANAPATH|BACTERIO default|ANAPATH");

CView::checkin();

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

$sejour    = $operation->loadRefSejour();
$praticien = $operation->loadRefPraticien();

$modele = CCompteRendu::getSpecialModel($praticien, $operation->_class, "[BON $type]", $sejour->group_id, true);

// Modèle spécial défini
if ($modele->_id) {
  CCompteRendu::streamDocForObject($modele, $operation);
}

// Impression standard
$patient = $sejour->loadRefPatient();
$patient->loadIPP();
$sejour->loadRefEtablissement();
$sejour->loadRefsAffectations();
$sejour->loadNDA();
CAffectation::massUpdateView($sejour->_ref_affectations);
$operation->loadRefPlageOp();

$patient->loadRefDossierMedical();
$patient->_poids = CConstantesMedicales::getFastConstante($patient->_id, "poids");

$dossier_medical = $patient->_ref_dossier_medical;
$dossier_medical->loadRefsAntecedents();
$antecedents = $dossier_medical->_ref_antecedents_by_type;

$options = array(
  "width"  => 220,
  "height" => 60,
  "class"  => "barcode",
);

$praticien->_rpps_base64 = CTemplateManager::getBarcodeDataUri($praticien->rpps, $options);
$praticien->_adeli_base64 = CTemplateManager::getBarcodeDataUri($praticien->adeli, $options);

if (!$praticien->_ref_signature) {
  $praticien->loadRefSignature();
}

$smarty = new CSmartyDP();

$smarty->assign("operation"  , $operation);
$smarty->assign("sejour"     , $sejour);
$smarty->assign("antecedents", $antecedents);
$smarty->assign("praticien"  , $praticien);
$smarty->assign("type"       , strtolower($type));

$content = $smarty->fetch("print_bon.tpl");

$htmltopdf = new CHtmlToPDF();
$file = new CFile();
$cr = new CCompteRendu();
$cr->_page_format = "a4";
$cr->_orientation = "portrait";

$htmltopdf->generatePDF($content, 1, $cr, $file);
