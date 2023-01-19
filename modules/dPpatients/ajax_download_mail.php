<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbPDFMerger;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatientEventSentMail;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceSMTP;

CCanDo::checkEdit();

$patient_event_ids = (array)CView::get("patient_events", "str");

CView::checkin();

$pdf_merger = new CMbPDFMerger();
$pdfs       = 0;

// Go through patient events ids
foreach ($patient_event_ids as $_patient_event) {
  // Get the event and check some stuff (practitioner, mail ...)
  $event = CEvenementPatient::findOrFail($_patient_event);

  if (!$event->praticien_id) {
    continue;
  }

  $event->loadRefPatient();
  if (!$event->_ref_patient->cp || !$event->_ref_patient->ville) {
    continue;
  }

  $event->loadRefTypeEvenementPatient();

  // Get the SMTP source
  $source = CExchangeSource::get("mediuser-" . $event->praticien_id, CSourceSMTP::TYPE, true, null, false);

  if ($event->_ref_type_evenement_patient) {
    // Get the model related to the event type
    $model = CCompteRendu::findOrFail($event->_ref_type_evenement_patient->mailing_model_id);
    $model->loadContent();

    // Use the template manager to generate the document and fill the template with the event object
    $template_manager           = new CTemplateManager();
    $template_manager->document = $model->generateDocFromModel(null, $model->header_id, $model->footer_id);

    $event->fillTemplate($template_manager);

    // Make the document and return an html page
    $template_manager->renderDocument($template_manager->document);

      $template_manager->document = $model->loadHTMLcontent(
          $template_manager->document,
          null,
          [
              $model->margin_top,
              $model->margin_right,
              $model->margin_bottom,
              $model->margin_left,
          ]
      );

    // Make the file in which the pdf will be saved
    $file             = new CFile();
    $file->_file_path = tempnam('./tmp', 'attachment') . '.pdf';

    // Make the pdf using the html generated earlier
    $html_to_pdf = new CHtmlToPDF();
    $pdf_content = $html_to_pdf->generatePDF($template_manager->document, false, new CCompteRendu(), $file, false);

    // Save the file with the pdf content
    $return = file_put_contents($file->_file_path, $pdf_content);

    $sent                   = new CPatientEventSentMail();
    $sent->patient_event_id = $event->_id;
    $sent->datetime         = CMbDT::dateTime();
    $sent->type             = 'postal';
    $sent->store();

    $pdf_merger->addPDF($file->_file_path);
    unlink($file->_file_path);
    $pdfs++;
  }
}

if ($pdfs > 0) {
  $pdf_merger->merge();
}
