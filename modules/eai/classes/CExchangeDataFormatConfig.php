<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\CMbObjectConfig;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CStoredObject;

/**
 * Class CExchangeDataFormatConfig
 * Echange Data Format Config
 */
class CExchangeDataFormatConfig extends CMbObjectConfig
{
    /** @var array */
    public static $config_fields = [];

    // DB Fields
    // Sender
    /** @var int */
    public $sender_id;
    /** @var string */
    public $sender_class;

    // Form fields
    /** @var array */
    public $_config_fields;

    /**
     * References
     */

    /** @var CInteropSender */
    public $_ref_sender;

    /**
     * @see parent::getProps
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["sender_id"]    = "ref class|CInteropSender meta|sender_class";
        $props["sender_class"] = "enum list|CSenderSFTP|CSenderFTP|CSenderSOAP|CSenderFileSystem";

        return $props;
    }

    /**
     * Load interop sender
     *
     * @return CInteropSender|CStoredObject
     */
    public function loadRefSender(): CInteropSender
    {
        return $this->_ref_sender = $this->loadFwdRef("sender_id", true);
    }

    /**
     * @see parent::store
     */
    function store()
    {
        $this->exportXML();

        return parent::store();
    }

    /**
     * Export XML
     *
     * @return CMbXMLDocument
     */
    public function exportXML(): CMbXMLDocument
    {
        $doc  = new CMbXMLDocument();
        $root = $doc->addElement($doc, $this->_class);

        foreach ($this->getConfigFields() as $field) {
            $node = $doc->addElement($root, "entry");
            $doc->addAttribute($node, "config", $field);
            $doc->addAttribute($node, "value", $this->$field);
        }

        return $doc;
    }

    /**
     * Get config fields
     *
     * @return array
     */
    function getConfigFields()
    {
        return $this->_config_fields = self::$config_fields;
    }

    /**
     * Import XML
     *
     * @return void
     */
    public function importXML(): void
    {
    }
}
