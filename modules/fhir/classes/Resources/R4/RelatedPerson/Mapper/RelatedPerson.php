<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\RelatedPerson\Mapper;

use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\RelatedPersonMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDate;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Patient\CFHIRDataTypePatientCommunication;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Fhir\Resources\R4\RelatedPerson\CFHIRResourceRelatedPerson;
use Ox\Mediboard\Patients\CCorrespondantPatient;

/**
 * Description
 */
class RelatedPerson implements DelegatedObjectMapperInterface, RelatedPersonMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CCorrespondantPatient */
    protected $object;

    /** @var CFHIRResourceRelatedPerson */
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
     * @return string[]
     */
    public function onlyProfiles(): array
    {
        return [CFHIR::class];
    }

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceRelatedPerson::class];
    }

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CCorrespondantPatient && $object->_id;
    }

    /**
     * @inheritDoc
     */
    public function mapActive(): ?CFHIRDataTypeBoolean
    {
        return new CFHIRDataTypeBoolean(true);
    }

    /**
     * @inheritDoc
     */
    public function mapPatient(): ?CFHIRDataTypeReference
    {
        $patient = $this->object->loadRefPatient();

        return $this->resource->addReference(CFHIRResourcePatient::class, $patient);
    }

    /**
     * @inheritDoc
     */
    public function mapRelationship(): array
    {
        $relation = $this->object->parente;
        $system   = 'urn:oid:2.16.840.1.113883.4.642.3.449';

        //TODO Etayer les relations
        switch ($relation) {
            case 'mere':
                $code    = 'MTH';
                $display = 'mother';
                break;
            case 'pere':
                $code    = 'FTH';
                $display = 'father';
                break;
            default:
                $code    = 'CHILD';
                $display = 'child';
                break;
        }

        $text   = $display;
        $coding = (new CFHIRDataTypeCoding())
            ->setCode($code)
            ->setDisplay($display)
            ->setSystem($system);

        return [
            (new CFHIRDataTypeCodeableConcept())
                ->setCoding($coding)
                ->setText(new CFHIRDataTypeString($text)),
        ];
    }

    /**
     * @inheritDoc
     */
    public function mapName(): array
    {
        $names = [];
        // name
        $names[] = (new CFHIRDataTypeHumanName())
            ->setFamily($this->object->nom_jeune_fille ?: $this->object->nom)
            ->setGiven($this->object->prenom)
            ->setUse('official');

        $has_usual_familly = $this->object->nom && $this->object->nom !== $this->object->nom_jeune_fille;
        if ($has_usual_familly) {
            $names[] = (new CFHIRDataTypeHumanName())
                ->setFamily($this->object->nom)
                ->setGiven($this->object->prenom)
                ->setUse('usual');
        }

        return $names;
    }

    /**
     * @inheritDoc
     */
    public function mapTelecom(): array
    {
        $telecom = [];
        if ($this->object->tel) {
            $telecom[] = (new CFHIRDataTypeContactPoint())
                ->setSystem('phone')
                ->setValue($this->object->tel);
        }

        if ($this->object->tel_autre) {
            $telecom[] = (new CFHIRDataTypeContactPoint())
                ->setValue($this->object->tel_autre)
                ->setSystem('phone');
        }

        if ($this->object->mob) {
            $telecom[] = (new CFHIRDataTypeContactPoint())
                ->setSystem('phone')
                ->setValue($this->object->mob);
        }

        if ($this->object->fax) {
            $telecom[] = (new CFHIRDataTypeContactPoint())
                ->setValue($this->object->fax)
                ->setSystem('fax');
        }

        if ($this->object->email) {
            $telecom[] = (new CFHIRDataTypeContactPoint())
                ->setSystem('email')
                ->setValue($this->object->email);
        }

        return $telecom;
    }

    /**
     * @inheritDoc
     */
    public function mapGender(): ?CFHIRDataTypeCode
    {
        return new CFHIRDataTypeCode($this->resource->formatGender($this->object->sex));
    }

    /**
     * @inheritDoc
     */
    public function mapBirthDate(): ?CFHIRDataTypeDate
    {
        return new CFHIRDataTypeDate($this->object->naissance);
    }

    /**
     * @inheritDoc
     */
    public function mapAddress(): array
    {
        $address = [];

        if ($this->object->adresse || $this->object->ville || $this->object->cp) {
            $address[] = (new CFHIRDataTypeAddress())
                ->setUse('work')
                ->setType('postal')
                ->SetLine(preg_split('/[\r\n]+/', $this->object->adresse) ?? '')
                ->setCity($this->object->ville ?? null)
                ->setPostalCode('postalCode');
        }

        return $address;
    }

    /**
     * @inheritDoc
     */
    public function mapPhoto(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapPeriod(): ?CFHIRDataTypePeriod
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapCommunication(): array
    {
        $coding = (new CFHIRDataTypeCoding())
            ->setSystem('urn:oid:2.16.840.1.113883.4.642.3.20')
            ->setDisplay('French (France)')
            ->setCode('fr-FR');

        $codeable = (new CFHIRDataTypeCodeableConcept())
            ->setCoding($coding)
            ->setText(new CFHIRDataTypeString('Français de France'));

        $patientCommunication            = new CFHIRDataTypePatientCommunication();
        $patientCommunication->language  = $codeable;
        $patientCommunication->preferred = new CFHIRDataTypeBoolean(true);

        return [$patientCommunication];
    }
}
