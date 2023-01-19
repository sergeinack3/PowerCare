<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\PractitionerRole\Profiles\AnnuaireSante;

use Exception;
use Ox\Interop\Fhir\Profiles\CFHIRAnnuaireSante;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\CFHIRResourcePractitionerRole;

class CFHIRResourcePractitionerRoleOrganizationalRass extends CFHIRResourcePractitionerRole
{
    // constants
    /** @var string */
    public const PROFILE_TYPE = 'practitionerRole-organizationalRole-rass';

    /** @var string */
    public const PROFILE_CLASS = CFHIRAnnuaireSante::class;

    /**
     * Map property extension
     * @throws Exception
     */
    protected function mapPractitioner(): void
    {
        // forbidden in this profile
        $this->practitioner = null;
    }

    protected function mapSpecialty(): void
    {
        // forbidden in this profile
        $this->specialty = [];
    }

    protected function mapHealthCareService(): void
    {
        // forbidden in this profile
        $this->healthcareService = [];
    }

    protected function mapAvailabilityExceptions(): void
    {
        // forbidden in this profile
        $this->availabilityExceptions = null;
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

    protected function mapEndpoint(): void
    {
        // forbidden in this profile
        $this->endpoint = [];
    }
}
