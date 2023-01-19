<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use Ox\Core\CMbArray;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;

/**
 * FHIR data type
 */
class CFHIRDataTypeCoding extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'Coding';

    /** @var CFHIRDataTypeUri */
    public $system;

    /** @var CFHIRDataTypeString */
    public $version;

    /** @var CFHIRDataTypeCode */
    public $code;

    /** @var CFHIRDataTypeString */
    public $display;

    /** @var CFHIRDataTypeBoolean */
    public $userSelected;

    /**
     * Get from values which come from values set
     *
     * @param array $values
     *
     * @return self
     */
    public static function fromValues(array $values): self
    {
        $self = new self();

        if ($code = CMbArray::get($values, 'code')) {
            $self->code = new CFHIRDataTypeCode($code);
        }

        if ($code_system = CMbArray::get($values, 'codeSystem')) {
            $self->system = new CFHIRDataTypeUri($code_system);
        }

        if ($display_name = CMbArray::get($values, 'displayName')) {
            $self->display = new CFHIRDataTypeString($display_name);
        }

        return $self;
    }

    /**
     * @param string     $system
     * @param string     $code
     * @param string     $displayName
     * @param array|null $reference
     *
     * @return CFHIRDataTypeCoding[] | CFHIRDataTypeCoding
     */
    public static function addCoding(string $system, string $code, string $displayName, ?array $reference = null)
    {
        $data = [
            "system"  => new CFHIRDataTypeString($system),
            "code"    => new CFHIRDataTypeCode($code),
            "display" => new CFHIRDataTypeString($displayName),
        ];

        if ($reference === null) {
            return CFHIRDataTypeCoding::build($data);
        }

        return array_merge($reference, [CFHIRDataTypeCoding::build($data)]);
    }

    /**
     * Check if System and code given match with actual coding
     *
     * @param string $system
     * @param string $code
     *
     * @return bool
     */
    public function isMatch(string $system, string $code): bool
    {
        if (!$this->code || !$this->system) {
            return false;
        }

        return $this->system->isSystemMatch($system) && $this->code->getValue() === $code;
    }

    /**
     * @param string $system
     *
     * @return CFHIRDataTypeCoding
     */
    public function setSystem(?string $system): CFHIRDataTypeCoding
    {
        $this->system = $system ? new CFHIRDataTypeUri($system) : null;

        return $this;
    }

    /**
     * @param string|null $version
     *
     * @return CFHIRDataTypeCoding
     */
    public function setVersion(?string $version): CFHIRDataTypeCoding
    {
        $this->version = $version ? new CFHIRDataTypeString($version) : null;

        return $this;
    }

    /**
     * @param string $code
     *
     * @return CFHIRDataTypeCoding
     */
    public function setCode(string $code): CFHIRDataTypeCoding
    {
        $this->code = $code ? new CFHIRDataTypeCode($code) : null;

        return $this;
    }

    /**
     * @param string|null $display
     *
     * @return CFHIRDataTypeCoding
     */
    public function setDisplay(?string $display): CFHIRDataTypeCoding
    {
        $this->display = $display ? new CFHIRDataTypeString($display) : null;

        return $this;
    }

    /**
     * @param bool $userSelected
     *
     * @return CFHIRDataTypeCoding
     */
    public function setUserSelected(bool $userSelected): CFHIRDataTypeCoding
    {
        $this->userSelected = $userSelected;

        return $this;
    }
}
