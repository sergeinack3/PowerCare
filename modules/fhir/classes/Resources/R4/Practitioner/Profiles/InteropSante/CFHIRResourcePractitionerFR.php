<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\InteropSante;

use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\R4\Practitioner\CFHIRResourcePractitioner;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Mapper\FrPractitioner;

class CFHIRResourcePractitionerFR extends CFHIRResourcePractitioner
{
    /** @var string */
    public const PROFILE_TYPE = 'FrPractitioner';

    /** @var string */
    public const PROFILE_CLASS = CFHIRInteropSante::class;

    /** @var FrPractitioner */
    protected $object_mapping;
}
