<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Schedule\Mapper;

use Exception;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceTrait;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\InteropSante\CFHIRResourcePractitionerFR;
use Ox\Interop\Fhir\Resources\R4\Schedule\Profiles\InteropSante\CFHIRResourceScheduleFR;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Patients\CMedecin;

/**
 * Description
 */
class FrSchedule extends Schedule
{
    use CStoredObjectResourceTrait;

    /** @var CPlageconsult */
    protected $object;

    /** @var CFHIRResourceScheduleFR */
    protected CFHIRResource $resource;

    public function onlyProfiles(): array
    {
        return [CFHIRInteropSante::class];
    }

    public function mapExtension(): array
    {
        return [
            CFHIRDataTypeExtension::addExtension(
                'http://interopsante.org/fhir/StructureDefinition/FrScheduleAvailabiltyTime',
                [
                    CFHIRDataTypeExtension::addExtension(
                        'type',
                        [
                            'value' => CFHIRDataTypeCoding::addCoding(
                                'http://interopsante.org/fhir/CodeSystem/fr-schedule-type',
                                $this->object->locked ? 'busy-unavailable' : 'free',
                                $this->object->locked ? 'Indisponibilité' : 'Disponibilité'
                            ),
                        ]
                    ),
                    // TODO Extension en erreur à corriger
                    /*$this->formatExtension(
                        'rrule',
                        [
                            'extension' => $this->loadRuleExtensions()
                        ]
                    ),*/
                    CFHIRDataTypeExtension::addExtension(
                        'start',
                        [
                            'valueDateTime' => $this->object->debut,
                        ]
                    ),
                    CFHIRDataTypeExtension::addExtension(
                        'end',
                        [
                            'valueDateTime' => $this->object->fin,
                        ]
                    ),
                    CFHIRDataTypeExtension::addExtension(
                        'identifier',
                        [
                            'value' => $this->resource->getIdentifier()[0],
                        ]
                    ),
                ]
            ),
        ];
    }

    /**
     * @throws Exception
     */
    public function mapSpecialty(): array
    {
        $specialty = [];

        $practitioner = $this->object->loadRefChir();

        if ($practitioner && $practitioner->_id) {
            $coding = $this->resource->setPractitionerSpecialty($practitioner);

            $specialty[] = CFHIRDataTypeCodeableConcept::addCodeable($coding);
        }

        return $specialty;
    }

    /**
     * Map property actor
     * @throws Exception
     */
    public function mapActor(): array
    {
        // Les actors sont à dynamiser en fonction du besoin, pour le projectathon nous avons échange un practitioner
        $practitioner  = $this->object->loadRefChir();
        $medecin       = new CMedecin();
        $medecin->rpps = $practitioner->rpps;
        $medecin->loadMatchingObject();

        return $this->resource->addReference(CFHIRResourcePractitionerFR::class, $medecin);
    }

    /**
     * @return CFHIRDataTypeExtension[]
     */
    private function loadRuleExtensions(): array
    {
        $until    = $this->object->fin;
        $interval = $this->object->_freq_minutes;
        $count    = 60 / $interval;

        return [
            CFHIRDataTypeExtension::addExtension(
                'rrule',
                [
                    CFHIRDataTypeExtension::addExtension(
                        'freq',
                        [
                            'value' => CFHIRDataTypeCoding::addCoding(
                                'https://www.ietf.org/rfc/rfc2445',
                                'MINUTELY',
                                'Par minute'
                            ),
                        ]
                    ),
                    CFHIRDataTypeExtension::addExtension(
                        'until',
                        [
                            'valueDateTime' => $until,
                        ]
                    ),
                    CFHIRDataTypeExtension::addExtension(
                        'count',
                        [
                            'valueInteger' => $count,
                        ]
                    ),
                    CFHIRDataTypeExtension::addExtension(
                        'interval',
                        [
                            'valueInteger' => $interval,
                        ]
                    ),
                ]
            ),
        ];
    }
}
