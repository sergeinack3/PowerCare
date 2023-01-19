<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Medimail\CMedimailAttachment;
use Ox\Mediboard\Medimail\CMedimailMessage;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\Entities\MessagerieEntity;
use Ox\Mediboard\Messagerie\Entities\ViewModels\MessagerieAttachmentLinkView;
use Ox\Mediboard\Messagerie\Entities\ViewModels\MessagerieMailLinkView;
use Ox\Mediboard\Messagerie\Exceptions\MessagerieLinkException;
use Ox\Mediboard\Messagerie\Services\MessagerieLinkService;
use Ox\Mediboard\Messagerie\Services\MessagerieLinkPatientService;
use Ox\Mediboard\Mssante\CMSSanteMail;
use Ox\Mediboard\Mssante\CMSSanteMailAttachment;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Link controller
 * Only work for Medimail & Mailiz (ex Mssante)
 */
class MessagerieLinkController extends CLegacyController
{
    // Search offset
    public const DEFAULT_SEARCH_START = '0';
    public const DEFAULT_SEARCH_LIMIT = '2';

    /**
     * Displays the ability to link an email to a context
     *
     * @throws Exception
     * @throws MessagerieLinkException
     */
    public function viewLink(): void
    {
        $this->checkPermEdit();

        $mail_type = CView::get('mail_type', 'enum list|CMedimailMessage|CMSSanteMail');
        $mail_id   = CView::getRefCheckEdit('mail_id', 'ref class|' . $mail_type);

        CView::checkin();

        $mail                  = CStoredObject::loadFromGuid("$mail_type-$mail_id");
        $mail_view_attachments = [];
        $categories            = (new CFilesCategory())->loadListWithPerms();

        switch (true) {
            case (CModule::getActive('medimail') && $mail instanceof CMedimailMessage):
                // Transforms attachments into a readable form
                foreach ($mail->loadAttachments() as $attachment) {
                    $attachment_file = $attachment->loadFile();
                    $attachment_name = explode('.', $attachment->file_name);

                    $view_attachment = MessagerieAttachmentLinkView::createFromData(
                        $attachment_name[0],
                        $attachment_name[1],
                        $attachment->_id,
                        $attachment->_class,
                        $attachment_file
                    );

                    $mail_view_attachments[] = $view_attachment;
                }

                // Transform mail into a readable form
                $mail_view = MessagerieMailLinkView::createFromData($mail->title, $mail->_id, $mail->_class);

                break;
            case (CModule::getActive('mssante') && $mail instanceof CMSSanteMail):
                // Transforms attachments into a readable form
                foreach ($mail->loadRefAttachments() as $attachment) {
                    $attachment_file = $attachment->loadRefFile();
                    $attachment_name = explode('.', $attachment->name);

                    $view_attachment = MessagerieAttachmentLinkView::createFromData(
                        $attachment_name[0],
                        $attachment_name[1],
                        $attachment->_id,
                        $attachment->_class,
                        $attachment_file
                    );

                    $mail_view_attachments[] = $view_attachment;
                }

                // Transform mail into a readable form
                $mail_view = MessagerieMailLinkView::createFromData($mail->subject, $mail->_id, $mail->_class);

                break;
            default:
                return;
        }

        // Count all attachments + Mail
        $count = count($mail_view_attachments) + 1;

        $this->renderSmarty(
            'Link/inc_link_message',
            [
                'mail'        => $mail_view,
                'attachments' => $mail_view_attachments,
                'categories'  => $categories,
                'count'       => $count,
            ]
        );
    }

    /**
     * Search patient
     *
     * @return void
     * @throws Exception
     */
    public function searchPatient(): void
    {
        $this->checkPermRead();

        $keywords = CView::request('_patient_search', 'str');

        CView::checkin();

        $user    = CMediusers::get();
        $where   = (!$user->isAdmin() && CAppUI::conf('dPpatients CPatient function_distinct'))
            ? [
                'function_id' => $user->function_id,
                'group_id'    => $user->loadRefFunction()->group_id
            ]
            : [];

        $matches = (new CPatient())->getAutocompleteList($keywords, $where, 5);

        $this->renderSmarty(
            'Link/inc_patient_autocomplete',
            [
                'matches' => $matches
            ]
        );
    }

    /**
     * Show the contexts that can be linked to a patient
     *
     * @return void
     * @throws Exception
     */
    public function showPatientContext(): void
    {
        $this->checkPermRead();

        $patient_id = CView::get('patient_id', 'ref class|CPatient');

        CView::checkin();

        $group_id = (CAppUI::gconf("dPpatients sharing multi_group") == "hidden" || CModule::getActive('oxCabinet'))
            ? CGroups::loadCurrent()->_id
            : null;
        $offset   = self::DEFAULT_SEARCH_START . ', ' . self::DEFAULT_SEARCH_LIMIT;
        $patient  = CPatient::findOrFail($patient_id);
        $patient->loadRefPhotoIdentite();

        $patient_service  = new MessagerieLinkPatientService($patient_id, $group_id, $offset);
        $hospitalizations = $patient_service->loadPatientHospitalizations();
        $consultations    = $patient_service->loadPatientConsultations();
        $events           = $patient_service->loadPatientEvents();

        $this->renderSmarty(
            'Link/inc_patient_context',
            [
                'patient'          => $patient,
                'hospitalizations' => $hospitalizations,
                'consultations'    => $consultations,
                'events'           => $events
            ]
        );
    }

    /**
     * Show a specific context with more result
     *
     * @return void
     * @throws Exception
     * @throws MessagerieLinkException
     */
    public function showMorePatientContext(): void
    {
        $this->checkPermRead();

        $patient_id = CView::get('patient_id', 'ref class|CPatient');
        $start      = CView::get('start', 'str notNull');
        $offset     = CView::get('offset', 'str notNull');
        $context    = CView::get('context', 'enum list|CSejour|CConsultation|CEvenementPatient notNull');

        CView::checkin();

        $offset   = "$start, $offset";
        $group_id = CGroups::loadCurrent()->_id;

        $patient_service = new MessagerieLinkPatientService($patient_id, $group_id, $offset, false);

        switch ($context) {
            case 'CSejour':
                $tpl  = 'inc_hospitalizations_context';
                $vars = [
                    'hospitalizations' => $patient_service->loadPatientHospitalizations(),
                ];
                break;
            case 'CConsultation':
                $tpl  = 'inc_consultations_context';
                $vars = [
                    'consultations' => $patient_service->loadPatientConsultations(),
                ];
                break;
            case 'CEvenementPatient':
                $tpl  = 'inc_events_context';
                $vars = [
                    'events' => $patient_service->loadPatientEvents(),
                ];
                break;
            default:
                return;
        }

        $this->renderSmarty(
            "Link/Context/$tpl",
            $vars
        );
    }

    /**
     * Link files/messages to a context
     *
     * @return void
     * @throws Exception
     */
    public function linkAttachments(): void
    {
        $this->checkPermEdit();

        $link_context_guid = CView::post('link_context', 'str notNull');
        $link_attachments  = json_decode(
            mb_convert_encoding(
                stripslashes(CView::post('link_attachments', 'str notNull')),
                'UTF-8',
                'ISO-8859-1'
            ),
            true
        );

        CView::checkin();

        $context = CStoredObject::loadFromGuid($link_context_guid);
        $user_id = (CMediusers::get())->_id;
        $service = new MessagerieLinkService();

        // Log access
        if (
            !CModule::getActive('oxCabinet')
            && ($context instanceof CSejour || $context instanceof COperation)
        ) {
            CAccessMedicalData::logAccess($context, 'Link MSS File');
        }

        foreach ($link_attachments as $link_attachment) {
            /** @var MessagerieEntity $attachment */
            $attachment = CStoredObject::loadFromGuid($link_attachment["guid"]);

            $file_name     = mb_convert_encoding($link_attachment['name'], 'ISO-8859-1', 'UTF-8');
            $file_category = (int)$link_attachment['category'];

            try {
                switch (true) {
                    case (CModule::getActive('medimail')
                        && ($attachment instanceof CMedimailMessage || $attachment instanceof CMedimailAttachment)):
                        $msg = $service->fromMedimail(
                            $attachment,
                            $user_id,
                            $context->_id,
                            $context->_class,
                            $file_name,
                            $file_category
                        );

                        break;
                    case (CModule::getActive('mssante')
                        && ($attachment instanceof CMSSanteMail || $attachment instanceof CMSSanteMailAttachment)):
                        $msg = $service->fromMailiz(
                            $attachment,
                            $user_id,
                            $context->_id,
                            $context->_class,
                            $file_name,
                            $file_category
                        );

                        break;
                    default:
                        return;
                }

                ($msg === null)
                    ? CAppUI::setMsg('CMessagingLink-Msg-Success link', UI_MSG_OK)
                    : CAppUI::setMsg('CMessagingLink-Msg-Error link %s', UI_MSG_WARNING, $msg);
            } catch (Exception $e) {
                CAppUI::stepAjax('CMessagingLink-Msg-Error link %s', UI_MSG_ERROR, $e->getMessage());
            }
        }

        echo CAppUI::getMsg();
    }
}
