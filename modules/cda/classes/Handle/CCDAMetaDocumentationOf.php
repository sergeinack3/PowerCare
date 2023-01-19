<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Handle;

use DOMNodeList;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;

class CCDAMetaDocumentationOf extends CStoredObject
{
    // DB Table key
    /** @var int */
    public $cda_meta_documentation_of_id;

    /** @var string */
    public $created_datetime;
    /** @var int */
    public $cda_meta_id;
    /** @var string */
    public $serviceEventCode;
    /** @var string */
    public $serviceEventCodeSystem;
    /** @var string */
    public $serviceEventStart;
    /** @var string */
    public $serviceEventEnd;
    /** @var int */
    public $serviceEventPerformerId;
    /** @var string */
    public $serviceEventPerformerClass;
    /** @var string */
    public $data;
    /** @var string */
    public $data_hash;

    /** @var CCDAMeta */
    public $_ref_cda_meta;

    /** @var CCDAMeta */
    public $_ref_performer;

    /**
     * @inheritDoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'cda_meta_documentation_of';
        $spec->key   = 'cda_meta_documentation_of_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props['created_datetime']           = "dateTime notNull";
        $props["cda_meta_id"]                = "ref notNull class|CCDAMeta back|cda_meta_documentation_of";
        $props["serviceEventCode"]           = "str";
        $props["serviceEventCodeSystem"]     = "str";
        $props["serviceEventStart"]          = "dateTime";
        $props["serviceEventEnd"]            = "dateTime";
        $props["serviceEventPerformerId"]    = "ref class|CMbObject meta|serviceEventPerformerClass back|cda_performer";
        $props["serviceEventPerformerClass"] = "enum list|CMedecin|CPatient";
        $props["data"]                       = "text";
        $props["data_hash"]                  = "str";

        return $props;
    }

    /**
     * Load metadata
     *
     * @return CCDAMeta|CStoredObject
     */
    public function loadRefCDAMeta(): CCDAMeta
    {
        return $this->_ref_cda_meta = $this->loadFwdRef("cda_meta_id", true);
    }

    /**
     * Load performer
     *
     * @return CMedecin|CPatient|CStoredObject
     */
    public function loadRefCDAPerformer(): CStoredObject
    {
        $this->_ref_performer = new $this->serviceEventPerformerClass();
        $this->_ref_performer->load($this->serviceEventPerformerId);

        return $this->_ref_performer;
    }

    /**
     * Get metadata participant JSON
     *
     * @return string
     */
    public function getDataJSON(): string
    {
        return $this->data;
    }

    /**
     * Handle order
     *
     * @param CCDAHandle $cda_handle
     *
     * @return void
     */
    public function handle(CCDAHandle $cda_handle): void
    {
        $nodes = $cda_handle->getCDADomDocument()->getDocumentationOf();
        if ($nodes->length === 0) {
            return;
        }

        $this->cda_meta_id = $cda_handle->getMeta()->_id;
        $cda_handle->handleMetaUnStructuredData($this, $nodes);

        $this->handleMetaStructuredData($cda_handle, $nodes);

        $report = $cda_handle->getCDADomDocument()->getReport();
        if ($msg = $this->store()) {
            $report->addItemFailed($this, $msg);
        }

        $report->addItemsStored($this);
    }

    /**
     * Store structured data
     *
     * @param CCDAHandle  $cda_handle
     * @param DOMNodeList $nodes
     *
     * @return void
     */
    private function handleMetaStructuredData(CCDAHandle $cda_handle, DOMNodeList $nodes): void
    {
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if (!$this->_id) {
            $this->created_datetime = "now";
        }

        if ($msg = parent::store()) {
            return $msg;
        }
    }
}
