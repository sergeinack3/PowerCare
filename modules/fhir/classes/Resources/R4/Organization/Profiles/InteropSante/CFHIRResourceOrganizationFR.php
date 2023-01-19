<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Organization\Profiles\InteropSante;

use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\R4\Organization\CFHIRResourceOrganization;

/**
 * FHIR organization resource
 */
class CFHIRResourceOrganizationFR extends CFHIRResourceOrganization
{
    // constants
    /** @var string */
    public const PROFILE_TYPE = 'FrOrganization';

    /** @var string */
    public const PROFILE_CLASS = CFHIRInteropSante::class;
}
