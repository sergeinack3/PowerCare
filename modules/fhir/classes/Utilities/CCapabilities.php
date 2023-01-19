<?php

/**
 * @package Mediboard\Fhir\Objects
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities;

use Exception;
use Ox\Core\CMbArray;
use Ox\Interop\Fhir\Actors\IActorFHIR;

class CCapabilities
{
    /** @var string */
    private $fhirVersion;

    /** @var CCapabilitiesResource[] */
    private $resources = [];

    /** @var CCapabilitiesResource[] */
    private $_resources_managed = [];

    /**
     * @param array $data
     *
     * @return $this
     */
    public function addResources(array $data): self
    {
        foreach ($data as $resource) {
            $this->resources[] = new CCapabilitiesResource($resource);
        }

        return $this;
    }

    /**
     * @param IActorFHIR $actor
     *
     * @return CCapabilitiesResource[]
     * @throws Exception
     */
    public function getResourcesNotActivated(IActorFHIR $actor): array
    {
        // resources gérées par l'actor
        $resources_managed = $this->_resources_managed ?: $this->getResourcesManaged($actor);

        $resources = [];
        foreach ($this->resources as $capabilities_resource) {
            if (!$resource = $capabilities_resource->getResource($this->fhirVersion)) {
                continue;
            }

            // si resource existe mais qu'il ne gère aucune intéractions
            if (!$resource->getInteractions()) {
                continue;
            }

            // find CCapabilitiesResource in $resources_managed
            /** @var CCapabilitiesResource $capability_managed */
            $capability_managed = $this->getResource(
                $resources_managed,
                $capabilities_resource->getType(),
                $capabilities_resource->getProfile()
            );
            if ($capability_managed) {
                $interactions = array_diff(
                    array_intersect(
                        $resource->getInteractions(),
                        $capabilities_resource->getInteractions(),
                    ),
                    $capability_managed->getInteractions()
                );

                $supportedProfiles = array_diff(
                    array_intersect(
                        $resource->getProfiles($resource->findProfiles()),
                        $capabilities_resource->getSupportedProfiles()
                    ),
                    $capability_managed->getSupportedProfiles(),
                );
            } else {
                $interactions      = array_intersect(
                    $resource->getInteractions(),
                    $capabilities_resource->getInteractions()
                );
                $supportedProfiles = array_intersect(
                    $resource->getProfiles($resource->findProfiles()),
                    $capabilities_resource->getSupportedProfiles()
                );
            }

            // Pa
            if (!$interactions) {
                continue;
            }

            $new_capabilities = new CCapabilitiesResource();
            $new_capabilities->setProfile($capabilities_resource->getProfile());
            $new_capabilities->setType($capabilities_resource->getType());
            $new_capabilities->setInteractions($interactions);
            $new_capabilities->setSupportedProfiles($supportedProfiles);
            $new_capabilities->setVersion($resource->getResourceFHIRVersion());

            $resources[] = $new_capabilities;
        }

        return $resources;
    }

    /**
     * @param IActorFHIR $actor
     *
     * @return CCapabilitiesResource[]
     * @throws Exception
     */
    public function getResourcesManaged(IActorFHIR $actor): array
    {
        $resources_managed = [];
        foreach ($actor->getAvailableResources() as $available_resource) {
            $resources_managed[$available_resource->getResourceType()][] = $available_resource->getProfile();
        }

        $resources = [];
        foreach ($this->resources as $capabilities_resource) {
            // check actor defined Resource
            $resource_type = $capabilities_resource->getType();
            if (!$authorized_resource = CMbArray::get($resources_managed, $resource_type)) {
                continue;
            }

            // check if profile for this resource is supported by actor
            if (!in_array($capabilities_resource->getProfile(), $authorized_resource)) {
                continue;
            }

            // get fhir resource
            if (!$resource = $actor->getResource($capabilities_resource->getProfile())) {
                continue;
            }

            // keep all interactions managed in intern and supported by server
            $interactions_supported_by_actor = $actor->getAvailableInteractions($resource);
            $interactions                    = array_intersect(
                $capabilities_resource->getInteractions(),
                $interactions_supported_by_actor
            );

            if (!$interactions) {
                continue;
            }

            // keep all profiles managed in intern and supported by server
            $profiles_supported_by_actor = $actor->getAvailableProfiles($resource);
            $supportedProfiles           = array_intersect(
                $capabilities_resource->getSupportedProfiles(),
                $profiles_supported_by_actor
            );

            $new_capabilities = new CCapabilitiesResource();
            $new_capabilities->setProfile($capabilities_resource->getProfile());
            $new_capabilities->setType($capabilities_resource->getType());
            $new_capabilities->setInteractions($interactions);
            $new_capabilities->setSupportedProfiles($supportedProfiles);
            $new_capabilities->setVersion($resource->getResourceFHIRVersion());

            $resources[] = $new_capabilities;
        }

        return $this->_resources_managed = $resources;
    }

    /**
     * @param CCapabilitiesResource[] $capabilities_resources
     * @param string                  $type
     * @param string                  $profile
     *
     * @return array
     */
    private function getResource(array $capabilities_resources, string $type, string $profile): ?CCapabilitiesResource
    {
        $resource = array_filter(
            $capabilities_resources,
            function ($capability) use ($type, $profile) {
                return $type === $capability->getType() && $capability->getProfile() === $profile;
            }
        );

        if (count($resource) === 1) {
            return reset($resource);
        }

        return null;
    }

    /**
     * @param IActorFHIR $actor
     *
     * @return array
     * @throws Exception
     */
    public function getResourcesNotSupported(): array
    {
        $resources = [];
        foreach ($this->resources as $capabilities_resource) {
            if (!$resource = $capabilities_resource->getResource($this->fhirVersion)) {
                $resources[] = $capabilities_resource;
                continue;
            }

            $interactions = array_diff(
                $capabilities_resource->getInteractions(),
                $resource->getInteractions()
            );

            $supportedProfiles = array_diff(
                $capabilities_resource->getSupportedProfiles(),
                $resource->getProfiles($resource->findProfiles())
            );

            if (!$supportedProfiles && !$interactions) {
                continue;
            }

            $new_capabilities = new CCapabilitiesResource();
            $new_capabilities->setProfile($capabilities_resource->getProfile());
            $new_capabilities->setType($capabilities_resource->getType());
            $new_capabilities->setInteractions($interactions);
            $new_capabilities->setSupportedProfiles($supportedProfiles);
            $new_capabilities->setVersion($resource->getResourceFHIRVersion());

            $resources[] = $new_capabilities;
        }

        return $resources;
    }
}
