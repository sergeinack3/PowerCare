<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Handle;

use DOMNode;
use Ox\Core\CStoredObject;

class CCDAMetaOrder extends CStoredObject
{
    // DB Table key
    /** @var int */
    public $cda_meta_order_id;

    /** @var string */
    public $created_datetime;
    /** @var int */
    public $cda_meta_id;
    /** @var string */
    public $orderId;
    /** @var string */
    public $orderCodeSystem;
    /** @var string */
    public $data;
    /** @var string */
    public $data_hash;

    /** @var CCDAMeta */
    public $_ref_cda_meta;

    /**
     * @inheritDoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'cda_meta_order';
        $spec->key   = 'cda_meta_order_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props['created_datetime'] = "dateTime notNull";
        $props["cda_meta_id"]      = "ref notNull class|CCDAMeta back|cda_meta_order";
        $props["orderId"]          = "str";
        $props["orderCodeSystem"]  = "str";
        $props["data"]             = "text";
        $props["data_hash"]        = "str";

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
        if (!$node = $cda_handle->getCDADomDocument()->getInFulfillmentOf()) {
            return;
        }

        $this->cda_meta_id = $cda_handle->getMeta()->_id;
        $cda_handle->handleMetaUnStructuredData($this, $node);

        $this->handleMetaStructuredData($cda_handle, $node);

        $report = $cda_handle->getCDADomDocument()->getReport();
        if ($msg = $this->store()) {
            $report->addItemFailed($this, $msg);
        }

        $report->addItemsStored($this);
    }

    /**
     * Store structured data
     *
     * @param CCDAHandle $cda_handle
     * @param DOMNode    $node
     *
     * @return void
     */
    private function handleMetaStructuredData(CCDAHandle $cda_handle, DOMNode $node): void
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
