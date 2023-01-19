<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Ox\Interop\Fhir\Resources\R4\Device\Profiles\PHD\CFHIRResourceDevicePHD;

class CPHD extends CIHEFHIR
{
    /** @var string */
    public const DOMAIN_TYPE = "PHD";

    /** @var string */
    public const BASE_PROFILE = 'http://hl7.org/fhir/uv/phd/StructureDefinition/';

    /**
     * CFHIRPHD constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->_categories = $this->makeExplicitCategories($this::listResourceCanonicals());
    }

    public static function listResourceCanonicals(): array
    {
        return [
            CFHIRResourceDevicePHD::getCanonical()
        ];
    }
}
