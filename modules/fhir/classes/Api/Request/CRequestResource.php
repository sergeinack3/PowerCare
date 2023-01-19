<?php

/**
 * @package Mediboard\Fhir\Request\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Api\Request;

use Exception;
use Ox\Core\Api\Request\IRequestParameter;
use Ox\Interop\Fhir\Actors\CSenderFHIR;
use Ox\Interop\Fhir\CExchangeFHIR;
use Ox\Interop\Fhir\Controllers\CFHIRController;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Exception\CFHIRExceptionNotFound;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCapabilities;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\CapabilityStatement\CFHIRResourceCapabilityStatement;
use Ox\Interop\Fhir\Serializers\CFHIRParser;
use Ox\Mediboard\System\CSenderHTTP;
use Symfony\Component\HttpFoundation\Request;

class CRequestResource implements IRequestParameter
{
    /** @var string */
    public const KEY_FHIR_QUERY_PROFILE = '_profile';
    /** @var string */
    public const KEY_FHIR_QUERY_ID = '_id';
    /** @var string */
    public const KEY_FHIR_QUERY_RESOURCE_TYPE = '_type';
    /** @var string */
    public const KEY_API_ATTRIBUTE_RESOURCE = 'resource';
    /** @var string */
    public const KEY_API_ATTRIBUTE_RESOURCE_ID = 'resource_id';
    /** @var string */
    public const KEY_API_ATTRIBUTE_VERSION_ID = 'version_id';
    /** @var string */
    public const KEY_INTERN_RESOURCE_TYPE = 'resource_type';
    /** @var string */
    public const KEY_INTERN_PROFILE = 'resource_profile';

    /** @var Request */
    private $request;

    /** @var CFHIRResource */
    private $resource;

    /** @var CSenderFHIR|null */
    private $sender;

    /** @var string */
    private $resource_type;

    /** @var string */
    private $profile;

    /**
     * CRequestResource constructor.
     *
     * @param Request $request
     *
     * @throws Exception
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        if ($sender_id = $request->attributes->get('fhir_sender_id')) {
            // keep sender already load on controller
            $sender = CFHIRController::$sender_http;

            // if not the same reload sender
            if (!$sender && $sender->_id !== $sender_id) {
                $sender = (new CSenderHTTP())->load($sender_id);
                $sender->getConfigs(new CExchangeFHIR());
            }

            $this->sender = $sender->_id ? new CSenderFHIR($sender) : null;
        }

        // case resource type is clearly defined
        $this->resource_type = $this->findResourceType($request);

        $this->profile = $this->findProfile();
    }

    /**
     * @param Request $request
     *
     * @return string|null
     */
    private function findResourceType(Request $request): ?string
    {
        // resource type already resolved
        if ($resource_type = $request->attributes->get(self::KEY_INTERN_RESOURCE_TYPE)) {
            return $resource_type;
        }

        // case of route /metadata
        $interaction_class = $request->attributes->get('object_class');
        if ($interaction_class === CFHIRInteractionCapabilities::class) {
            return CFHIRResourceCapabilityStatement::RESOURCE_TYPE;
        }

        // case of route POST
        if (in_array($interaction_class, [CFHIRInteractionCreate::class, CFHIRInteractionUpdate::class])) {
            if ($resource = CFHIRParser::tryToDetermineResource($request->getContent())) {
                // save profile type
                $request->attributes->set(self::KEY_INTERN_PROFILE, $resource->getProfile());

                return $resource::RESOURCE_TYPE;
            }
        }

        // get resource type clearly defined in route
        if ($resource_type = $request->attributes->get(self::KEY_API_ATTRIBUTE_RESOURCE)) {
            return $resource_type;
        }

        // get resource type defined in query parameters
        if ($resource_type = $request->query->get(self::KEY_FHIR_QUERY_RESOURCE_TYPE)) {
            return $resource_type;
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function findProfile(): ?string
    {
        // profile already matches
        if ($profile = $this->request->attributes->get(self::KEY_INTERN_PROFILE)) {
            return $profile;
        }

        // find from query
        $profile = $this->request->query->get(self::KEY_FHIR_QUERY_PROFILE);
        if ($profile && $this->request->getMethod() === Request::METHOD_GET) {
            return $profile;
        }

        // find inside content
        $interaction_class = $this->request->attributes->get('object_class');
        // case of route POST
        if ($interaction_class === CFHIRInteractionCreate::class || $interaction_class === CFHIRInteractionUpdate::class) {
            // todo
        }

        return null;
    }

    /**
     * @throws CFHIRExceptionNotFound
     * @throws Exception
     */
    private function findResource(): ?CFHIRResource
    {
        // find resource
        if ($resource = $this->findResourceFromSender()) {
            $resource->_sender = $this->sender->getSender();

            if ($resource_id = $this->getResourceId()) {
                $resource->setId(new CFHIRDataTypeString($resource_id));
            }
        }

        return $this->resource = $resource;
    }

    /**
     * @return string|null
     */
    public function getResourceType(): ?string
    {
        return $this->resource_type;
    }

    /**
     * @return CSenderFHIR|null
     */
    public function getSender(): ?CSenderFHIR
    {
        $sender = $this->sender->getSender();

        return $sender && $sender->_id ? $this->sender : null;
    }

    /**
     * @return string|null
     */
    public function getVersionId(): ?string
    {
        return $this->request->attributes->get(self::KEY_API_ATTRIBUTE_VERSION_ID);
    }

    /**
     * @return string|null
     */
    public function getResourceId(): ?string
    {
        return $this->request->attributes->get(self::KEY_API_ATTRIBUTE_RESOURCE_ID);
    }

    /**
     * @param string      $resource_type
     * @param string|null $profile
     *
     * @return CFHIRResource|null
     * @throws Exception
     */
    private function findResourceFromSender(): ?CFHIRResource
    {
        // retrieve all resources active from supported_messages
        $available_resources = $this->sender->getAvailableResources();
        // find from profile
        if ($this->profile) {
            $resources = array_filter(
                $available_resources,
                function (CFHIRResource $resource) {
                    return $resource->getProfile() === $this->profile;
                }
            );
        } else {
            // find from resource_type
            $resources = array_filter(
                $available_resources,
                function (CFHIRResource $resource) {
                    return $resource->getResourceType() === $this->resource_type;
                }
            );

            // if multiples matches, keep fhir resource
            if (count($resources) > 1) {
                $fhir_resource = array_filter(
                    $resources,
                    function (CFHIRResource $resource) {
                        return $resource->isFHIRResource();
                    }
                );

                $resources = count($fhir_resource) === 1 ? $fhir_resource : $resources;
            }
        }

        // resource matched
        if (count($resources) === 1) {
            return reset($resources);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getProfile(): ?string
    {
        return $this->profile;
    }

    /**
     * @return CFHIRResource|null
     * @throws Exception
     */
    public function getResource(): ?CFHIRResource
    {
        if ($this->resource) {
            return $this->resource;
        }

        return $this->findResource();
    }
}
