<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Profiles;

class CFHIRMES extends CFHIR
{
    /** @var string */
    public const DOMAIN_NAME = "ANS";
    /** @var string */
    public const DOMAIN_TYPE = "MES";

    /** @var string */
    public const BASE_PROFILE = 'http://esante.gouv.fr/ci-sis/fhir/StructureDefinition/';
}
