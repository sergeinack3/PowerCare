<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds;

use Ox\Core\Cache;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Interop\Eai\CItemReport;
use Ox\Interop\Xds\Exception\CXDSException;
use Ox\Interop\Xds\Factory\CXDSFactory;
use Ox\Interop\Xds\Factory\IXDSContext;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Psr\SimpleCache\InvalidArgumentException;

class CXDSRepository
{
    /** @var string */
    public const REQUEST_ITI_57 = 'Update_Document_Set';
    /** @var string */
    public const REQUEST_ITI_43 = 'Retrieve_Document_Set';
    /** @var string */
    public const REQUEST_ITI_41 = 'Provide_and_Register_Document_Set_b';
    /** @var string */
    public const REQUEST_ITI_32 = 'Distribute_Document_Set_on_Media';
    /** @var string */
    public const REQUEST_ITI_18 = 'Registry_Stored_Query';

    /** @var CXDSFactory */
    private $factory;

    /** @var CFile */
    private $file;

    /** @var string */
    private $content;

    /** @var CXDSXmlDocument */
    private $dom;

    /**
     * CXDSRepository constructor.
     */
    public function __construct(string $type, ?CDocumentItem $document = null, ?CDocumentItem $docItem = null)
    {
        $this->factory = CXDSFactory::factory($type, $document, $docItem);
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options) {
        foreach ($options as $key => $option) {
            if (!property_exists($this->factory, $key)) {
                continue;
            }

            $this->factory->$key = $option;
        }
    }

    /**
     * @return CXDSXmlDocument
     */
    public function getDom(): CXDSXmlDocument
    {
        return $this->dom;
    }

    /**
     * @return CXDSFactory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @param IXDSContext $context
     */
    public function setContextXDS(IXDSContext $context) {
        $this->factory->context_object = $context;
    }

    /**
     * @param string $request_type
     * @param array  $options
     *
     * @return string
     * @throws CMbException
     * @throws CXDSException
     */
    public function generateContentXDS(string $request_type, array $options = []): string
    {
        if (!$this->content) {
            $this->content = $this->generateXDS($request_type, $options);
        }

        return $this->content;
    }

    /**
     * @param bool $xml_declaration
     *
     * @return string
     */
    public function regenerateContentFromDOM(bool $xml_declaration = false): ?string
    {
        if (!$this->dom) {
            return null;
        }

        return $this->dom->saveXML($xml_declaration ? null : $this->dom->documentElement);
    }

    /**
     * @return string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @return CFile
     */
    public function generateFileXDS(): CFile
    {
        if (!$this->file) {
        }

        return $this->file;
    }

    /**
     * @param string $request_type
     * @param array  $options
     *
     * @return string
     * @throws CMbException
     * @throws CXDSException
     */
    private function generateXDS(string $request_type, array $options): string
    {
        // extract data
        if ($this->factory->document) {
            $this->factory->extractData();
        }

        // set to true for add xml declaration in the first line of document
        $xml_declaration = CMbArray::get($options, 'xml_declaration', false);

        // generate xds document
        switch ($request_type) {
            case self::REQUEST_ITI_57:
                $uuid         = CMbArray::get($options, 'uuid');
                $archivage    = CMbArray::get($options, 'archivage');
                $masquage     = CMbArray::get($options, 'hide');
                $id_extrinsic = CMbArray::get($options, 'id_extrinsic');
                $metadata     = CMbArray::get($options, 'metadata');
                $dom          = $this->factory->generateXDS57($uuid, $archivage, $masquage, $id_extrinsic, $metadata);
                break;
            case self::REQUEST_ITI_41:
                $document = CMbArray::get($options, 'document');
                $dom      = $this->factory->generateXDS41($document);
                break;
            case self::REQUEST_ITI_32:
                $status             = CMbArray::get($options, 'status');
                $dom                = $this->factory->generateXDS32($status);
                break;
            case self::REQUEST_ITI_43:
                $repository_id = CMbArray::get($options, 'repository_id');
                $oid           = CMbArray::get($options, 'oid');
                $dom           = $this->factory->generateXDS43($repository_id, $oid);
                break;
            case self::REQUEST_ITI_18:
                $query = CMbArray::get($options, 'query');
                $dom = $this->factory->generateXDS18($query);
                break;
            default:
                throw CXDSException::invalidRequestType($request_type);
        }

        $this->dom = $dom;

        return $dom->saveXML($xml_declaration ? null : $this->dom->documentElement);
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        // get only items with severity in error
        $error_items = array_filter(
            $this->factory->report->getItems(),
            function (CItemReport $item_report) {
                return $item_report->getSeverity() === CItemReport::SEVERITY_ERROR;
            }
        );

        return count($error_items) > 0;
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    public function clearCache(): bool
    {
        $cache = new Cache('xds_factory', 'xds_class', Cache::INNER_OUTER);
        return $cache->rem();
    }
}
