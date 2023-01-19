<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use DOMDocument;
use Ox\Core\CMbObject;
use Ox\Core\CMbXMLDocument;
use Ox\Mediboard\System\CContentXML;

/**
 * Class CEchangeXML
 * Echange XML
 */
class CEchangeXML extends CExchangeDataFormat
{
    /** @var string */
    public $identifiant_emetteur;
    /** @var int */
    public $initiateur_id;

    // Forward references
    /** @var CExchangeDataFormat[] */
    public $_ref_notifications;

    /**
     * @inheritDoc
     */
    public function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->loggable = false;

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props["identifiant_emetteur"]    = "str";
        $props["message_content_id"]      = "ref class|CContentXML show|0 cascade back|messages_xml";
        $props["acquittement_content_id"] = "ref class|CContentXML show|0 cascade back|acquittements_xml";

        $props["_message"]      = "xml";
        $props["_acquittement"] = "xml";

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function loadContent()
    {
        $content = new CContentXML();
        $content->load($this->message_content_id);
        $this->_message = $content->content;

        $content = new CContentXML();
        $content->load($this->acquittement_content_id);
        $this->_acquittement = $content->content;
    }

    /**
     * @inheritDoc
     */
    public function updatePlainFields()
    {
        parent::updatePlainFields();

        if ($this->_message !== null) {
            /** @var CContentXML $content */
            $content          = $this->loadFwdRef("message_content_id", true);
            $content->content = $this->_message;
            if ($msg = $content->store()) {
                return;
            }
            if (!$this->message_content_id) {
                $this->message_content_id = $content->_id;
            }
        }

        if ($this->_acquittement !== null) {
            /** @var CContentXML $content */
            $content          = $this->loadFwdRef("acquittement_content_id", true);
            $content->content = $this->_acquittement;
            if ($msg = $content->store()) {
                return;
            }
            if (!$this->acquittement_content_id) {
                $this->acquittement_content_id = $content->_id;
            }
        }
    }

    /**
     * Set ACK error
     *
     * @param DOMDocument $dom_acq      Acquittement
     * @param array       $code_erreur  Error codes
     * @param string|null $commentaires Comments
     * @param CMbObject   $mbObject     Object
     * @param array       $data         Objects
     *
     * @return void
     */
    public function setAckError($dom_acq, $code_erreur, $commentaires = null, CMbObject $mbObject = null, $data = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function understand(string $data, CInteropActor $actor = null): bool
    {
        if (!$dom = $this->isWellFormed($data)) {
            return false;
        }

        $root     = $dom->documentElement;
        $nodeName = $root->nodeName;
        foreach ($this->getFamily() as $_message) {
            $message_class     = new $_message();
            $document_elements = $message_class->getDocumentElements();

            if (array_key_exists($nodeName, $document_elements)) {
                $this->_events_message_by_family[$_message][] = new $document_elements[$nodeName]();

                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     *            
     * @return CMbXMLDocument|null
     */
    public function isWellFormed($data)
    {
        $dom = new CMbXMLDocument();
        if ($dom->loadXML($data, LIBXML_NOWARNING | LIBXML_NOERROR) !== false) {
            return $dom;
        }

        return null;
    }
}


