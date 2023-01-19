<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Practitioner\Mapper\AnnuaireSante;

use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Mapper\PractitionerMedecin;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\ANS\CFHIRResourcePractitionerRASS;
use Ox\Mediboard\Patients\CMedecin;

class PractitionerRass extends PractitionerMedecin
{
    /** @var CMedecin */
    protected $object;

    /** @var CFHIRResourcePractitionerRASS */
    protected CFHIRResource $resource;
}
