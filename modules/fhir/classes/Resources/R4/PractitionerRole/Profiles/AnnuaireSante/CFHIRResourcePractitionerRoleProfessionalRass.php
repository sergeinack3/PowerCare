<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\PractitionerRole\Profiles\AnnuaireSante;

use Ox\Interop\Fhir\Profiles\CFHIRAnnuaireSante;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\CFHIRResourcePractitionerRole;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\PractitionerRoleMappingInterface;

class CFHIRResourcePractitionerRoleProfessionalRass extends CFHIRResourcePractitionerRole
{
    // constants
    /** @var string */
    public const PROFILE_TYPE = 'practitionerRole-professionalRole-rass';

    /** @var string */
    public const PROFILE_CLASS = CFHIRAnnuaireSante::class;

    protected function mapOrganization(): void
    {
        // forbidden in this profile
        $this->organization = null;
    }

    protected function mapLocation(): void
    {
        // forbidden in this profile
        $this->location = [];
    }

    protected function mapHealthCareService(): void
    {
        // forbidden in this profile
        $this->healthcareService = [];
    }

    protected function mapAvailableTime(): void
    {
        // forbidden in this profile
        $this->availableTime = [];
    }

    protected function mapNotAvailable(): void
    {
        // forbidden in this profile
        $this->notAvailable = [];
    }

    protected function mapAvailabilityExceptions(): void
    {
        // forbidden in this profile
        $this->availabilityExceptions = null;
    }

    protected function mapEndpoint(): void
    {
        // forbidden in this profile
        $this->endpoint = [];
    }
}
