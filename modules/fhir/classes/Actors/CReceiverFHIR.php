<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Actors;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\HttpClient\Client;
use Ox\Core\HttpClient\ClientException;
use Ox\Core\HttpClient\Response;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CMessageSupported;
use Ox\Interop\Fhir\CExchangeFHIR;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Exception\CFHIRExceptionInformational;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;

/**
 * Class CReceiverFHIR
 * Receiver FHIR
 */
class CReceiverFHIR extends CInteropReceiver implements IActorFHIR
{
    use CInteropActorFHIRTrait;

    /** @var string[] */
    public const ACTORS_MANAGED = [
        self::ACTOR_APPFINE,
        self::ACTOR_MEDIBOARD,
        self::ACTOR_TAMM,
    ];

    /** @var array Sources supportes par un destinataire */
    public static $supported_sources = [
        CSourceHTTP::TYPE,
    ];

    /** @var string */
    public const PROFILE_FHIR = 'CFHIR';

    /** @var string */
    public const PROFILE_PIXM = 'CPIXm';

    /** @var string */
    public const PROFILE_MHD = 'CMHD';

    /** @var string */
    public const PROFILE_PDQM = 'CPDQm';

    // attributes
    /** @var integer */
    public $receiver_fhir_id;

    // form fields
    /** @var string */
    public $_tag_fhir;

    /** @var CSourceHTTP */
    public $_source;

    /** @var CExchangeFHIR */
    public $_exchange_fhir;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = 'receiver_fhir';
        $spec->key      = 'receiver_fhir_id';
        $spec->messages = [
            "FHIR" => ["CFHIR"],
            "PDQm" => ["CPDQm"],
            "PIXm" => ["CPIXm"],
            "MHD"  => ["CMHD"],
        ];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props["group_id"]  .= " back|destinataires_fhir";
        $props["_tag_fhir"] = "str";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_tag_fhir = CFHIR::getObjectTag($this->group_id);

        if (!$this->_configs) {
            $this->loadConfigValues();
        }
    }

    /**
     * @param CFHIRInteraction $request
     *
     * @inheritdoc
     * @return Response
     * @throws Exception
     */
    public function sendEvent(
        $request,
        $object = null,
        $data = [],
        $headers = [],
        $message_return = false,
        $soapVar = false,
        $method = "GET"
    ) {
        if (!parent::sendEvent($request::NAME, $object, $data, $headers, $message_return, $soapVar)) {
            return null;
        }

        /** @var CSourceHTTP $source */
        $source = $this->getSource($request->profil);
        if (!$source || !$source->_id || !$source->active) {
            throw new CFHIRExceptionInformational(CAppUI::tr("CExchangeSource-not_available_for_%s", $this->nom));
        }

        $this->checkInteraction($request);

        // validate exchange
        $validation = true;

        $url = trim($source->host, '/');
        if ($path = $request->getPath()) {
            $url .= (!str_starts_with($path, '?') ? '/' : "") . $path;
        }

        // Request build and generate exchange
        $data_trace        = $request->buildQuery($data);
        $data_trace['url'] = $url;

        // Génération de l'échange
        $this->generateExchange($request, $data_trace, $validation);

        // Si on n'est pas en synchrone
        if (!$this->synchronous) {
            return null;
        }

        $configs       = [
            'base_uri' => $source->host,
            'timeout'  => 60
        ];
        $guzzle_client = new GuzzleClient($configs);

        $client = new Client($source, $guzzle_client);
        $client->setTokenHeader();
        $client->setHeaders(['Accept' => $request->format, 'Content-Type' => $request->getFormat()]);
        $client->setHeaders($headers);

        $this->beforeCall();
        try {
            $response = $client->call($method, $url, $request->getBody($data));
        } catch (ClientException $e) {
            throw new CFHIRException($e->getMessage(), $e->getCode());
        }

        $this->afterCall($response);

        return $response;
    }


    private function checkInteraction(CFHIRInteraction $interaction): void
    {
        // todo il faut check que l'interaction pour la ressource demandé est autorisé
        // $resource = $request->getResource();
        // if ($this->get) {
        //        $available_interactions = $enc_sender_fhir->getAvailableInteractions($resource);
        //        $interaction_name       = $interaction::NAME;
        //        if (!in_array($interaction_name, $available_interactions)) {
        //          thow exeception;
        //         }
        // }
    }

    private function beforeCall(): void
    {
        if ($this->_exchange_fhir) {
            $this->_exchange_fhir->send_datetime = CMbDT::dateTime();
        }
    }

    /**
     * @param Response $response
     *
     * @throws Exception
     */
    private function afterCall(Response $response): void
    {
        if (!$this->_exchange_fhir) {
            return;
        }

        $this->_exchange_fhir->response_datetime = CMbDT::dateTime();

        if (!$body = $response->getBody()) {
            if ($msg = $this->_exchange_fhir->store()) {
                throw new CFHIRException($msg);
            }

            return;
        }

        $this->_exchange_fhir->statut_acquittement = in_array($response->getStatusCode(), [200,201]) ? "ok" : 'err';
        $this->_exchange_fhir->acquittement_valide = 1;
        $this->_exchange_fhir->_acquittement       = $body;
        $this->_exchange_fhir->store();
    }

    /**
     * @param string $source_profile
     *
     * @return null|CSourceHTTP
     */
    public function getSource(string $source_profile): ?CSourceHTTP
    {
        if ($this->_source && $this->_source->_id) {
            return $this->_source;
        }

        /** @var CSourceHTTP $source */
        $source = CExchangeSource::get("{$this->_guid}-{$source_profile}");

        return $this->_source = ($source && $source->_id) ? $source : null;
    }

    /**
     * Generate exchange FHIR
     *
     * @param CFHIRInteraction $interaction
     * @param array|null       $data
     * @param bool             $message_valid
     *
     * @return CExchangeFHIR
     * @throws Exception
     */
    private function generateExchange(CFHIRInteraction $interaction, ?array $data, bool $message_valid): CExchangeFHIR
    {
        $body    = CMbArray::get($data, 'data');
        $exchange_fhir                  = new CExchangeFHIR();
        $exchange_fhir->date_production = CMbDT::dateTime();
        $exchange_fhir->receiver_id     = $this->_id;
        $exchange_fhir->group_id        = $this->group_id;
        $exchange_fhir->format          = $interaction->getFormat();
        $exchange_fhir->message_valide  = $message_valid ? 1 : 0;
        $exchange_fhir->_message        = $body;
        $exchange_fhir->type            = $interaction::NAME;
        $exchange_fhir->sous_type       = $interaction->resourceType;

        $exchange_fhir->store();

        return $this->_exchange_fhir = $exchange_fhir;
    }

    /**
     * @inheritDoc
     */
    public function loadRefsMessagesSupported()
    {
        $messages = parent::loadRefsMessagesSupported();

        // build class map fhir
        $this->getActorClassMap();

        return $messages;
    }

    /**
     * @return CFHIRResource[]
     * @throws Exception
     */
    public function getAvailableResources(): array
    {
        return $this->getAvailableResourcesForActor($this);
    }

    /**
     * @param CFHIRResource $resource
     *
     * @return string[]
     * @throws Exception
     */
    public function getAvailableInteractions(CFHIRResource $resource): array
    {
        return $this->getAvailableInteractionsForActor($this, $resource);
    }

    /**
     * @param CFHIRResource $resource
     *
     * @return string[]
     * @throws Exception
     */
    public function getAvailableProfiles(CFHIRResource $resource): array
    {
        return $this->getAvailableProfilesForActor($this, $resource);
    }

    /**
     * @param string $canonical_or_type canonical | resource_type | resource_class
     *
     * @return CFHIRResource|null
     */
    public function getResource(string $canonical_or_type): ?CFHIRResource
    {
        $resource = $this->getResourceTrait($canonical_or_type);
        $resource->setInteropActor($this);

        return $resource;
    }

    /**
     * @param CFHIRResource         $resource
     * @param CFHIRInteraction[] $interactions
     *
     * @return array|CStoredObject[]|CMessageSupported[]
     * @throws Exception
     */
    public function getMessagesSupportedForResource(CFHIRResource $resource, array $interactions = []): array
    {
        return $this->getMessagesSupportedForActor($this, $resource, $interactions);
    }
}
