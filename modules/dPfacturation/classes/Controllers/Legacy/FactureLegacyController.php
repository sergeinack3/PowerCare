<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation\Controllers\Legacy;

use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CView;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\FacturePrintService;
use Ox\Mediboard\Files\MailReceiverService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceSMTP;
use phpmailerException;

class FactureLegacyController extends CLegacyController
{
    public function printFacture(): void
    {
        $this->checkPermRead();
        $facture_class = CView::get('facture_class', 'str notNull', true);
        $facture_id    = CView::get('facture_id', 'ref meta|facture_class notNull', true);

        CView::checkin();

        /* @var CFactureCabinet $facture */
        $facture = new $facture_class();
        $facture->load($facture_id);

        $service = new FacturePrintService($facture);
        $file    = $service->generatePdfFile(false, true);
        $file->store();
        $file->streamFile();
    }

    public function viewSendFactureByMail(): void
    {
        $this->checkPermRead();

        $facture_class = CView::get('facture_class', 'str notNull');
        $facture_id    = CView::get('facture_id', 'ref meta|facture_class notNull');

        CView::checkin();

        /* @var CFactureCabinet $facture */
        $facture = new $facture_class();
        $facture->load($facture_id);

        $patient = $facture->loadRefPatient();

        $receivers = (new MailReceiverService($patient))->getReceivers(
            MailReceiverService::ADDRESS_TYPE_MAIL,
            MailReceiverService::RECEIVER_TYPE_CASUAL
        );

        $facture->loadRefsConsultation();
        $facture->_ref_first_consult->loadRefPraticien();
        $subject = CAppUI::tr(
            'CFacture-msg-mail_subject',
            CMbDT::format($facture->_ref_first_consult->_date, '%d/%m/%Y')
        );
        $body = str_replace('<br />', '', CAppUI::tr(
            'CFacture-msg-mail_body',
            $facture->_ref_first_consult->_ref_praticien->_view,
            CMbDT::format($facture->_ref_first_consult->_date, '%d/%m/%Y')
        ));

        $this->renderSmarty('view_send_facture_mail', [
            'facture'   => $facture,
            'subject'   => $subject,
            'body'      => $body,
            'patient'   => $patient,
            'receivers' => $receivers,
        ]);
    }

    public function sendFactureByMail(): void
    {
        $this->checkPermRead();

        $facture_class = CView::post('facture_class', 'str notNull');
        $facture_id    = CView::post('facture_id', 'ref meta|facture_class notNull');
        $receivers     = json_decode(utf8_encode(stripslashes(CView::post('receivers', 'str'))), true);
        $subject       = CView::post('subject', 'str');
        $body          = CView::post('body', 'str');

        CView::checkin();

        /* @var CFactureCabinet $facture */
        $facture = new $facture_class();
        $facture->load($facture_id);

        $service = new FacturePrintService($facture);
        $file    = $service->generatePdfFile(false, false);
        $file->store();

        $user = CMediusers::get();

        /** @var CSourceSMTP $source  */
        $source = CExchangeSource::get("mediuser-" . $user->_id, CSourceSMTP::TYPE);

        $source->setSenderNameFromUser($user, true);
        $source->init();

        $source->setSubject($subject);
        $source->setBody(nl2br($body));

        $file_name = str_replace(['/', '  ', ' n°'], ['', ' ', ''], $file->file_name);

        $source->addAttachment($file->_file_path, $file_name);

        $cci_receivers = CAppUI::loadPref('cciReceivers', $user->_id);
        foreach ($receivers as $receiver) {
            if (array_key_exists('email', $receiver) && $receiver['email'] != '') {
                if ($cci_receivers) {
                    $source->addBcc($receiver['email'], $receiver['name']);
                } else {
                    $source->setRecipient($receiver['email'], $receiver['name']);
                }
            }
        }

        $mail = $source->createUserMail($user->_id, $facture);
        if ($source->asynchronous == '0') {
            try {
                $source->send();
                if ($mail) {
                    $mail->sent = 1;
                    $mail->store();
                }

                CAppUI::displayAjaxMsg('CFacture-msg-sent_by_mail');
            } catch (phpmailerException $e) {
                if ($mail) {
                    $mail->delete();
                }
                CAppUI::displayAjaxMsg($e->errorMessage(), UI_MSG_WARNING);
            } catch (CMbException $e) {
                if ($mail) {
                    $mail->delete();
                }
                $e->stepAjax();
            }
        } else {
            CAppUI::displayAjaxMsg('CUsermail-to_send');
        }
    }
}
