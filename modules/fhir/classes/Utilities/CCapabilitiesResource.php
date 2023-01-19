<?php

/**
 * @package Mediboard\Fhir\Objects
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities;

use Ox\Core\CMbArray;
use Ox\Interop\Fhir\ClassMap\FHIRClassMap;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Utilities\SearchParameters\AbstractSearchParameter;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterList;

class CCapabilitiesResource
{
    /** @var string */
    private $type;

    /** @var string */
    private $profile;

    /** @var string[] */
    private $supportedProfiles = [];

    /** @var string[] */
    private $interactions = [];

    /** @var SearchParameterList */
    private $searchParameters;

    /** @var string */
    private $version;

    /**
     * CCapabilitiesResource constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->searchParameters = new SearchParameterList();

        if (!$type = CMbArray::get($data, 'type')) {
            return;
        }

        $this->setType($type);

        $profile = CMbArray::get($data, 'profile', CFHIR::BASE_PROFILE . $type);
        $this->setProfile($profile);

        if ($interactions = CMbArray::get($data, 'interactions')) {
            $this->addInteractions($interactions);
        }

        if ($supported_profiles = CMbArray::get($data, 'supportedProfiles')) {
            $this->addSupportedProfiles($supported_profiles);
        }
    }

    /**
     * @param string $type
     *
     * @return CCapabilitiesResource
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param string      $profile
     * @param string|null $type
     *
     * @return CCapabilitiesResource
     */
    public function setProfile(string $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @param string[] $interactions
     *
     * @return CCapabilitiesResource
     */
    public function setInteractions(array $interactions): self
    {
        $this->interactions = $interactions;

        return $this;
    }

    /**
     * @param string[] $supportedProfiles
     *
     * @return CCapabilitiesResource
     */
    public function setSupportedProfiles(array $supportedProfiles): self
    {
        $this->supportedProfiles = $supportedProfiles;

        return $this;
    }

    /**
     * @param string|string[] $profiles
     *
     * @return CCapabilitiesResource
     */
    public function addSupportedProfiles($profiles): self
    {
        if (!is_array($profiles)) {
            $profiles = [$profiles];
        }

        foreach ($profiles as $profile) {
            if (!in_array($profile, $this->supportedProfiles)) {
                $this->supportedProfiles[] = $profile;
            }
        }

        return $this;
    }

    /**
     * @param string|string[] $interactions
     *
     * @return void
     */
    public function addInteractions($interactions): self
    {
        if (!is_array($interactions)) {
            $interactions = [$interactions];
        }

        foreach ($interactions as $interaction) {
            if (!in_array($interaction, $this->interactions)) {
                $this->interactions[] = $interaction;
            }
        }

        return $this;
    }

    /**
     * @param AbstractSearchParameter[]|AbstractSearchParameter $search_parameters
     *
     * @return $this
     */
    public function addSearchAttributes($search_parameters): self
    {
        if (!is_array($search_parameters)) {
            $search_parameters = [$search_parameters];
        }

        foreach ($search_parameters as $search_parameter) {
            if (!$search_parameter instanceof AbstractSearchParameter) {
                throw new CFHIRException(
                    'search parameter definition should be an instance of ' . AbstractSearchParameter::class
                );
            }

            // add parameter only if not exist
            if (!$this->searchParameters->addUnique($search_parameter)) {
                $name = $search_parameter->getParameterName();
                throw new CFHIRException("The parameter '$name' should be unique");
            }
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getInteractions(): array
    {
        return $this->interactions;
    }

    /**
     * @return string[]
     */
    public function getSupportedProfiles(): array
    {
        return $this->supportedProfiles;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getProfile(): string
    {
        return $this->profile;
    }

    /**
     * @param string $parameter_name
     *
     * @return AbstractSearchParameter|null
     */
    public function getSearchParameters(string $parameter_name): ?AbstractSearchParameter
    {
        /** @var AbstractSearchParameter|null $parameter */
        if ($parameter = $this->searchParameters->get($parameter_name)) {
            return $parameter;
        }

        return null;
    }

    /**
     * @param string $version
     *
     * @return CCapabilitiesResource
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version ?: "";
    }

    /**
     * @param string[] $supported_versions
     */
    public function setSupportedVersions(array $supported_versions): self
    {
        $this->supported_versions = $supported_versions;

        return $this;
    }

    /**
     * @return CFHIRResource|null
     */
    public function getResource(?string $fhir_version = null): ?CFHIRResource
    {
        return (new FHIRClassMap())->resource->getResource($this->profile);
    }
}
