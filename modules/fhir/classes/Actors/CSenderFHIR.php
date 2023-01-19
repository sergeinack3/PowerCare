<?php

/**
 * @package Mediboard\Fhir\Actors
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Actors;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Interop\Eai\CMessageSupported;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CSenderHTTP;

class CSenderFHIR implements IShortNameAutoloadable, IActorFHIR
{
    use CInteropActorFHIRTrait;

    /** @var CSenderHTTP */
    private $sender;

    public function __construct(?CSenderHTTP $sender = null)
    {
        $this->sender = $sender ?? new CSenderHTTP();
    }

    /**
     * @param CUser $user
     *
     * @return $this|null
     * @throws Exception
     */
    public function loadFromUser(CUser $user): ?CSenderHTTP
    {
        if (!$user->_id) {
            return null;
        }

        return $this->sender = CSenderHTTP::loadFromUser($user);
    }

    /**
     * @return CFHIRResource[]
     * @throws Exception
     */
    public function getAvailableResources(): array
    {
        return $this->getAvailableResourcesForActor($this->sender);
    }

    /**
     * @param CFHIRResource $resource
     *
     * @return string[]
     * @throws Exception
     */
    public function getAvailableInteractions(CFHIRResource $resource): array
    {
        return $this->getAvailableInteractionsForActor($this->sender, $resource);
    }

    /**
     * @param CFHIRResource $resource
     *
     * @return string[]
     * @throws Exception
     */
    public function getAvailableProfiles(CFHIRResource $resource): array
    {
        return $this->getAvailableProfilesForActor($this->sender, $resource);
    }

    /**
     * @return CSenderHTTP
     */
    public function getSender(): ?CSenderHTTP
    {
        return $this->sender;
    }

    /**
     * @param CFHIRResource         $resource
     * @param CFHIRInteraction[] $interactions
     *
     * @return array|CMessageSupported[]
     * @throws Exception
     */
    public function getMessagesSupportedForResource(CFHIRResource $resource, array $interactions = []): array
    {
        return $this->getMessagesSupportedForActor($this->sender, $resource, $interactions);
    }

    public function getResource(string $canonical_or_type): CFHIRResource
    {
        $resource = $this->getResourceTrait($canonical_or_type);
        $resource->setInteropActor($this->sender);

        return $resource;
    }

    /**
     * @return CMessageSupported[]
     * @throws Exception
     */
    public function loadRefsMessagesSupported(): array
    {
        if (!$this->sender) {
            return [];
        }

        $messages = $this->sender->loadRefsMessagesSupported();

        // build class map fhir
        $this->getActorClassMap();

        return $messages;
    }

    /**
     * Get messages supported for sender
     *
     * @return array
     * @throws Exception
     */
    public function getMessagesSupported(): array
    {
        return $this->sender ? $this->sender->getMessagesSupported() : [];
    }
}
