<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Medicament\CMedicament;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Imprime les documents reliés à un objet
 */
CCanDo::checkRead();

$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", "ref class|$object_class");

CView::checkin();

$object = new $object_class;
/** @var $object CMbObject */
$object->load($object_id);

if ($object instanceof CSejour || $object instanceof COperation || $object instanceof CConsultation) {
  CAccessMedicalData::logAccess($object);
}

$object->loadRefsDocs();
$object->loadRefsFiles();

// Pour un séjour d'urgences, on ajoute les documents de la consultation
if ($object instanceof CSejour && in_array($object->type, CSejour::getTypesSejoursUrgence($object->praticien_id))) {
  $object->loadRefsConsultations();
  $consult_atu = $object->_ref_consult_atu;

  if ($consult_atu->_id) {
    $consult_atu->loadRefsDocs();
    $consult_atu->loadRefsFiles();

    $object->_ref_documents = array_merge($object->_ref_documents, $consult_atu->_ref_documents);
    $object->_ref_files     = array_merge($object->_ref_files    , $consult_atu->_ref_files);
  }
}

// On retire les documents annulés
// Quantité par défaut reprise du modèle initial
CStoredObject::massLoadFwdRef($object->_ref_documents, "modele_id");
foreach ($object->_ref_documents as $key => $_doc) {
  if ($_doc->annule) {
    unset($object->_ref_documents[$key]);
    continue;
  }

  $_doc->loadModele();
  $_doc->getNbPrint();
}

// On retire les fichiers qui ne sont pas au format pdf ou annulés
foreach ($object->_ref_files as $key => $_file) {
  if ((strpos($_file->file_type, "pdf") === false) || $_file->annule) {
    unset($object->_ref_files[$key]);
  }
}

// Pour une consultation d'urgence, on ajoute la prescription de sortie
if (CModule::getActive("dPurgences")
    && CModule::getActive("dPprescription")
    && CModule::getActive("dPmedicament")
    && CMedicament::getBase() != "besco"
    && $object instanceof CConsultation
    && $object->sejour_id
) {
  $object->loadRefSejour();
  if (in_array($object->_ref_sejour->type, CSejour::getTypesSejoursUrgence($object->_ref_sejour->praticien_id))) {
    $object->_ref_sejour->loadRefsPrescriptions();
  }
}

$smarty = new CSmartyDP();

$smarty->assign("object", $object);

$smarty->display("print_select_docs");
