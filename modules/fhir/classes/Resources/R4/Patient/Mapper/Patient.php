<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Patient\Mapper;

use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\PatientMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDate;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Patient\CFHIRDataTypePatientCommunication;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Patient\CFHIRDataTypePatientContact;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\Organization\CFHIRResourceOrganization;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Fhir\Resources\R4\Practitioner\CFHIRResourcePractitioner;
use Ox\Interop\Ihe\CPDQm;
use Ox\Interop\Ihe\CPIXm;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CPatient;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Description
 */
class Patient implements DelegatedObjectMapperInterface, PatientMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CPatient */
    protected $object;

    /** @var CFHIRResourcePatient */
    protected CFHIRResource $resource;

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return void
     */
    public function setResource(CFHIRResource $resource, $object): void
    {
        $this->object   = $object;
        $this->resource = $resource;
    }

    /**
     * @param CFHIRResource $resource
     * @param               $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CPatient && $object->_id;
    }

    /**
     * @return string[]
     */
    public function onlyProfiles(): ?array
    {
        return [CFHIR::class, CPDQm::class, CPIXm::class];
    }

    public function onlyRessources(): ?array
    {
        return [CFHIRResourcePatient::RESOURCE_TYPE];
    }

    public function mapActive(): ?CFHIRDataTypeBoolean
    {
        return new CFHIRDataTypeBoolean(true);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function mapName(): array
    {
        // name
        $names = CFHIRDataTypeHumanName::addName(
            $this->object->nom_jeune_fille,
            $this->object->prenom,
            'official'
        );

        $has_usual_familly = $this->object->nom && $this->object->nom !== $this->object->nom_jeune_fille;
        if ($has_usual_familly || $this->object->prenom_usuel) {
            $names = CFHIRDataTypeHumanName::addName(
                $this->object->nom,
                $this->object->prenom_usuel ?: $this->object->prenoms,
                'usual',
                null,
                $names
            );
        }

        return $names;
    }

    /**
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function mapTelecom(): array
    {
        $telecom = [];

        if ($this->object->tel) {
            $telecom[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => 'phone',
                    "value"  => $this->object->tel,
                ]
            );
        }

        if ($this->object->tel2) {
            $telecom[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => 'phone',
                    "value"  => $this->object->tel2,
                ]
            );
        }

        if ($this->object->tel_autre) {
            $telecom[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => 'phone',
                    "value"  => $this->object->tel_autre,
                ]
            );
        }

        if ($this->object->tel_autre_mobile) {
            $telecom[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => 'phone',
                    "value"  => $this->object->tel_autre_mobile,
                ]
            );
        }

        if ($this->object->tel_pro) {
            $telecom[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => 'phone',
                    "value"  => $this->object->tel_pro,
                ]
            );
        }

        if ($this->object->email) {
            $telecom[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => 'email',
                    "value"  => $this->object->email,
                ]
            );
        }

        return $telecom;
    }

    public function mapGender(): ?CFHIRDataTypeCode
    {
        return new CFHIRDataTypeCode($this->resource->formatGender($this->object->sexe));
    }

    public function mapBirthDate(): ?CFHIRDataTypeDate
    {
        return new CFHIRDataTypeDate($this->object->naissance);
    }

    public function mapDeceased(): ?CFHIRDataTypeChoice
    {
        if (!$this->object->deces) {
            return null;
        }

        return new CFHIRDataTypeChoice(CFHIRDataTypeDateTime::class, $this->object->deces);
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function mapAddress(): array
    {
        if (!$this->object->adresse && !$this->object->ville && !$this->object->cp) {
            return [];
        }

        return [
            CFHIRDataTypeAddress::build(
                [
                    'use'        => 'home',
                    'type'       => 'postal',
                    'line'       => preg_split('/[\r\n]+/', $this->object->adresse) ?? null,
                    'city'       => $this->object->ville ?? null,
                    'postalCode' => $this->object->cp ?? null,
                ]
            ),
        ];
    }

    public function mapMaritalStatus(): ?CFHIRDataTypeCodeableConcept
    {
        $system = 'http://terminology.hl7.org/CodeSystem/v3-MaritalStatus';

        switch ($this->object->situation_famille) {
            case 'M':
                $code    = 'M';
                $display = 'Married';
                $text    = 'A current marriage contract is active';
                break;

            case 'G':
                $code    = 'T';
                $display = 'Domestic partner';
                $text    = 'Person declares that a domestic partner relationship exists.';
                break;

            case 'D':
                $code    = 'D';
                $display = 'Divorced';
                $text    = 'Marriage contract has been declared dissolved and inactive';
                break;

            case 'W':
                $code    = 'W';
                $display = 'Widowed';
                $text    = 'The spouse has died';
                break;

            case 'A':
                $code    = 'L';
                $display = 'Legally Separated';
                $text    = 'Legally Separated';
                break;

            case 'S':
                $code    = 'U';
                $display = 'unmarried';
                $text    = 'Currently not in a marriage contract.';
                break;

            default:
                $system  = 'http://terminology.hl7.org/CodeSystem/v3-NullFlavor';
                $code    = 'UNK';
                $display = 'unknown';
                $text    = '';
                break;
        }

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);

        return CFHIRDataTypeCodeableConcept::addCodeable($coding, $text);
    }

    public function mapMultipleBirth(): ?CFHIRDataTypeChoice
    {
        // not implemented
        return null;
    }

    public function mapPhoto(): array
    {
        // not implemented
        return [];
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function mapContact(): array
    {
        $contacts = [];

        /** @var CCorrespondantPatient[] */
        $correspondantsPatient = $this->object->loadRefsCorrespondantsPatient();

        foreach ($correspondantsPatient as $_correspondant) {
            $relationship = $this->getContactRelationship($_correspondant);

            $name = $this->getContactName($_correspondant);

            $telecom = $this->getContactTelecom($_correspondant);

            $address = $this->getContactAddress($_correspondant);

            $gender = new CFHIRDataTypeCode($this->resource->formatGender($_correspondant->sex));

            $organization = null;

            $period = null;

            $contacts[] = CFHIRDataTypePatientContact::build(
                [
                    'relationship' => $relationship,
                    'name'         => $name,
                    'telecom'      => $telecom,
                    'address'      => $address,
                    'gender'       => $gender,
                    'organization' => $organization,
                    'period'       => $period,
                ]
            );
        }

        return $contacts;
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function mapCommunication(): array
    {
        $system  = 'urn:oid:2.16.840.1.113883.4.642.3.20';
        $code    = 'fr-FR';
        $display = 'French (France)';

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $text   = 'Français de France';

        $language = CFHIRDataTypeCodeableConcept::addCodeable($coding, $text);

        $preferred = new CFHIRDataTypeBoolean(true);

        return [CFHIRDataTypePatientCommunication::build(['language' => $language, 'preferred' => $preferred])];
    }

    public function mapGeneralPractitioner(string $resource_class = CFHIRResourcePractitioner::class): array
    {
        $resource_class = CFHIRResourcePractitioner::class;

        $medecin = $this->object->loadRefMedecinTraitant();

        if (!$medecin->_id) {
            return [];
        }

        return [$this->resource->addReference($resource_class, $medecin)];
    }

    public function mapManagingOrganization(string $resource_class = CFHIRResourceOrganization::class):
    ?CFHIRDataTypeReference {
        $group = $this->object->loadRefGroup();

        if (!$group->_id) {
            return null;
        }

        return $this->resource->addReference($resource_class, $group);
    }

    public function mapLink(): array
    {
        return [];
    }

    /**
     * @param CCorrespondantPatient $_correspondant_patient
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    private function getContactRelationship(CCorrespondantPatient $_correspondant_patient): array
    {
        $system = 'urn:oid:2.16.840.1.113883.4.642.3.1130';
        $text   = 'The nature of the relationship between the patient and the contact person.';

        switch ($_correspondant_patient->relation) {
            case 'assurance':
                $code    = 'I';
                $display = 'Insurance Company';
                break;

            case 'employeur':
                $code    = 'E';
                $display = 'Employer';
                break;

            case 'parent_proche':
                $code    = 'N';
                $display = 'Next-of-Kin';
                break;

            default:
                $code    = 'U';
                $display = 'Unknown';
                break;
        }

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);

        return [CFHIRDataTypeCodeableConcept::addCodeable($coding, $text)];
    }

    /**
     * @param CCorrespondantPatient $_correspondant_patient
     *
     * @return CFHIRDataTypeHumanName
     * @throws InvalidArgumentException
     */
    protected function getContactName(CCorrespondantPatient $_correspondant_patient): ?CFHIRDataTypeHumanName
    {
        if ($_correspondant_patient->nom_jeune_fille) {
            $names = CFHIRDataTypeHumanName::addName(
                $_correspondant_patient->nom_jeune_fille,
                [$_correspondant_patient->prenom],
                'official'
            );
        } elseif ($_correspondant_patient->nom) {
            $names = CFHIRDataTypeHumanName::addName(
                $_correspondant_patient->nom,
                [$_correspondant_patient->prenom],
                'usual'
            );
        }

        return $names[0] ?? null;
    }

    /**
     * @param CCorrespondantPatient $_correspondant_patient
     *
     * @return CFHIRDataTypeContactPoint[]
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    protected function getContactTelecom(CCorrespondantPatient $_correspondant_patient): array
    {
        $contactPoints = [];

        if ($_correspondant_patient->tel) {
            $contactPoints[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => 'phone',
                    "value"  => $_correspondant_patient->tel,
                ]
            );
        }

        if ($_correspondant_patient->tel_autre) {
            $contactPoints[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => 'phone',
                    "value"  => $_correspondant_patient->tel_autre,
                ]
            );
        }

        if ($_correspondant_patient->mob) {
            $contactPoints[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => 'phone',
                    "value"  => $_correspondant_patient->mob,
                ]
            );
        }

        if ($_correspondant_patient->fax) {
            $contactPoints[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => 'phone',
                    "value"  => $_correspondant_patient->fax,
                ]
            );
        }

        if ($_correspondant_patient->email) {
            $contactPoints[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => 'email',
                    "value"  => $_correspondant_patient->email,
                ]
            );
        }

        return $contactPoints;
    }

    /**
     * @param CCorrespondantPatient $_correspondant_patient
     *
     * @return CFHIRDataTypeAddress | null
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    protected function getContactAddress(CCorrespondantPatient $_correspondant_patient): ?CFHIRDataTypeAddress
    {
        if ($_correspondant_patient->adresse || $_correspondant_patient->ville || $_correspondant_patient->cp) {
            return CFHIRDataTypeAddress::build(
                [
                    'use'        => new CFHIRDataTypeString('work'),
                    'type'       => new CFHIRDataTypeString('both'),
                    'line'       => preg_split('/[\r\n]+/', $_correspondant_patient->adresse) ?? null,
                    'city'       => $_correspondant_patient->ville ?? null,
                    'postalCode' => $_correspondant_patient->cp ?? null,
                ]
            );
        }

        return null;
    }
}
