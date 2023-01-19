<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Bundle\Profiles\ANS\CDL;

use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Profiles\CFHIRCDL;
use Ox\Interop\Fhir\Resources\R4\Bundle\CFHIRResourceBundle;
use Ox\Interop\Fhir\Resources\R4\DocumentReference\CFHIRResourceDocumentReference;
use Ox\Interop\Fhir\Resources\R4\Organization\Profiles\InteropSante\CFHIRResourceOrganizationFR;
use Ox\Interop\Fhir\Resources\R4\Patient\Profiles\InteropSante\CFHIRResourcePatientFR;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\InteropSante\CFHIRResourcePractitionerFR;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\CFHIRResourcePractitionerRole;
use Ox\Interop\Fhir\Resources\R4\RelatedPerson\CFHIRResourceRelatedPerson;

class CFHIRResourceBundleCreationNoteCdL extends CFHIRResourceBundle
{
    /** @var string */
    public const PROFILE_TYPE = "CdL_BundleCreationNoteCdL";

    /** @var string|CFHIR */
    public const PROFILE_CLASS = CFHIRCDL::class;

    /** @var CFHIRResourceDocumentReference */ // todo CFHIRResourceDocumentReferenceCdL
    private $document_reference;

    /** @var CFHIRResourcePatientFR */
    private $patient;

    /** @var CFHIRResourcePractitionerFR */
    private $practitioner;

    /** @var CFHIRResourcePractitionerRole[] */ // todo RASS
    private $practitionerRole;

    /** @var CFHIRResourceOrganizationFR */
    private $organization;

    /** @var CFHIRResourceRelatedPerson */ // todo implementer le FRRelatedPerson de Interop sante
    private $relatedPerson;

    // not implemented CFHIRResourceDevice
    //private $device;

    /**
     * @param CFHIRResourceDocumentReference $document_reference
     *
     * @return CFHIRResourceBundleCreationNoteCdL
     */
    public function setDocumentReference(CFHIRResourceDocumentReference $document_reference
    ): CFHIRResourceBundleCreationNoteCdL {
        $this->document_reference = $document_reference;

        return $this;
    }

    /**
     * @param CFHIRResourcePatientFR $patient
     *
     * @return CFHIRResourceBundleCreationNoteCdL
     */
    public function setPatient(CFHIRResourcePatientFR $patient): CFHIRResourceBundleCreationNoteCdL
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * @param CFHIRResourcePractitionerFR $practitioner
     *
     * @return CFHIRResourceBundleCreationNoteCdL
     */
    public function setPractitioner(CFHIRResourcePractitionerFR $practitioner): CFHIRResourceBundleCreationNoteCdL
    {
        $this->practitioner = $practitioner;

        return $this;
    }

    /**
     * @param CFHIRResourcePractitionerRole $practitionerRole
     *
     * @return CFHIRResourceBundleCreationNoteCdL
     */
    public function addPractitionerRole(CFHIRResourcePractitionerRole $practitionerRole
    ): CFHIRResourceBundleCreationNoteCdL {
        $this->practitionerRole[] = $practitionerRole;

        return $this;
    }

    /**
     * @param CFHIRResourceOrganizationFR $organization
     *
     * @return CFHIRResourceBundleCreationNoteCdL
     */
    public function setOrganization(CFHIRResourceOrganizationFR $organization): CFHIRResourceBundleCreationNoteCdL
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @param CFHIRResourceRelatedPerson $relatedPerson
     *
     * @return CFHIRResourceBundleCreationNoteCdL
     */
    public function setRelatedPerson(CFHIRResourceRelatedPerson $relatedPerson): CFHIRResourceBundleCreationNoteCdL
    {
        $this->relatedPerson = $relatedPerson;

        return $this;
    }
}
