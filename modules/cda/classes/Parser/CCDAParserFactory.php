<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Parser;

use Exception;
use Ox\Core\CMbObject;
use Ox\Interop\Cda\CCDADomDocument;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\CCDAReport;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Handle\CCDAHandle;
use Ox\Interop\Cda\Levels\Level1\CCDALevel1;

/**
 * Class CCDAParserFactory
 * Parser CDA Document
 */
class CCDAParserFactory
{
    /** @var CCDADomDocument */
    public $cda_dom_document;
    /** @var int */
    protected $level;
    /** @var CCDAFactory */
    private $cda_factory;
    /** @var CCDAHandle */
    private $handle_object;
    /** @var CMbObject */
    private $target_object;
    /** @var CCDAReport */
    private $report;
    /** @var Exception|CCDAException */
    private $exception;
    /** @var bool */
    private $has_error;

    /**
     * Construct
     *
     * @param CCDADomDocument $cda_dom_document DOM Document
     *
     */
    public function __construct(CCDADomDocument $cda_dom_document)
    {
        $this->cda_dom_document = $cda_dom_document;
    }

    /**
     * @param CCDADomDocument $cda_dom_document
     * @return static
     */
    public static function parseDocument(CCDADomDocument $cda_dom_document): self
    {
        $parser = new self($cda_dom_document);
        $parser->parse();

        return $parser;
    }

    /**
     * Parse CDA DOM Document
     *
     * @return bool
     */
    public function parse(): bool
    {
        try {
            $this->getCDADomDocument()->getContentNodes();

            // Détermine le level du CDA
            $this->determineLevel();

            // determine le cda factory
            $this->cda_factory = $this->determineCDAFactory(new CMbObject());

            $this->handle_object = $this->cda_factory->getHandle();
        } catch (Exception $exception) {
            $this->exception = $exception;
        } finally {
            $this->has_error = !($this->cda_factory && $this->handle_object);
        }

        return !$this->has_error;
    }

    /**
     * @return CCDADomDocument
     */
    public function getCDADomDocument(): CCDADomDocument
    {
        return $this->cda_dom_document;
    }

    /**
     * Determine CDA level
     *
     * @return int
     */
    private function determineLevel(): ?int
    {
        $this->getCDADomDocument()->setLevel();

        if (!$this->level = $this->getCDADomDocument()->getLevel()) {
            /* @todo Exception */
        }

        return $this->level;
    }

    /**
     * @param CMbObject $object
     *
     * @return CCDAFactory
     * @throws CCDAException
     * @throws Exception
     */
    private function determineCDAFactory(CMbObject $object): CCDAFactory
    {
        $class = null;
        if ($this->level === CCDADomDocument::LEVEL_3) {
            $class = CCDAFactory::get($this->getCDADomDocument()->getTypeDoc());
        } elseif ($this->level === CCDADomDocument::LEVEL_1) {
            $class = CCDALevel1::class;
        }

        if (!$class) {
            throw CCDAException::invalidFactoryType();
        }

        return new $class($object);
    }

    /**
     * @return CCDAReport
     */
    public function getReport(): CCDAReport
    {
        return $this->report;
    }

    /**
     * @param CCDAReport $report
     */
    public function setReport(CCDAReport $report): void
    {
        $this->report = $report;
    }

    public function getHandleObject(): ?CCDAHandle
    {
        return $this->handle_object;
    }

    /**
     * @return Exception|CCDAException|null
     */
    public function getException(): ?Exception
    {
        return $this->exception;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->has_error;
    }
}
