<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\BundleBuilder;

use Ox\Core\CMbSecurity;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUnsignedInt;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle\CFHIRDataTypeBundleEntry;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle\CFHIRDataTypeBundleLink;
use Ox\Interop\Fhir\Resources\R4\Bundle\CFHIRResourceBundle;

/**
 * Description
 */
class BundleBuilder
{
    /** @var CFHIRResourceBundle */
    protected $bundle;

    /**
     * @param CFHIRResourceBundle|null $bundle
     */
    public function __construct(CFHIRResourceBundle $bundle = null)
    {
        if (!$bundle) {
            $bundle = new CFHIRResourceBundle();
        }

        if (!$bundle->getResourceId()) {
            $bundle->setId(new CFHIRDataTypeString(CMbSecurity::generateUUID()));
        }

        $this->bundle = $bundle;
    }

    /**
     * @param CFHIRDataTypeBundleEntry|null $entry
     *
     * @return CFHIRDataTypeBundleEntry
     */
    public function addEntry(CFHIRDataTypeBundleEntry $entry = null): CFHIRDataTypeBundleEntry
    {
        if (!$entry) {
            $entry = new CFHIRDataTypeBundleEntry();
        }

        $this->bundle->addEntry($entry);

        return $entry;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->bundle->setType(new CFHIRDataTypeCode($type));

        return $this;
    }

    /**
     * @param string $datetime
     *
     * @return $this
     */
    public function setTimestamp(string $datetime): self
    {
        $this->bundle->setTimestamp(new CFHIRDataTypeInstant($datetime));

        return $this;
    }

    /**
     * @param int $total
     *
     * @return $this
     */
    public function setTotal(int $total): self
    {
        $this->bundle->setTotal(new CFHIRDataTypeUnsignedInt($total));

        return $this;
    }

    /**
     * @return $this
     */
    public function addLink(string $url, string $relation): self
    {
        $link = (new CFHIRDataTypeBundleLink())
            ->setRelation($relation)
            ->setUrl($url);

        $this->bundle->addLink($link);

        return $this;
    }
    /**
     * @param CFHIRDataTypeBundleLink $link
     *
     * @return $this
     */
    public function addLinkElement(CFHIRDataTypeBundleLink $link): self
    {
        $this->bundle->addLink($link);

        return $this;
    }

    /**
     * @param CFHIRDataTypeBundleLink[] $link
     *
     * @return $this
     */
    public function setLink(array $link): self
    {
        $this->bundle->setLink(...$link);

        return $this;
    }

    /**
     * @param CFHIRResourceBundle|null $bundle
     *
     * @return BuilderBatchTransaction
     */
    public static function getBuilderTransaction(CFHIRResourceBundle $bundle = null): BuilderBatchTransaction
    {
        return (new BuilderBatchTransaction($bundle))->setType(CFHIRResourceBundle::TYPE_TRANSACTION);
    }

    /**
     * @param CFHIRResourceBundle|null $bundle
     *
     * @return BuilderBatchTransaction
     */
    public static function getBuilderBatch(CFHIRResourceBundle $bundle = null): BuilderBatchTransaction
    {
        return (new BuilderBatchTransaction($bundle))->setType(CFHIRResourceBundle::TYPE_BATCH);
    }

    /**
     * @param CFHIRResourceBundle|null $bundle
     *
     * @return BuilderCollection
     */
    public static function getBuilderCollection(CFHIRResourceBundle $bundle = null): BuilderCollection
    {
        return (new BuilderCollection($bundle))->setType(CFHIRResourceBundle::TYPE_COLLECTION);
    }

    /**
     * @param CFHIRResourceBundle|null $bundle
     *
     * @return BuilderCollection
     */
    public static function getBuilderSearchset(CFHIRResourceBundle $bundle = null): BuilderCollection
    {
        return (new BuilderCollection($bundle))->setType(CFHIRResourceBundle::TYPE_SEARCHSET);
    }

    public static function getBuilderHistory(CFHIRResourceBundle $bundle = null): BuilderCollection
    {
        return (new BuilderCollection($bundle))->setType(CFHIRResourceBundle::TYPE_HISTORY);
    }

    public static function getBuilderBatchResponse(CFHIRResourceBundle $bundle = null): BuilderBatchTransactionResponse
    {
        return (new BuilderBatchTransactionResponse($bundle))->setType(CFHIRResourceBundle::TYPE_BATCH_RESPONSE);
    }

    public static function getBuilderTransactionResponse(CFHIRResourceBundle $bundle = null): BuilderBatchTransactionResponse
    {
        return (new BuilderBatchTransactionResponse($bundle))->setType(CFHIRResourceBundle::TYPE_TRANSACTION_RESPONSE);
    }

    /**
     * @return CFHIRResourceBundle
     */
    public function build(): CFHIRResourceBundle
    {
        return $this->bundle;
    }
}
