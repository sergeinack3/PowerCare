<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes;

/**
 * FHIR data type
 */
class CFHIRDataTypeBase64Binary extends CFHIRDataType
{
    /** @var string */
    public const NAME = 'Base64Binary';

    /** @var string|null */
    private ?string $encoded_data = null;

    /**
     * @return string|null
     */
    public function getValue()
    {
        if ($this->_value === null || $this->_value === '') {
            $this->encoded_data = null;

            return $this->_value;
        }

        if ($this->encoded_data) {
            return $this->encoded_data;
        }

        return $this->encoded_data = base64_encode($this->_value);
    }

    /**
     * Get decoded data
     *
     * @return string|null
     */
    public function getDecodedData(): ?string
    {
        if ($this->_value) {
            return base64_decode($this->_value);
        }

        return null;
    }
}
