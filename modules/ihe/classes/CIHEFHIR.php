<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Exception;
use Ox\Interop\Eai\CMessageSupported;
use Ox\Interop\Fhir\ClassMap\FHIRClassMap;
use Ox\Interop\Fhir\Contracts\Profiles\ProfileResource;
use Ox\Interop\Fhir\Profiles\ProfileResourceTrait;

/**
 * Class CIHE
 * IHE classes
 */
class CIHEFHIR extends CIHE implements ProfileResource
{
    use ProfileResourceTrait;

    /** @var string */
    public const DOMAIN_TYPE = '';
    /** @var string */
    public const BASE_PROFILE = '';

    /** @var string */
    public const TYPE = 'FHIR';

    /** @var string */
    protected const PREFIX_TRANSLATE_VERSION = 'FHIR';

    public function __construct()
    {
        $this->domain = static::DOMAIN_ITI;
        $this->type   = static::DOMAIN_TYPE;

        parent::__construct();
    }

    /**
     * @return array|null
     * @throws Exception
     */
    public function getEvenements(): ?array
    {
        return $this->getEvenementsFromCategories();
    }

    public static function getCanonical(): string
    {
        return static::BASE_PROFILE;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getCategoryVersions(): array
    {
        if ($this->_versions_category) {
            return $this->_versions_category;
        }

        $map = (new FHIRClassMap());
        $versions = [];
        foreach (array_keys($this->_categories) as $canonical) {
            $versions[$canonical] = $map->version->getResourceVersions($canonical);
        }

        return $this->_versions_category = $versions;
    }

    /**
     * @param CMessageSupported $message_supported
     * @param string            $category
     *
     * @return bool
     */
    protected function isTransactionSupported(CMessageSupported $message_supported, string $category): bool
    {
        if (!$message_supported->transaction) {
            return false;
        }

        $map               = new FHIRClassMap();
        $expected_resource = $map->resource->getResource($category);
        $actual_resource   = $map->resource->getResource($message_supported->transaction);

        return $actual_resource instanceof $expected_resource;
    }

    /**
     * @inheritDoc
     */
    public static function listResourceCanonicals(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getProfileName(): string
    {
        return static::DOMAIN_TYPE;
    }
}
