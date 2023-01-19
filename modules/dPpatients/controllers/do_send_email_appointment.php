<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbPDFMerger;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Messagerie\CMailAttachments;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatientEventSentMail;
use Ox\Mediboard\SmsProviders\CMail;
use Ox\Mediboard\SmsProviders\CMailDispatcher;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CPreferences;
use Ox\Mediboard\System\CSourceSMTP;

CCanDo::checkEdit();

$patient_event_ids = (array)CView::post("patient_events", "str");
$subject           = CView::post("subject", "str");
$message           = CView::post("message", "str");

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

    $patient = $event->loadRefPatient();
    if (!$patient->email || $patient->allow_email !== '1') {
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

        $template_manager->document = $model->loadHTMLcontent($template_manager->document);

        // Make the file in which the pdf will be saved
        $file             = new CFile();
        $file->_file_path = tempnam('./tmp', $model->nom . '-' . $patient->nom . '-' . $patient->prenom) . '.pdf';

        // Make the pdf using the html generated earlier
        $html_to_pdf = new CHtmlToPDF();
        $pdf_content = $html_to_pdf->generatePDF($template_manager->document, false, new CCompteRendu(), $file, false);

        // Save the file with the pdf content
        file_put_contents($file->_file_path, $pdf_content);

        // Prepare the email attachment
        $attachement        = new CMailAttachments();
        $attachement->_file = $file;

        // Get subject
        $preference          = new CPreferences();
        $preference->user_id = $event->praticien_id;
        $preference->key     = 'send_document_subject';
        $preference->loadMatchingObject();
        $subject = $preference->value;

        // Get body
        $preference          = new CPreferences();
        $preference->user_id = $event->praticien_id;
        $preference->key     = 'send_document_body';
        $preference->loadMatchingObject();
        $body = $preference->value;

        // Prepare the email
        $mail          = new CMail();
        $mail->subject = $subject;
        $mail->message = $body;
        $mail->setReceiver($event->_ref_patient->email);
        $mail->setSource($source);
        $mail->setAttachments($attachement);

        // Send the email
        $response = CMailDispatcher::send($mail);

        // If it worked, save the state of the sending mail as "sent"
        if ($response->getStatusCode() === 1) {
            $sent                   = new CPatientEventSentMail();
            $sent->patient_event_id = $event->_id;
            $sent->datetime         = CMbDT::dateTime();
            $sent->type             = 'email';
            $sent->store();
        }
    }
}

// Just return anything to say that all went well
CApp::json([]);
