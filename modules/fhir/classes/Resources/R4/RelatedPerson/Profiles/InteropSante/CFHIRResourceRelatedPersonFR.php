<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\RelatedPerson\Profiles\InteropSante;

use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\R4\RelatedPerson\CFHIRResourceRelatedPerson;

/**
 * FHIR RelatedPerson ressource
 */
class CFHIRResourceRelatedPersonFR extends CFHIRResourceRelatedPerson
{
    /** @var string  */
    public const PROFILE_TYPE = "FrRelatedPerson";

    /** @var string  */
    public const PROFILE_CLASS = CFHIRInteropSante::class;
}
