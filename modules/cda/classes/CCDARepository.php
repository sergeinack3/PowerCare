<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Interop\Eai\CItemReport;
use Ox\Interop\Eai\CReport;
use Ox\Mediboard\Files\CFile;
use Psr\SimpleCache\InvalidArgumentException;

class CCDARepository
{
    /** @var CFile */
    private $file;

    /** @var string */
    private $content_cda;

    /** @var CCDAFactory */
    private $factory;


    /**
     * CCDARepository constructor.
     *
     * @param string|null $type
     * @param CMbObject   $object
     *
     * @throws Exception
     */
    public function __construct(?string $type, CMbObject $object)
    {
        $this->factory = CCDAFactory::factory($type, $object);
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
     * @return string
     * @throws CMbException
     */
    public function getContentCda(): string
    {
        if (!$this->content_cda) {
            $this->content_cda = $this->factory->generateContentCDA();
        }

        return $this->content_cda;
    }

    /**
     * Get file created but not stored from repo cda
     *
     * @return CFile
     * @throws CMbException
     */
    public function getFileCDA(): CFile
    {
        if (!$this->file) {
            $this->file        = $this->factory->generateFileCDA();
            $this->content_cda = $this->file->getContent();
        }

        return $this->file;
    }

    /**
     * @return CCDAFactory
     */
    public function getFactory(): CCDAFactory
    {
        return $this->factory;
    }

    /**
     * @return CReport
     */
    public function getReport(): CReport
    {
        return $this->factory->report;
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
     * @throws CMbException
     */
    public function validate(): bool
    {
        if (!$this->content_cda) {
            return false;
        }
        try {
            CCdaTools::validateCDA($this->content_cda);
        } catch (CMbException $e) {
            throw $e;
        }

        return true;
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    public function clearCache(): bool
    {
        $cache = new Cache('cda_factory', 'cda_class', Cache::INNER_OUTER);

        return $cache->rem();
    }

    /**
     * Get CDA DOM document
     *
     * @return CCDADomDocument|null
     */
    public function getCDADomDocument(): ?CCDADomDocument
    {
        return $this->getFactory()->dom_cda;
    }
}

