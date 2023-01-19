<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use Ox\Core\CMbArray;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;

/**
 * FHIR data type
 */
class CFHIRDataTypeCodeableConcept extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'CodeableConcept';

    /** @var CFHIRDataTypeCoding[] */
    public $coding;

    /** @var CFHIRDataTypeString */
    public $text;

    /**
     * Get from values which come from values set
     *
     * @param array $values
     *
     * @return static
     */
    public static function fromValues(array $values): self
    {
        $self = new self();

        if ($text = CMbArray::get($values, 'text')) {
            $self->text = new CFHIRDataTypeString($text);
        }

        $self->coding[] = CFHIRDataTypeCoding::fromValues($values);

        return $self;
    }

    /**
     * @param array|CFHIRDataTypeCoding[]|CFHIRDataTypeCoding $codingData
     * @param string|null                                     $text
     * @param array                                           $reference
     *
     * @return CFHIRDataTypeCodeableConcept[]|CFHIRDataTypeCodeableConcept
     */
    public static function addCodeable($codingData, ?string $text = null, ?array $reference = null)
    {
        if (!is_array($codingData)) {
            $codingData = [$codingData];
        }

        $codingRefs = [];
        foreach ($codingData as $key => $coding) {
            if (is_object($coding) && $coding instanceof CFHIRDataTypeCoding) {
                $codingRefs[] = $coding;
                continue;
            }
            $system  = CMbArray::get($coding, 'system', '');
            $code    = CMbArray::get($coding, 'code', '');
            $display = CMbArray::get($coding, 'display', '');

            $codingRefs[] = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        }

        $data = ['coding' => $codingRefs];
        if ($text) {
            $data['text'] = new CFHIRDataTypeString($text);
        }

        if ($reference === null) {
            return CFHIRDataTypeCodeableConcept::build($data);
        }

        return array_merge($reference, [CFHIRDataTypeCodeableConcept::build($data)]);
    }

    /**
     * Search and return coding corresponding to system and code
     *
     * @param string $system Code System, should be oid / uri
     * @param string $code   Code
     *
     * @return CFHIRDataTypeCoding|null
     */
    public function getCoding(string $system, string $code): ?CFHIRDataTypeCoding
    {
        foreach ($this->coding ?? [] as $coding) {
            if (!$coding->system || !$coding->code) {
                continue;
            }

            if ($coding->isMatch($system, $code)) {
                return $coding;
            }
        }

        return null;
    }

    /**
     * @param string $system
     * @param string $code
     *
     * @return array
     */
    public function searchCodings(string $system, string $code): array
    {
        $codings = [];

        foreach ($this->coding as $_coding) {
            if ($code && $_coding->isMatch($system, $code)) {
                $codings[] = $_coding;
            }

            if (!$code && $_coding->system->isSystemMatch($system)) {
                $codings[] = $_coding;
            }
        }

        return $codings;
    }

    /**
     * @param string      $system
     * @param string|null $code
     *
     * @return CFHIRDataTypeCoding|null
     */
    public function searchCoding(string $system, ?string $code = null): ?CFHIRDataTypeCoding
    {
        foreach ($this->coding as $_coding) {
            if ($code && $_coding->isMatch($system, $code)) {
                return $_coding;
            }

            if (!$code && $_coding->system->isSystemMatch($system)) {
                return $_coding;
            }
        }

        return null;
    }

    /**
     * @param CFHIRDataTypeCoding $coding
     *
     * @return bool
     */
    public function hasCoding(CFHIRDataTypeCoding $coding): bool
    {
        if (!isset($coding)) {
            return false;
        }

        foreach ($this->coding as $_coding) {
            if ($_coding === $coding) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param CFHIRDataTypeCoding ...$coding
     *
     * @return CFHIRDataTypeCodeableConcept
     */
    public function setCoding(CFHIRDataTypeCoding ...$coding): CFHIRDataTypeCodeableConcept
    {
        $this->coding = $coding;

        return $this;
    }

    /**
     * @param CFHIRDataTypeString $text
     *
     * @return CFHIRDataTypeCodeableConcept
     */
    public function setText(CFHIRDataTypeString $text): CFHIRDataTypeCodeableConcept
    {
        $this->text = $text;

        return $this;
    }
}
