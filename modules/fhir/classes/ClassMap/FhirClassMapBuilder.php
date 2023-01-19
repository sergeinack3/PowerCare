<?php

/**
 * @package Mediboard\Fhir\Resources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\ClassMap;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectHandleInterface;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectInterface;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectSearcherInterface;
use Ox\Interop\Fhir\Contracts\Profiles\ProfileResource;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;

class FhirClassMapBuilder
{
    private array $map = [];

    private ?CClassMap $class_map = null;

    /** @var DelegatedObjectInterface[] */
    private array $delegated_objects = [];
    /** @var CFHIRResource[] */
    private array $resources = [];
    /** @var ProfileResource[] */
    private array $profiles = [];

    /**
     * @return array
     * @throws Exception
     */
    public function build(): array
    {
        $this->class_map = CClassMap::getInstance();

        if (!$this->resources) {
            $this->resources = $this->findResources();
        }

        if (!$this->profiles) {
            $this->profiles = $this->findProfiles();
        }

        if (!$this->delegated_objects) {
            $this->delegated_objects = $this->findDelegated();
        }

        $this->map = [];

        $this->buildVersions($this->resources);

        $this->buildResources($this->resources);

        $this->buildProfiles($this->profiles);

        $this->buildObjectsDelegated($this->resources);

        return $this->map;
    }

    /**
     * @param CFHIRResource[] $resources
     *
     * @return array
     */
    private function buildResources(array $resources): array
    {
        foreach ($resources as $resource) {
            $versions = $resource->getAvailableFHIRVersions();
            foreach ($versions as $version) {
                $this->buildResource($resource, $version);
            }
        }

        return $this->map;
    }

    /**
     * @param CFHIRResource $resource
     * @param string        $fhir_version
     *
     * @return array
     */
    private function buildResource(CFHIRResource $resource, string $fhir_version): array
    {
        /** @var CFHIR|string $profile_class */
        $resource_class = get_class($resource);
        $canonical      = $resource->getProfile();

        // resource <fhir_version> canonical <canonical>
        $this->map['resource'][$fhir_version]['canonical'][$canonical] = $resource_class;

        // resource <fhir_version> resource_type resource_type
        if ($resource->isFHIRResource()) {
            $this->map['resource'][$fhir_version]['resource_type'][$resource::RESOURCE_TYPE] = $resource_class;
        }

        // resource <fhir_version> type <resource_type>
        $this->map['resource'][$fhir_version]['type'][$resource::RESOURCE_TYPE][] = $resource_class;

        return $this->map;
    }

    /**
     * @param array         $map
     * @param CFHIRResource $resource
     * @param string        $fhir_version
     *
     * @return array
     */
    private function buildDelegated(CFHIRResource $resource, string $fhir_version): array
    {
        // delegated mapper
        $this->map['delegated'][$fhir_version]['mapper'] =
            $this->buildSpecificDelegated('mapper', $this->map, $resource, $fhir_version);

        // delegated searcher
        $this->map['delegated'][$fhir_version]['searcher'] =
            $this->buildSpecificDelegated('searcher', $this->map, $resource, $fhir_version);

        // delegated handle
        $this->map['delegated'][$fhir_version]['handle'] =
            $this->buildSpecificDelegated('handle', $this->map, $resource, $fhir_version);

        return $this->map;
    }

    /**
     * @param string        $key
     * @param array         $map
     * @param CFHIRResource $resource
     * @param string        $fhir_version
     *
     * @return array
     */
    private function buildSpecificDelegated(
        string $key,
        array $map,
        CFHIRResource $resource,
        string $fhir_version
    ): array {
        $profile_class = $resource::PROFILE_CLASS;
        $canonical     = $resource->getProfile();
        $resource_type = $resource::RESOURCE_TYPE;

        $delegated_objects = CMbArray::getRecursive($map, "delegated $fhir_version $key", []);
        // resource <fhir_version> delegated mapper type <resource_type>
        if ($types_delegated = CMbArray::getRecursive($this->delegated_objects, "$key type $resource_type", [])) {
            $delegated_objects['type'][$resource_type] = $types_delegated;
        }

        // canonical resource_canonical
        $resource_types   = array_keys($this->delegated_objects[$key]['type'] ?? []);
        $resource_classes = array_map(function (CFHIRResource $resource) {
            return get_class($resource);
        }, $this->resources);

        foreach ($this->delegated_objects[$key]['all'] ?? [] as $delegated_class) {
            if (!in_array($delegated_class, $this->delegated_objects[$key]['type'][$resource_type] ?? [])) {
                continue;
            }

            /** @var DelegatedObjectInterface $delegated_object */
            $delegated_object = new $delegated_class();

            // filtre by profiles
            $profile_classes = $this->map['profiles'][$fhir_version]['canonical'][$canonical] ?? [];
            $delegated_profile_classes = $delegated_object->onlyProfiles();
            if (is_array($delegated_profile_classes) && empty($delegated_profile_classes)) {
                continue;
            }

            if ($delegated_profile_classes && !array_intersect($delegated_profile_classes, $profile_classes)) {
                continue;
            }

            $resources = $delegated_object->onlyRessources();

            // if filter is null, add for all resources
            if ($resources === null) {
                $delegated_objects['canonical'][$canonical][] = $delegated_class;
                continue;
            }

            // filtre by resource_class or resource_type
            foreach ($resources as $res) {
                // is resource_type
                if (($is_resource_type = in_array($res, $resource_types)) && $res !== $resource_type) {
                    continue;
                }
                // resource_class
                elseif (($is_resource_class = in_array($res, $resource_classes)) && $res !== get_class($resource)) {
                    continue;
                }
                // bad value in $res
                elseif (!$is_resource_type && !$is_resource_class) {
                    continue;
                }

                // success filter
                $delegated_objects['canonical'][$canonical][] = $delegated_class;
                continue 2;
            }
        }

        // resource <fhir_version delegated mapper all
        if (!CMbArray::get($delegated_objects, "all", [])) {
            if ($all_delegated = CMbArray::getRecursive($this->delegated_objects, "$key all")) {
                $delegated_objects['all'] = $all_delegated;
            }
        }

        return $delegated_objects;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function findDelegated(): array
    {
        $delegated_objects = [];
        $classmap          = CClassMap::getInstance();

        // mapper
        $delegated_objects['mapper'] = $this->findSpecificDelegated(DelegatedObjectMapperInterface::class, $classmap);

        // searcher
        $delegated_objects['searcher'] = $this->findSpecificDelegated(
            DelegatedObjectSearcherInterface::class,
            $classmap
        );

        // handle
        $delegated_objects['handle'] = $this->findSpecificDelegated(DelegatedObjectHandleInterface::class, $classmap);

        return $delegated_objects;
    }

    /**
     * @param string    $interface
     * @param CClassMap $classMap
     *
     * @return array
     * @throws Exception
     */
    private function findSpecificDelegated(string $interface, CClassMap $classMap): array
    {
        $result = [];

        $resource_types = [];
        $resource_classes = [];
        foreach ($this->resources as $resource) {
            $resource_types[$resource::RESOURCE_TYPE] = $resource::RESOURCE_TYPE;
            $resource_classes[get_class($resource)] = $resource::RESOURCE_TYPE;
        }

        foreach ($classMap->getClassChildren($interface) as $class) {
            if (!$classMap->getClassMap($class)) {
                continue;
            }
            /** @var DelegatedObjectInterface $object_delegated */
            $object_delegated = new $class();

            $filter_resources = $object_delegated->onlyRessources();

            // autoconfig disable
            if (is_array($filter_resources) && empty($filter_resources)) {
                continue;
            }

            $resources = $filter_resources ?: array_values($resource_types);

            foreach ($resources as $resource) {
                $resource_type = $resource_types[$resource] ?? null;
                $resource_type = $resource_type ?: ($resource_classes[$resource] ?? null);
                if ($resource_type === null) {
                    continue;
                }

                // resource type
                if (!in_array($class, $result['type'][$resource_type] ?? [])) {
                    $result['type'][$resource_type][] = $class;
                }

                // all
                if (!in_array($class, $result['all'] ?? [])) {
                    $result['all'][] = $class;
                }
            }
        }

        return $result;
    }

    /**
     * @param CFHIRResource[] $resources
     * @param string[]        $profiles
     *
     * @return array
     */
    private function buildProfiles(array $profiles): void
    {
        /** @var string|ProfileResource $profile */
        foreach ($profiles as $profile) {
            $canonical_resources = $profile::listResourceCanonicals();
            foreach ($canonical_resources as $canonical) {
                if (!$versions = $this->map['versions']['canonical'][$canonical] ?? null) {
                    continue;
                }
                foreach ($versions as $fhir_version) {
                    if (!$resource = $this->map['resource'][$fhir_version]['canonical'][$canonical] ?? null) {
                        continue;
                    }

                    $this->buildProfile($profile, new $resource(), $fhir_version);
                }
            }
        }
    }

    /**
     * @param string[]|CFHIRResource[] $resources
     *
     * @return FhirClassMapBuilder
     */
    public function setResources(array $resources): FhirClassMapBuilder
    {
        foreach ($resources as $key => $resource) {
            if ($resource instanceof CFHIRResource) {
                continue;
            }

            if (is_string($resource) && is_subclass_of($resource, CFHIRResource::class)) {
                $resources[$key] = new $resource();
                continue;
            }

            unset($resources[$key]);
        }

        $this->resources = $resources;

        return $this;
    }

    /**
     * @param DelegatedObjectInterface[] $delegated_objects
     *
     * @return FhirClassMapBuilder
     */
    public function setDelegatedObjects(array $delegated_objects): FhirClassMapBuilder
    {
        foreach ($delegated_objects as $key => $delegated) {
            if (is_string($delegated) && is_subclass_of($delegated, ProfileResource::class)) {
                $delegated_objects[$key] = new $delegated();
            } elseif (!$delegated instanceof ProfileResource) {
                unset($delegated_objects[$key]);
            }
        }

        $this->delegated_objects = $delegated_objects;

        return $this;
    }

    /**
     * @param ProfileResource[] $profiles
     *
     * @return FhirClassMapBuilder
     */
    public function setProfiles(array $profiles): FhirClassMapBuilder
    {
        foreach ($profiles as $key => $profile) {
            if (is_string($profile) && is_subclass_of($profile, ProfileResource::class)) {
                $profiles[$key] = new $profile();
            } elseif (!$profile instanceof ProfileResource) {
                unset($profiles[$key]);
            }
        }

        $this->profiles = $profiles;

        return $this;
    }

    private function findProfiles(): array
    {
        return $this->class_map->getClassChildren(ProfileResource::class, false, true);
    }

    private function findResources(): array
    {
        return $this->class_map->getClassChildren(CFHIRResource::class, true, true);
    }

    /**
     * @param CFHIRResource[] $resources
     *
     * @return void
     */
    private function buildVersions(array $resources): void
    {
        foreach ($resources as $resource) {
            $versions = $resource->getAvailableFHIRVersions();
            foreach ($versions as $version) {
                if (!in_array($version, $this->map['versions']['fhir_version'] ?? [])) {
                    $this->map['versions']['fhir_version'][] = $version;
                }

                $this->map['versions']['canonical'][$resource::getCanonical()] = array_values($versions);
            }
        }
    }

    private function buildProfile(string $profile_class, CFHIRResource $resource, string $fhir_version): void
    {
        $map       = $this->map;
        $canonical = $resource::getCanonical();

        // profiles <fhir_version> canonical <canonical>
        $this->map['profiles'][$fhir_version]['canonical'][$canonical][] = $profile_class;

        // profiles <fhir_version> profile_class <profile_class>
        if (!in_array($canonical, CMbArray::getRecursive($map, "profiles $fhir_version profile $profile_class", []))) {
            $this->map['profiles'][$fhir_version]['profile_class'][$profile_class][] = $canonical;
        }

        // profiles <fhir_version> resource_type <fhir_resource>
        $this->map['profiles'][$fhir_version]['resource_type'][$resource::RESOURCE_TYPE][] = $profile_class;

        // profiles <fhir_version> profile <base_url> => profile_class
        $this->map['profiles'][$fhir_version]['profile'][$profile_class::BASE_PROFILE] = $profile_class;
    }

    /**
     * @param CFHIRResource[] $resources
     *
     * @return void
     */
    private function buildObjectsDelegated(array $resources): void
    {
        foreach ($resources as $resource) {
            $versions = $resource->getAvailableFHIRVersions();
            foreach ($versions as $version) {
                $this->buildDelegated($resource, $version);
            }
        }
    }
}
