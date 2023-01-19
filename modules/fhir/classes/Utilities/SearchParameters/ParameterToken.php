<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\SearchParameters;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;

class ParameterToken
{
    private ?string $code = null;

    private ?CFHIRDataTypeUri $system = null;

    private bool $system_should_be_null = false;

    /**
     * SearchParameter constructor.
     *
     * @param AbstractSearchParameter $type
     * @param mixed                   $value
     * @param string|null             $modifier
     * @param string|null             $prefix
     */
    public function __construct(string $value)
    {
        $pipe_position = strpos($value, '|');

        // [parameter]=[code] / only code given
        if ($pipe_position === false) {
            $this->code = $value;
        } elseif ($pipe_position === 0) {
            // [parameter]=|[code] / only code given but system is null
            $this->code                  = $this->withoutPipe($value);
            $this->system_should_be_null = true;
        } elseif ($pipe_position === strlen($value)) {
            // [parameter]=[system] / only system given
            $this->system = new CFHIRDataTypeUri($this->withoutPipe($value));
        } else {
            // [parameter]=[system]|[code] / System and code given
            [$system, $code] = explode('|', $value, 2);

            $this->system = new CFHIRDataTypeUri($system);
            $this->code   = $code;
        }
    }

    /**
     * @param string $value
     *
     * @return array|string|string[]
     */
    protected function withoutPipe(string $value)
    {
        return str_replace('|', '', $value);
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return string|null
     */
    public function getSystem(): ?CFHIRDataTypeUri
    {
        return $this->system;
    }

    /**
     * @return bool
     */
    public function isSystemShouldBeNull(): bool
    {
        return $this->system_should_be_null;
    }
}
