<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Patient\Profiles\InteropSante;

use Ox\Core\CStoredObject;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\PatientMappingInterface;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionDelete;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Fhir\Resources\R4\Patient\Mapper\FrPatient;
use Ox\Interop\Fhir\Resources\R4\Patient\PatientInterface;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\InteropSante\CFHIRResourcePractitionerFR;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;

/**
 * FHIR patient resource
 */
class CFHIRResourcePatientFR extends CFHIRResourcePatient
{
    // constants
    /** @var string */
    public const PROFILE_TYPE = 'FrPatient';

    /** @var string */
    public const PROFILE_CLASS = CFHIRInteropSante::class;

    /** @var PatientMappingInterface */
    protected $object_mapping;

    /**
     * @return CCapabilitiesResource
     */
    public function generateCapabilities(): CCapabilitiesResource
    {
        return (parent::generateCapabilities())
            ->addInteractions(
                [
                    CFHIRInteractionCreate::NAME,
                    CFHIRInteractionRead::NAME,
                    CFHIRInteractionSearch::NAME,
                    CFHIRInteractionUpdate::NAME,
                    CFHIRInteractionDelete::NAME,
                ]
            );
    }

    /**
     * @param CStoredObject $object
     *
     * @return DelegatedObjectMapperInterface
     */
    protected function setMapperOld(CStoredObject $object): DelegatedObjectMapperInterface
    {
        $mapping_object = FrPatient::class;

        return new $mapping_object();
    }

    /**
     * Map property generalPractitioner
     *
     */
    protected function mapGeneralPractitioner(): void
    {
        $this->object_mapping->mapGeneralPractitioner(CFHIRResourcePractitionerFR::class);
    }
}
