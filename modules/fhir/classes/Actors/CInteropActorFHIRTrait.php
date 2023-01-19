<?php

/**
 * @package Mediboard\Fhir\Actors
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Actors;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CMessageSupported;
use Ox\Interop\Fhir\ClassMap\FHIRClassMap;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectHandleInterface;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectInterface;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectSearcherInterface;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Exception\CFHIRExceptionInformational;
use Ox\Interop\Fhir\Exception\CFHIRExceptionInvalidValue;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\CapabilityStatement\CFHIRResourceCapabilityStatement;

trait CInteropActorFHIRTrait
{
    /** @var CMessageSupported[] */
    private $fhir_messages_supported;

    /** @var FHIRClassMap */
    protected $fhir_map;

    /** @var array */
    protected $delegated_objects;

    /**
     * @param CInteropActor $actor
     * @param CFHIRResource $resource
     *
     * @return string[]
     * @throws Exception
     */
    private function getAvailableProfilesForActor(CInteropActor $actor, CFHIRResource $resource): array
    {
        if (!$actor->_id) {
            return [];
        }

        $message_supported = new CMessageSupported();
        $ds                = $message_supported->getDS();

        $where = [
            'object_id'    => $ds->prepare('= ? ', $actor->_id),
            'object_class' => $ds->prepare('= ?', $actor->_class),
            'active'       => $ds->prepare('= ?', true),
            'transaction'  => $ds->prepare('= ?', $resource::RESOURCE_TYPE),
        ];

        $messages_supported = $message_supported->loadList($where);
        $profile_classes    = array_map(
            function ($class) {
                $profile_object = new $class();
                if (!$profile_object instanceof CFHIR) {
                    return null;
                }

                return get_class($profile_object);
            },
            CMbArray::pluck($messages_supported, 'profil')
        );
        $profile_classes    = array_filter($profile_classes);

        $profiles = [];
        foreach ($resource->findProfiles() as $resource_profile) {
            if (!in_array($resource_profile::PROFILE_CLASS, $profile_classes)) {
                continue;
            }

            $profiles[] = $resource_profile->getProfile();
        }

        return $profiles;
    }

    /**
     * @param CInteropActor $actor
     *
     * @return CFHIRResource[]
     * @throws Exception
     */
    private function getAvailableResourcesForActor(CInteropActor $actor): array
    {
        $default_available_resources = [
            CFHIRResourceCapabilityStatement::RESOURCE_TYPE => new CFHIRResourceCapabilityStatement(),
        ];

        if (!$actor->_id) {
            return $default_available_resources;
        }

        $messages = $actor->getMessagesSupported();
        // messages sorted by profiles
        $messages_sorted = [];
        foreach ($messages as $message) {
            if (!$message->active) {
                continue;
            }

            $messages_sorted[$message->profil][$message->transaction] = $message;
        }

        $available_resources = [];
        foreach ($messages_sorted as $profile_class => $messages) {
            /** @var CMessageSupported $message */
            foreach ($messages as $message) {
                if (!$resource = $this->getResourceFromMessage($message)) {
                    continue;
                }

                $available_resources[get_class($resource)] = $resource;
            }
        }

        $capabilities_resource_enabled = array_filter(
            $available_resources,
            function (CFHIRResource $resource) {
                return $resource::RESOURCE_TYPE === CFHIRResourceCapabilityStatement::RESOURCE_TYPE;
            }
        );

        if (!$capabilities_resource_enabled) {
            $available_resources = array_merge($available_resources, $default_available_resources);
        }

        return $available_resources;
    }

    /**
     * @param CMessageSupported $message_supported
     *
     * @return CFHIRResource|null
     * @throws Exception
     */
    public function getResourceFromMessage(CMessageSupported $message_supported): ?CFHIRResource
    {
        if (!$message_supported->transaction) {
            return null;
        }

        try {
            return (new FHIRClassMap())->resource->getResource($message_supported->transaction);
        } catch (CFHIRExceptionInformational $exception) {
            // old message not supported now or invalid resource fhir
            return null;
        }
    }

    /**
     * @param CInteropActor $actor
     * @param CFHIRResource $resource
     *
     * @return string[]
     * @throws Exception
     */
    private function getAvailableInteractionsForActor(CInteropActor $actor, CFHIRResource $resource): array
    {
        if ($resource::RESOURCE_TYPE === CFHIRResourceCapabilityStatement::RESOURCE_TYPE) {
            return (new CFHIRResourceCapabilityStatement())->getInteractions();
        }

        $messages_supported   = $this->getMessagesSupportedForActor($actor, $resource);
        $interaction_classes  = CMbArray::pluck($messages_supported, 'message');
        $interactions_managed = array_map(
            function ($class) {
                /** @var CFHIRInteraction $interaction */
                $interaction = new $class();

                return $interaction::NAME;
            },
            $interaction_classes
        );

        return array_intersect($interactions_managed, $resource->getInteractions());
    }

    /**
     * @param CInteropActor $actor
     * @param CFHIRResource $resource
     * @param CFHIRInteraction[] $interactions
     *
     * @return array
     * @throws Exception
     */
    private function getMessagesSupportedForActor(
        CInteropActor $actor,
        CFHIRResource $resource,
        array $interactions = []
    ): array {
        $message_supported = new CMessageSupported();
        $ds                = $message_supported->getDS();

        $where = [
            'object_id'    => $ds->prepare('= ?', $actor->_id),
            'object_class' => $ds->prepare('= ?', $actor->_class),
            'active'       => $ds->prepare('= ?', 1),
            'transaction'  => $ds->prepare('= ?', $resource->getProfile()),
            'version'      => $ds->prepare('= ?', $resource->getResourceFHIRVersion())
        ];

        if ($interactions) {
            $messages_interaction = [];
            foreach ($interactions as $interaction) {
                if (!is_object($interaction)) {
                    if (!is_subclass_of($interaction, CFHIRInteraction::class)) {
                        throw new CFHIRExceptionInvalidValue("The class given is not a subclass of CFHIRInteraction");
                    }

                    $interaction = new $interaction();
                }

                if (!$interaction instanceof CFHIRInteraction) {
                    throw new CFHIRExceptionInvalidValue("The object given is not an instance of CFHIRInteraction");
                }

                $messages_interaction[] = CClassMap::getSN($interaction);
            }

            if ($messages_interaction) {
                $where['message'] = $message_supported->getDS()->prepareIn($messages_interaction);
            }
        }

        return $message_supported->loadList($where);
    }

    /**
     * @return CMessageSupported[]
     * @throws Exception
     */
    private function getFhirMessagesSupported(): array
    {
        if ($this->fhir_messages_supported) {
            return $this->fhir_messages_supported;
        }

        $all_messages_supported = $this->getMessagesSupported();
        $messages = [];
        foreach ($all_messages_supported as $message_supported) {
            if (!$message_supported->active || !($resource = $this->getResourceFromMessage($message_supported))) {
                continue;
            }

            $messages[] = $message_supported;
        }

        return $this->fhir_messages_supported = $messages;
    }

    /**
     * @inheritDoc
     */
    public function getActorClassMap(): FHIRClassMap
    {
        if ($this->fhir_map) {
            return $this->fhir_map;
        }

        $fhir_map_all_resources = new FHIRClassMap();
        $fhir_map_all_resources->resource->setReturnClass(true);

        $resources = [];
        $messages_supported = $this->getFhirMessagesSupported();
        foreach ($messages_supported as $message_supported) {
            if (!$resource = $this->getResourceFromMessage($message_supported)) {
                continue;
            }

            $resources[] = $resource_class = get_class($resource);
        }

        return $this->fhir_map = new FHIRClassMap($resources);
    }

    /**
     * @param string $canonical_or_type
     *
     * @return CFHIRResource
     */
    protected function getResourceTrait(string $canonical_or_type): CFHIRResource
    {
        $actor_map = $this->getActorClassMap();

        return $actor_map->resource->getResource($canonical_or_type);
    }

    /**
     * @inheritDoc
     */
    public function getDelegatedMapper($resource_or_canonical): ?DelegatedObjectMapperInterface
    {
        return $this->getDelegatedObject('mapper', $resource_or_canonical);
    }

    /**
     * @param string $type
     * @param string|CFHIRResource $resource
     *
     * @return DelegatedObjectInterface
     * @throws Exception
     */
    private function getDelegatedObject(string $type, $resource): ?DelegatedObjectInterface
    {
        if (!$resource instanceof CFHIRResource) {
            if (!$resource = $this->getActorClassMap()->resource->getResource($resource)) {
                throw new CFHIRException('invalid resource given');
            }
        }

        if ($delegated = CMbArray::getRecursive($this->delegated_objects, "$type " . $resource->getProfile())) {
            return $delegated;
        }

        $messages_supported = $this->getFhirMessagesSupported();
        $delegated_object   = null;

        foreach ($messages_supported as $message_supported) {
            if ($message_supported->transaction === $resource->getProfile()) {
                if ($config_delegated = CAppUI::conf("fhir delegated_objects delegated_$type", $message_supported->_guid)) {
                    $delegated_object = $this->getActorClassMap()->delegated->getDelegatedFromShortName($type, $config_delegated);
                }
                break;
            }
        }

        $this->delegated_objects[$type][$resource->getProfile()] = $delegated_object;

        return $delegated_object;
    }

    /**
     * @inheritDoc
     */
    public function getDelegatedSearcher($resource_or_canonical): ?DelegatedObjectSearcherInterface
    {
        return $this->getDelegatedObject('searcher', $resource_or_canonical);
    }

    /**
     * @inheritDoc
     */
    public function getDelegatedHandle($resource_or_canonical): ?DelegatedObjectHandleInterface
    {
        return $this->getDelegatedObject('handle', $resource_or_canonical);
    }
}
