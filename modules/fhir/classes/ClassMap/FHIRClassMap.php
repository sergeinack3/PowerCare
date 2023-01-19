<?php

/**
 * @package Mediboard\Fhir\Resources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\ClassMap;

use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Components\Cache\LayeredCache;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Psr\SimpleCache\InvalidArgumentException;

class FHIRClassMap
{
    /** @var string */
    private const CACHE_KEY = 'FHIR-map';
    /** @var int */
    private const CACHE_TTL = 600;

    /** @var array */
    private $map = [];

    /** @var string  */
    private $default_fhir_version;

    /** @var LayeredCache */
    private $cache;

    /** @var ProfileMap */
    public $profile;

    /** @var ResourceMap */
    public $resource;

    /** @var DelegatedMap */
    public $delegated;

    /** @var VersionMap */
    public $version;

    /**
     * CFHIRMap constructor.
     *
     * @param string[] $resources
     *
     * @throws InvalidArgumentException
     * @throws CouldNotGetCache
     */
    public function __construct(array $resources = [])
    {
        $this->default_fhir_version = CAppUI::conf('fhir general version', 'static');
        $this->cache                = Cache::getCache(Cache::INNER_OUTER);
        $this->map                  = $this->getOrBuildClassMap($resources);
    }

    /**
     * @param string $version
     *
     * @return $this
     */
    public function selectVersion(string $version): self
    {
        $this->profile->selectVersion($version);
        $this->resource->selectVersion($version);
        $this->delegated->selectVersion($version);

        return $this;
    }

    /**
     * @param string[] $resources
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function getOrBuildClassMap(array $resources = []): array
    {
        if ($this->map) {
            return $this->map;
        }

        // only fhir ressources
        $resources = array_filter($resources, function ($class) {
            return is_subclass_of($class, CFHIRResource::class);
        });

        $hash = sha1(serialize($resources));

        $key = count($resources) > 0 ? self::CACHE_KEY . ".$hash" : self::CACHE_KEY;
        $ttl = count($resources) > 0 ? self::CACHE_TTL : null;

        if (!$class_map = $this->cache->get($key)) {
            $class_map = (new FhirClassMapBuilder())
                ->setResources($resources)
                ->build();
        }

        $this->map = $class_map;

        $this->profile   = new ProfileMap($this, $this->default_fhir_version);
        $this->resource  = new ResourceMap($this, $this->default_fhir_version);
        $this->delegated = new DelegatedMap($this, $this->default_fhir_version);
        $this->version   = new VersionMap($this, $this->default_fhir_version);

        $this->cache->set($key, $class_map, $ttl);

        return $class_map;
    }

    public function clearCache(): bool
    {
        $this->map = null;
        return $this->cache->delete(self::CACHE_KEY);
    }
}
