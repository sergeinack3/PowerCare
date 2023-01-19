<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Handle;

use DOMNode;
use Ox\Core\CMbArray;
use Ox\Core\CMbPath;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Exception\CCDAExceptionLevel1;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Sante400\CIdSante400;

class CCDAHandleLevel1 extends CCDAHandle
{
    /** @var DOMNode */
    protected $nonXMLBody;
    /** @var string */
    protected $nonXMLBodyText;
    /** @var string */
    protected $nonXMLBodyMediaType;
    /** @var string */
    protected $nonXMLBodyRepresentation;

    /**
     * @inheritDoc
     */
    protected function handleComponents(): void
    {
        $this->patient = $this->getPatient();

        $cda_dom_document = $this->getCDADomDocument();
        $this->nonXMLBody = $cda_dom_document->getNonXMLBody();

        $this->handleXMLBodyMediaType();
        $this->handleXMLBodyText();

        $this->storeFile();
    }

    /**
     * Handle xml body media type & representation
     *
     * @return void
     * @throws CCDAExceptionLevel1
     */
    private function handleXMLBodyMediaType(): void
    {
        $cda_dom_document = $this->getCDADomDocument();

        $text                           = $cda_dom_document->queryNode('text', $this->nonXMLBody);
        $this->nonXMLBodyMediaType      = $cda_dom_document->getValueAttributNode($text, 'mediaType');
        $this->nonXMLBodyRepresentation = $cda_dom_document->getValueAttributNode($text, 'representation');

        // On regarde si on gère le type de fichier reçu
        if (!CMbArray::in($this->nonXMLBodyMediaType, CDocumentItem::$extensions_authorized_for_cda)) {
            throw CCDAExceptionLevel1::unknownMediaType();
        }

        // Est-ce une représentation en base 64 ?
        if ($this->nonXMLBodyRepresentation !== 'B64') {
            throw CCDAExceptionLevel1::notBase64File();
        }
    }

    /**
     * Handle XML body text
     *
     * @return void
     */
    private function handleXMLBodyText(): void
    {
        $this->nonXMLBodyText = base64_decode(
            $this->getCDADomDocument()->queryTextNode('text', $this->nonXMLBody)
        );
    }

    /**
     * Store file
     *
     * @return void
     * @throws CCDAExceptionLevel1|CCDAException
     */
    private function storeFile(): void
    {
        if (!$this->target_object) {
            throw CCDAExceptionLevel1::noTargetToSaveFile();
        }

        $file = new CFile();
        $idex = CIdSante400::getMatch(
            'CFile',
            $this->getCDADomDocument()->_ref_sender->_self_tag,
            $this->getMeta()->id
        );
        if ($idex->_id) {
            $file->load($idex->object_id);
        }

        $file->file_name = $this->getMeta()->title . CMbPath::getExtensionByMimeType($this->nonXMLBodyMediaType);
        $file->setObject($this->target_object);
        $file->fillFields();
        $file->file_date = $this->getMeta()->effectiveTime;
        //$file->author_id = $this->getMeta()->aut;
        $file->type_doc_dmp = $this->getCDADomDocument()->getTypeDoc();
        $file->file_type    = $this->nonXMLBodyMediaType;
        $file->private      = 0;
        $file->setContent($this->nonXMLBodyText);
        if ($msg = $file->store()) {
            unlink($file->_file_path);

            throw CCDAExceptionLevel1::errorStoreEmbedCDAFile($msg);
        }

        $this->getReport()->addItemsStored($file);

        $idex->object_id = $file->_id;
        if ($msg = $idex->store()) {
            $this->report->addItemFailed($idex, $msg);
        }

        $this->report->addItemsStored($idex);
    }
}
