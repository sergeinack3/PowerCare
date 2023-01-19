<?php

/**
 * @package Mediboard\Fhir\Resources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\ClassMap;

use Ox\Core\CMbArray;
use Ox\Interop\Fhir\Exception\CFHIRExceptionInformational;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Symfony\Component\HttpFoundation\Response;

class ResourceMap extends ObjectMap
{
    /** @var string */
    protected const KEY_MAP = 'resource';

    /**
     * @param string $canonical_or_type canonical | resource_type | resource_class
     *
     * @return CFHIRResource|string
     */
    public function getResource(string $canonical_or_type)
    {
        $version = $this->version;

        $all_canonical = CMbArray::getRecursive($this->class_map, "resource $version canonical", []);
        if (is_subclass_of($canonical_or_type, CFHIRResource::class)) {
            if (array_search($canonical_or_type, $all_canonical)) {
                return $this->out($canonical_or_type);
            }
        }

        // search by canonical
        if ($resource = CMbArray::get($all_canonical, "$canonical_or_type")) {
            return $this->out($resource);
        }

        // fallback : search by type (only for international resource)
        if ($resource = $this->listResources($canonical_or_type, CFHIR::class)) {
            return $this->out(reset($resource));
        }

        throw new CFHIRExceptionInformational(
            "It is impossible to resolve the resource '$canonical_or_type' with version '$version'",
            Response::HTTP_INTERNAL_SERVER_ERROR,
            [],
            CFHIRExceptionInformational::CODE_RESOURCE_NOT_SUPPORTED_NOW
        );
    }

    /**
     * @param string|null       $resource_type
     * @param CFHIR|string|null $profile
     *
     * @return CFHIRResource[]|string[]
     */
    public function listResources(?string $resource_type = null, $profile = null): array
    {
        $version = $this->version;

        if ($resource_type) {
            $resources = CMbArray::getRecursive($this->map, "$version type $resource_type", []);

            if ($profile) {
                return $this->out($this->filterByProfile($resources, $profile));
            }

            return $this->out($resources);
        }

        $resources = array_values(CMbArray::getRecursive($this->map, "$version canonical", []));
        if ($profile) {
            return $this->out($this->filterByProfile($resources, $profile));
        }

        return $this->out($resources);
    }

    /**
     * @param array        $resources
     * @param CFHIR|string $profile
     *
     * @return array
     */
    private function filterByProfile(array $resources, $profile): array
    {
        $version = $this->version;
        if ($profile instanceof CFHIR) {
            $profile = get_class($profile);
        }

        $resources_canonical = CMbArray::getRecursive($this->map, "$version canonical", []);
        $resources           = array_filter($resources_canonical, function ($resource_class) use ($resources) {
            return in_array($resource_class, $resources);
        });

        if (is_string($profile)) {
            if ($profile !== CFHIR::class && !is_subclass_of($profile, CFHIR::class)) {
                $profile = CMbArray::getRecursive($this->class_map, "profiles $version profile $profile", null);
            }

            $canonical_from_profile = CMbArray::getRecursive(
                $this->class_map,
                "profiles $version profile_class $profile",
                []
            );

            $resources = array_filter(
                $resources,
                function ($canonical) use ($canonical_from_profile) {
                    return in_array($canonical, $canonical_from_profile);
                },
                ARRAY_FILTER_USE_KEY
            );

            $resources = array_values($resources);
        } else {
            return [];
        }

        return $resources;
    }

    /**
     * @return CFHIRResource[]|string[]
     */
    public function listProfiled(?string $profile = null): array
    {
        $resources    = $this->listResources(null, $profile);

        return $this->out(
            array_values(
                array_filter($resources, function ($resource) {
                    return $resource::PROFILE_CLASS !== CFHIR::class;
                })
            )
        );
    }
}
