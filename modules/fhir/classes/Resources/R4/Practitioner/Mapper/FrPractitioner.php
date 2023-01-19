<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Practitioner\Mapper;

use Exception;
use Ox\Core\CMbArray;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\InteropSante\CFHIRResourcePractitionerFR;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CMedecinExercicePlace;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Description
 */
class FrPractitioner extends Practitioner
{
    /** @var CMedecin */
    protected $object;

    /** @var CFHIRResourcePractitionerFR */
    protected CFHIRResource $resource;

    public function onlyProfiles(): array
    {
        return [CFHIRInteropSante::class];
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function mapIdentifier(): array
    {
        $identifiers = parent::mapIdentifier();

        /** @var CMedecinExercicePlace[] $medecin_exercice_places */
        $medecin_exercice_places = $this->object->getMedecinExercicePlaces();
        $adeli                   = array_unique(CMbArray::pluck($medecin_exercice_places, "adeli"))[0];

        // ADELI
        if ($adeli || $this->object->adeli) {
            $coding = (new CFHIRDataTypeCoding())
                ->setCode('ADELI')
                ->setDisplay('N° ADELI')
                ->setSystem('http://interopsante.org/fhir/CodeSystem/fr-v2-0203');
            $type   = (new CFHIRDataTypeCodeableConcept())
                ->setCoding($coding)
                ->setText(
                    (new CFHIRDataTypeString('N° ADELI'))
                );

            $identifiers[] = CFHIRDataTypeIdentifier::makeIdentifier(
                $adeli ?? $this->object->adeli,
                'urn:oid:1.2.250.1.71.4.2.1'
            )
                ->setUse(new CFHIRDataTypeCode('official'))
                ->setType($type);
        }

        // RPPS
        if ($this->object->rpps) {
            $coding = (new CFHIRDataTypeCoding())
                ->setCode('RPPS')
                ->setSystem('http://interopsante.org/fhir/CodeSystem/fr-v2-0203')
                ->setDisplay('N° RPPS');
            $type = CFHIRDataTypeCodeableConcept::addCodeable($coding, 'N° RPPS');

            $identifiers[] = CFHIRDataTypeIdentifier::makeIdentifier($this->object->rpps, 'urn:oid:1.2.250.1.71.4.2.1')
                ->setType($type)
                ->setUse(new CFHIRDataTypeCode('official'));
        }

        return $identifiers;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function mapName(): array
    {
        // TODO PASSER EN FrHumanName
        return CFHIRDataTypeHumanName::addName(
            $this->object->nom,
            $this->object->prenom,
            'usual',
            $this->object->_view
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function mapTelecom(): array
    {
        // TODO PASSER EN FrContactPoint
        $telecoms = [];
        if ($this->object->tel) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => "phone",
                    "value"  => $this->object->tel,
                ]
            );
        }

        if ($this->object->tel_autre) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => "phone",
                    "value"  => $this->object->tel_autre,
                ]
            );
        }

        if ($this->object->fax) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => "fax",
                    "value"  => $this->object->fax,
                ]
            );
        }

        if ($this->object->portable) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => "portable",
                    "value"  => $this->object->portable,
                ]
            );
        }

        if ($this->object->email) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => "email",
                    "value"  => $this->object->email,
                ]
            );
        }

        return $telecoms;
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function mapAddress(): array
    {
        // TODO PASSER EN FrAddress
        if ($this->object->adresse || $this->object->ville || $this->object->cp) {
            return [
                CFHIRDataTypeAddress::build(
                    [
                        'use'        => 'work',
                        'type'       => 'postal',
                        'line'       => preg_split('/[\r\n]+/', $this->object->adresse) ?? null,
                        'city'       => $this->object->ville ?? null,
                        'postalCode' => $this->object->cp ?? null,
                    ]
                ),
            ];
        }

        return [];
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function mapQualification(): array
    {
        // TODO Mapper avec les spécialités française
        return parent::mapQualification();
    }
}
