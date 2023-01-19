<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\CompteRendu\CWkhtmlToPDF;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();
$dossier_anesth_id = CView::get("dossier_anesth_id", "ref class|CConsultAnesth", true);
CView::checkin();

$dossier_anesth = new CConsultAnesth();
$dossier_anesth->load($dossier_anesth_id);

$consultation = $dossier_anesth->loadRefConsultation();
$consultation->_ref_consult_anesth = $dossier_anesth;
$patient = $consultation->loadRefPatient();

$sejour = $dossier_anesth->loadRefSejour();

if (CModule::getActive("maternite") && $consultation->grossesse_id && $consultation->_ref_consult_anesth->_id
    && !$consultation->_ref_consult_anesth->operation_id
) {
  $grossesse = $consultation->loadRefGrossesse();
  $sejours = $grossesse->loadRefsSejours();
  $sejour = end($sejours);

  if (!$sejour) {
    $sejour = new CSejour();
  }
}

$urls = array(
  // Le patient
  "patient" => array(
    "m"                 => "system",
    "dialog"            => "httpreq_vw_complete_object",
    "object_guid"       => $patient->_guid,
    "not-printable"     => 1,
  ),

  // Constantes
  "cstes" => array(
    "m"                 => "patients",
    "dialog"            => "ajax_display_constantes",
    "patient_id"        => $patient->_id,
    "context_guid"      => $consultation->_guid,
  ),
  // Exam. Clinique
  "exam_clinique" => array(
    "m"                 => "cabinet",
    "dialog"            => "ajax_vw_examens_anesth",
    "dossier_anesth_id" => $dossier_anesth->_id,
  ),

  // Intubation
  "intubation" => array(
    "m"                 => "cabinet",
    "dialog"            => "ajax_vw_intubation",
    "dossier_anesth_id" => $dossier_anesth->_id,
  ),

  // Exam. Comp.
  "exam_comp" => array(
    "m"                 => "cabinet",
    "dialog"            => "ajax_vw_examens_complementaire",
    "dossier_anesth_id" => $dossier_anesth->_id,
  ),

  // Infos. Anesth.
  "info_anesth" => array(
    "m"                 => "cabinet",
    "dialog"            => "httpreq_vw_choix_anesth",
    "selConsult"        => $consultation->_id,
    "dossier_anesth_id" => $dossier_anesth->_id,
  ),

  // Facteurs de risque
  "facteurs_risque" => array(
    "m"                 => "cabinet",
    "dialog"            => "httpreq_vw_facteurs_risque",
    "dossier_anesth_id" => $dossier_anesth->_id,
    "sejour_id"         => $sejour->_id,
  )
);

if (!CAppUI::gconf("dPcabinet CConsultAnesth show_facteurs_risque")) {
  unset($urls["facteurs_risque"]);
}

header("Pragma: ");
header("Cache-Control: ");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
// END extra headers to resolve IE caching bug
header("MIME-Version: 1.0");
header('Content-type: application/pdf');
header("Content-disposition: inline; filename=\"Consultation préanesthésique\"");

echo CWkhtmlToPDF::makePDF($dossier_anesth, "Consultation préanesthésique " . CMbDT::dateTime(), $urls, "A4", "Portrait", "print");
