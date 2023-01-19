<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\DocumentReference\Profiles\ANS;

use Ox\Interop\Fhir\Profiles\CFHIRCDL;
use Ox\Interop\Fhir\Resources\R4\DocumentReference\CFHIRResourceDocumentReference;

/**
 * FIHR document reference resource
 */
class CFHIRResourceDocumentReferenceCdL extends CFHIRResourceDocumentReference
{
    // constants
    /** @var string */
    public const PROFILE_TYPE = 'CdL_DocumentReferenceCdL';

    /** @var string */
    public const PROFILE_CLASS = CFHIRCDL::class;


    protected function mapDocStatus(): void
    {
        // Forbidden for this profile
        $this->docStatus = null;
    }

    protected function mapAuthenticator(): void
    {
        // Forbidden for this profile
        $this->authenticator = null;
    }

    protected function mapCustodian(): void
    {
        // Forbidden for this profile
        $this->custodian = null;
    }

    protected function mapSecurityLabel(): void
    {
        parent::mapSecurityLabel();

        // on garde que le premier
        if ($this->securityLabel) {
            $this->securityLabel = [reset($this->securityLabel)];
        }
    }
}
