<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Interop\Cda\CCDARepository;
use Ox\Interop\Cda\Levels\Level1\ANS\CCDAANS;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CReadFile;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Docitem managing
 */
class CDocumentItemController extends CLegacyController
{
    /**
     * Copy selected documents
     *
     * @return mixed
     * @throws Exception
     */
    public function copySelectedDocItems(): ?string
    {
        $this->checkPermEdit();
        $docitem_guids     = CView::post("docitem_guids", "str");
        $context_guid_copy = CView::post("context_guid_copy", 'str');
        $prefix = CView::post("prefix", 'str');
        CView::checkin();

        $docitem_guids = json_decode(stripslashes($docitem_guids), true);
        $counter_file  = 0;
        $curr_user     = CMediusers::get();

        $context_copy = CMbObject::loadFromGuid($context_guid_copy);

        foreach ($docitem_guids as $_document_guid) {
            /** @var $docitem CDocumentItem */
            $docitem               = CMbObject::loadFromGuid($_document_guid);
            $docitem->_id          = '';
            $docitem->object_class = $context_copy->_class;
            $docitem->object_id    = $context_copy->_id;
            $docitem->author_id    = $curr_user->_id;

            $msg = null;

            if ($docitem instanceof CFile) {
                $save_path                   = $docitem->_file_path;
                $docitem->_file_path         = '';
                $docitem->file_name          = $prefix . $docitem->file_name;
                $docitem->file_real_filename = '';
                $docitem->file_date          = '';
                $docitem->fillFields();
                $docitem->updateFormFields();
                $docitem->setCopyFrom($save_path);
                $docitem->forceDir();

                $msg = $docitem->store();
            } elseif ($docitem instanceof CCompteRendu) {
                $docitem->loadContent();
                $docitem->content_id        = '';
                $docitem->_ref_content->_id = '';
                $docitem->nom               = $prefix . $docitem->nom;

                $msg = $docitem->store();
            }

            if ($msg) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
            } else {
                $counter_file++;
            }
        }

        CAppUI::stepAjax(CAppUI::tr("CDocumentItem-msg-%s file (s) added in the patient event", $counter_file));

        return CAppUI::getMsg();
    }

    public function add_type_document()
    {
        $this->checkPermEdit();
        $docItem_guid = CView::get("document_guid", "str");
        CView::checkin();

        $docItem = CMbObject::loadFromGuid($docItem_guid);

        return $this->renderSmarty('inc_type_document', [
            'docItem' => $docItem,
        ]);
    }

    public function confirm_generate_cda()
    {
        $this->checkPermEdit();
        $docItem_guid = CView::get("document_guid", "str");
        CView::checkin();

        $docItem = CMbObject::loadFromGuid($docItem_guid);

        return $this->renderSmarty('inc_confirm_type_document', [
            'docItem' => $docItem,
        ]);
    }

    public function generate_cda()
    {
        $this->checkPermEdit();
        $docItem_guid = CView::get("document_guid", "str");
        CView::checkin();

        /** @var CMbObject $docItem */
        $docItem = CMbObject::loadFromGuid($docItem_guid);
        if (!$docItem || !$docItem->_id) {
            CAppUI::stepAjax('CDocumentItem.none', UI_MSG_ERROR);
        }

        // Repository CDA
        $repo_cda = new CCDARepository(CCDAANS::TYPE, $docItem);

        if ($docItem instanceof CCompteRendu && $docItem->version > 1) {
            $repo_cda->setOptions(
                [
                    'old_version' => $docItem->version - 1,
                    'old_id'      => $docItem->_id,
                ]
            );
        }

        // Generate content file and content cda
        $content_cda = $repo_cda->getContentCda();
        $report      = $repo_cda->getReport();

        if (!$report->getItems()) {
            $file               = new CFile();
            $file->object_class = $docItem->object_class;
            $file->object_id    = $docItem->object_id;
            $file->file_name    = $docItem instanceof CFile ? CMbArray::get($docItem->getPathInfo(), 'filename') . '.xml' : $docItem->nom . '.xml';
            $file->type_doc_dmp = $docItem->type_doc_dmp;
            $file->file_type    = 'application/xml';
            $file->loadMatchingObject();
            if ($file->_id) {
                $file->completeField();
            }

            $file->fillFields();
            $file->setContent($content_cda);

            if ($msg = $file->store()) {
                CAppUI::stepAjax($msg, UI_MSG_ERROR);
            }
        }

        return $this->renderSmarty('inc_generate_cda', [
            'report' => $report,
        ]);
    }

    public function readFile(): void
    {
        $this->checkPermEdit();
        $docitem_id    = CView::get("docitem_id", "num");
        $docitem_class = CView::get("docitem_class", "str");
        $object_class  = CView::get("object_class", "str");
        $object_id     = CView::get("object_id", "num");
        $uid_unread    = CView::get("uid_unread", "str");
        CView::checkin();

        /** @var CMbObject $object */
        $object = $object_class::findOrFail($object_id);
        $object->loadRefsDocItems();

        $readFile               = new CReadFile();
        $readFile->object_class = $docitem_class;
        $readFile->object_id    = $docitem_id;
        $readFile->user_id      = CMediusers::get()->_id;
        $readFile->datetime     = 'now';
        $readFile->store();

        $this->renderSmarty(
            'inc_read_file',
            [
                'object_id'    => $object_id,
                'object_class' => $object_class,
                'documents'    => CReadFile::getUnread([$object])[$object->_id],
                'uid_unread'   => $uid_unread
            ]
        );
    }
}
