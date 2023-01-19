<?php

/**
 * @package Mediboard\Fhir\Objects
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities;

use DOMDocument;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbXMLDocument;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Exception\CFHIRExceptionInvalidValue;
use Ox\Interop\Fhir\Exception\CFHIRExceptionNotFound;
use Ox\Interop\Fhir\Resources\R4\ValueSet\CFHIRResourceValueSet;
use Ox\Interop\Fhir\Serializers\CFHIRParser;
use Psr\SimpleCache\InvalidArgumentException;

class ValueSetRepository
{
    /** @var string */
    private const ROOT_PATH = '/modules/interopResources/resources/valuesets/';

    /** @var string[] */
    public const JDV = [
        "http://openxtrem.com/ValueSet/OX-JDV-LABORATORY/FHIR/OX-JDV-LABORATORY" => self::OX_VALUE_SET_LABORATORY
    ];

    /** @var string */
    public const OX_VALUE_SET_LABORATORY = 'OX-JDV-LABORATORY';

    private ?string $path_to_valueSet = null;

    private string $type_value_set = 'OX';

    private ?DOMDocument $dom = null;

    private ?CFHIRResourceValueSet $value_set = null;

    public static function get(string $name_or_codeSystem, string $type = 'OX'): self
    {
        return (new self())
            ->selectValueSet($name_or_codeSystem)
            ->setTypeValueSet($type);
    }

    /**
     * @param string $type_value_set
     *
     * @return ValueSetRepository
     */
    public function setTypeValueSet(string $type_value_set): ValueSetRepository
    {
        $this->type_value_set = $type_value_set;

        return $this;
    }

    /**
     * Reset data when value set is changed
     *
     * @return void
     */
    private function reset(): void
    {
        $this->dom              = null;
        $this->value_set        = null;
        $this->path_to_valueSet = null;
    }

    /**
     * @return DOMDocument
     */
    private function getDom(): DOMDocument
    {
        if (!$dom = $this->dom) {
            $this->dom = $dom = new CMbXMLDocument();
            if ($this->path_to_valueSet) {
                $dom->load($this->path_to_valueSet);
            }
        }

        return $dom;
    }

    /**
     * @return CFHIRResourceValueSet
     * @throws InvalidArgumentException
     */
    private function getValueSet(): CFHIRResourceValueSet
    {
        if (!$resource_parsed = $this->value_set) {
            $dom = $this->getDom();

            $parser = CFHIRParser::parse($dom->saveXML());
            $resource_parsed = $parser->getResource();

            if (!$resource_parsed instanceof CFHIRResourceValueSet) {
                throw new CFHIRExceptionInvalidValue('The resource parsed was not a value set');
            }
        }

        return $this->value_set = $resource_parsed;
    }

    /**
     * Select a value set
     *
     * @param string $name_or_codeSystem
     *
     * @return $this
     */
    public function selectValueSet(string $name_or_codeSystem): self
    {
        // reset document
        $this->dom = null;

        // value given is name of value set ?
        if (in_array($name_or_codeSystem, self::JDV)) {
            $this->path_to_valueSet = $this->getRootPath() . "/" . $name_or_codeSystem . '.xml';

            return $this;
        }

        // value given is a code system
        if (array_key_exists($name_or_codeSystem, self::JDV)) {
            $this->path_to_valueSet = $this->getRootPath() . self::JDV[$name_or_codeSystem] . '.xml';

            return $this;
        }

        // value is not code system and not name of value set
        throw new CFHIRExceptionNotFound('The ValueSet requested is not found');
    }

    /**
     * Get root path
     *
     * @return string
     * @throws Exception
     */
    private function getRootPath(): string
    {
        return rtrim(CAppUI::conf('root_dir'), '/') . self::ROOT_PATH . "$this->type_value_set/FHIR/";
    }

    /**
     * Search a code in the code system among the value set
     *
     * @param string $code_system
     * @param string $code
     *
     * @return CFHIRDataTypeCoding|null
     * @throws InvalidArgumentException
     */
    public function fromCodeSystem(string $code_system, string $code): ?CFHIRDataTypeCoding
    {
        if (!$value_set = $this->getValueSet()) {
            return null;
        }

        return $value_set->searchCoding($code_system, $code);
    }
}
