<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes;

class CFHIRDataTypeCanonical extends CFHIRDataType
{
    /** @var string */
    public const NAME = 'Canonical';

    /** @var numeric | null */
    private $version;

    /**
     * CFHIRDataTypeCanonical constructor.
     *
     * @param null $value
     * @param null $version
     */
    public function __construct($value = null, $version = null)
    {
        parent::__construct($value);

        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->version !== null ? $this->_value . '|' . $this->version : $this->_value;
    }

    /**
     * @return float|int|string|null
     */
    public function getVersion()
    {
        return $this->version;
    }
}
