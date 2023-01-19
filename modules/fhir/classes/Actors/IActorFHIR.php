<?php

/**
 * @package Mediboard\Fhir\Actors
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Actors;

use Exception;
use Ox\Interop\Eai\CMessageSupported;
use Ox\Interop\Fhir\ClassMap\FHIRClassMap;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectHandleInterface;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectSearcherInterface;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Ox\Interop\Fhir\Resources\CFHIRResource;

interface IActorFHIR
{
    /**
     * @return CFHIRResource[]
     */
    public function getAvailableResources(): array;

    /**
     * @param CFHIRResource $resource
     *
     * @return string[]
     */
    public function getAvailableInteractions(CFHIRResource $resource): array;

    /**
     * @param CFHIRResource $resource
     *
     * @return string[]
     */
    public function getAvailableProfiles(CFHIRResource $resource): array;

    /**
     * @param string $profile
     *
     * @return CFHIRResource|null
     */
    public function getResource(string $profile): ?CFHIRResource;

    /**
     * @param CFHIRResource         $resource
     * @param CFHIRInteraction[] $interactions
     *
     * @return CMessageSupported[]
     */
    public function getMessagesSupportedForResource(CFHIRResource $resource, array $interactions = []): array;

    /**
     * Get classmap for only resources active for the actor
     *
     * @return FHIRClassMap
     */
    public function getActorClassMap(): FHIRClassMap;

    /**
     * @return DelegatedObjectHandleInterface|null
     * @throws Exception
     */
    public function getDelegatedHandle($resource_or_canonical): ?DelegatedObjectHandleInterface;

    /**
     * @return DelegatedObjectSearcherInterface|null
     * @throws Exception
     */
    public function getDelegatedSearcher($resource_or_canonical): ?DelegatedObjectSearcherInterface;

    /**
     * @param string|CFHIRResource $resource_or_canonical
     *
     * @return DelegatedObjectMapperInterface|null
     */
    public function getDelegatedMapper($resource_or_canonical): ?DelegatedObjectMapperInterface;
}
