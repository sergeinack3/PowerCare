<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Profiles;

class CFHIRInteropSante extends CFHIR
{
    public const DOMAIN_NAME = "InteropSante";
    public const DOMAIN_TYPE = "CORE-FR";

    /** @var string */
    public const BASE_PROFILE = 'http://interopsante.org/fhir/StructureDefinition/';
}
