<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;

/**
 * FHIR data type
 */
class CFHIRDataTypeAddress extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'Address';

    public ?CFHIRDataTypeCode $use = null;

    public ?CFHIRDataTypeCode $type = null;

    public ?CFHIRDataTypeString $text = null;

    /** @var CFHIRDataTypeString[] */
    public array $line = [];

    public ?CFHIRDataTypeString $city = null;

    public ?CFHIRDataTypeString $district = null;

    public ?CFHIRDataTypeString $state = null;

    public ?CFHIRDataTypeString $postalCode = null;

    public ?CFHIRDataTypeString $country = null;

    public ?CFHIRDataTypePeriod $period = null;

    /**
     * @param CFHIRDataTypeCode|null $use
     *
     * @return CFHIRDataTypeAddress
     */
    public function setUse(?string $use): CFHIRDataTypeAddress
    {
        $this->use = $use ? new CFHIRDataTypeString($use) : null;

        return $this;
    }

    /**
     * @param string|null $type
     *
     * @return CFHIRDataTypeAddress
     */
    public function setType(?string $type): CFHIRDataTypeAddress
    {
        $this->type = $type ? new CFHIRDataTypeCode($type) : null;

        return $this;
    }

    /**
     * @param CFHIRDataTypeString|null $text
     *
     * @return CFHIRDataTypeAddress
     */
    public function setText(?string $text): CFHIRDataTypeAddress
    {
        $this->text = $text ? new CFHIRDataTypeString($this) : null;

        return $this;
    }

    /**
     * @param string ...$line
     *
     * @return CFHIRDataTypeAddress
     */
    public function setLine(string ...$line): CFHIRDataTypeAddress
    {
        $this->line = $line;

        return $this;
    }

    /**
     * @param CFHIRDataTypeString|null $city
     *
     * @return CFHIRDataTypeAddress
     */
    public function setCity(?string $city): CFHIRDataTypeAddress
    {
        $this->city = $city ? new CFHIRDataTypeString($city) : null;

        return $this;
}

    /**
     * @param CFHIRDataTypeString|null $postalCode
     *
     * @return CFHIRDataTypeAddress
     */
    public function setPostalCode(?string $postalCode): CFHIRDataTypeAddress
    {
        $this->postalCode = $postalCode ? new CFHIRDataTypeString($postalCode) : null;

        return $this;
    }
}
