<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\ANS;

use Ox\Interop\Fhir\Profiles\CFHIRAnnuaireSante;
use Ox\Interop\Fhir\Resources\R4\Practitioner\CFHIRResourcePractitioner;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Mapper\AnnuaireSante\PractitionerRass;

/**
 * FHIR practitioner resource
 */
class CFHIRResourcePractitionerRASS extends CFHIRResourcePractitioner
{
    // constants
    /** @var string */
    public const PROFILE_TYPE = 'practitioner-rass';

    /** @var string */
    public const PROFILE_CLASS = CFHIRAnnuaireSante::class;

    /** @var PractitionerRass */
    public $object_mapping;
}
