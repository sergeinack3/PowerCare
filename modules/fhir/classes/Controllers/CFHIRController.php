<?php

/**
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Controllers;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CController;
use Ox\Core\Chronometer;
use Ox\Core\CMbDT;
use Ox\Core\Kernel\Routing\RouterBridge;
use Ox\Interop\Connectathon\CBlink1;
use Ox\Interop\Eai\CMessageSupported;
use Ox\Interop\Fhir\Actors\CSenderFHIR;
use Ox\Interop\Fhir\Api\Request\CRequestFormats;
use Ox\Interop\Fhir\Api\Request\CRequestInteraction;
use Ox\Interop\Fhir\Api\Request\CRequestResource;
use Ox\Interop\Fhir\Api\Response\CFHIRResponse;
use Ox\Interop\Fhir\CExchangeFHIR;
use Ox\Interop\Fhir\ClassMap\FHIRClassMap;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Exception\CFHIRExceptionForbidden;
use Ox\Interop\Fhir\Exception\CFHIRExceptionInformational;
use Ox\Interop\Fhir\Exception\CFHIRExceptionInvalidValue;
use Ox\Interop\Fhir\Exception\CFHIRExceptionNotSupported;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCapabilities;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\R4\CapabilityStatement\CFHIRResourceCapabilityStatement;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CExchangeHTTP;
use Ox\Mediboard\System\CSenderHTTP;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Description
 */
class CFHIRController extends CController
{
    /** @var CSenderHTTP */
    public static $sender_http;

    /** @var CExchangeHTTP */
    public static $exchange_http;

    /** @var CExchangeFHIR */
    public static $exchange_fhir;

    /** @var string */
    public static $exchange_status;

    /** @var Chronometer */
    public static $chrono;

    /**
     * @throws Exception
     */
    public static function initBlink1(): void
    {
        $blink = CBlink1::getInstance();
        $blink->addPattern(CFHIR::BLINK1_UNKNOW, "3,#0489B1,0.5,#000000,0.5");
        $blink->addPattern(CFHIR::BLINK1_ERROR, "3,#FF0000,0.5,#000000,0.5");
        $blink->addPattern(CFHIR::BLINK1_WARNING, "3,#FFFF00,0.5,#000000,0.5");
        $blink->addPattern(CFHIR::BLINK1_OK, "3,#3ADF00,0.5,#000000,0.5");
    }

    /**
     * Determine type of fhir resource called
     *
     * @param Request $request
     *
     * @return string|null
     */
    public static function determineTypeResource(Request $request): ?string
    {
        // force capability statement when call /meta
        if ($request->attributes->get('object_class') === CFHIRInteractionCapabilities::class) {
            return CFHIRResourceCapabilityStatement::RESOURCE_TYPE;
        }

        // type defined on parameter "resource" on path of the route api
        if ($resource_type = $request->attributes->get('resource')) {
            return $resource_type;
        }

        return null;
    }

    /**
     * @param Request $request
     *
     * @throws Exception
     */
    public static function start(Request $request): void
    {
        // chrono
        CApp::$chrono->stop();
        self::$chrono = new Chronometer();
        self::$chrono->start();

        $method          = $request->getMethod();
        $content_request = ($method == "POST") ? $request->getContent() : $request->getRequestUri();
        $format          = (new CRequestFormats($request))->getFormat();
        $validation      = true;
        $group           = CGroups::get();

        $str   = $request->getProtocolVersion() . "\r\n";
        $str   .= $request->headers->__toString();
        $input = $str . "\r\n" . $content_request;

        // resolve interaction
        $interaction = null;
        if ($interaction_class = $request->attributes->get('object_class')) {
            $interaction = new $interaction_class();
            if (!$interaction instanceof CFHIRInteraction) {
                $interaction = null;
            }
        }

        // récupération du sender
        $sender_http = self::$sender_http;

        // Création de l'échange HTTP
        $exchange_http                = new CExchangeHTTP();
        $exchange_http->date_echange  = "now";
        $exchange_http->destinataire  = CAppUI::conf("mb_id");
        $exchange_http->function_name = $method;
        $exchange_http->input         = serialize($input);
        $exchange_http->emetteur      = $sender_http ? $sender_http->nom : CAppUI::tr("Unknown");
        if (!$sender_http) {
            $exchange_http->http_fault = 1;
        }
        $exchange_http->response_time = self::$chrono->total;
        $exchange_http->store();

        // création de l'échange FHIR
        $exchange_fhir                  = new CExchangeFHIR();
        $exchange_fhir->date_production = CMbDT::dateTime();
        $exchange_fhir->sender_class    = 'CSenderHTTP';
        $exchange_fhir->sender_id       = $sender_http ? $sender_http->_id : null;
        $exchange_fhir->group_id        = $group->group_id;
        $exchange_fhir->format          = $format;
        $exchange_fhir->message_valide  = $validation ? 1 : 0;
        $exchange_fhir->_message        = $content_request;
        $exchange_fhir->type            = $interaction ? $interaction::NAME : null;
        $exchange_fhir->sous_type       = self::determineTypeResource($request);
        $exchange_fhir->store();

        // if sender not found ==> forbidden access but we log the exchange
        if (!self::$sender_http) {
            throw new CFHIRExceptionForbidden();
        }

        // load configs
        $sender_http->getConfigs($exchange_fhir);

        self::$exchange_status = 'ok';
        self::$exchange_fhir   = $exchange_fhir;
        self::$exchange_http   = $exchange_http;
        self::$sender_http     = $sender_http;
    }

    /**
     * @param Request $request
     *
     * @throws Exception
     */
    public static function resolveResource(Request $request): void
    {
        $request_resource = new CRequestResource($request);
        if (!$resource_type = $request_resource->getResourceType()) {
            throw new CFHIRExceptionNotSupported('It is impossible to resolve type of FHIR Resource');
        }

        // check coherance between type and profile
        if ($profile = $request_resource->getProfile()) {
            $map      = new FHIRClassMap();
            $resource = $map->resource->getResource($profile);
            if ($resource::RESOURCE_TYPE !== $resource_type) {
                throw new CFHIRExceptionInvalidValue(
                    "The resource type '$resource_type' and profile '$profile' required are not compatible"
                );
            }
        }

        $request->attributes->set(CRequestResource::KEY_INTERN_PROFILE, $profile);
        $request->attributes->set(CRequestResource::KEY_INTERN_RESOURCE_TYPE, $resource_type);
    }

    /**
     * @param Request     $request
     * @param CSenderHTTP $sender_http
     *
     * @throws Exception
     */
    public static function validateInteraction(Request $request): void
    {
        $enc_sender_fhir = new CSenderFHIR(self::$sender_http);

        // target resource
        $interaction_class = $request->attributes->get('object_class');
        if (!$interaction_class || !CClassMap::getInstance()->getClassMap($interaction_class)) {
            throw new CFHIRExceptionInformational('Interaction class is not defined or invalid in route definition');
        }

        /** @var CFHIRInteraction $interaction */
        $interaction = new $interaction_class();

        // determine resource
        $request_resource = new CRequestResource($request);
        $resource_type    = $request_resource->getResourceType();
        $profile          = $request_resource->getProfile();

        // is resource supported
        if (!$resource = $request_resource->getResource()) {
            $message = "Resource '$resource_type' not supported" .
                ($request_resource->getProfile() ? " with profile '$profile'" : "");
            throw new CFHIRExceptionNotSupported($message);
        }

        // interaction is supported
        $available_interactions = $enc_sender_fhir->getAvailableInteractions($resource);
        if (!in_array($interaction::NAME, $available_interactions)) {
            $version = $resource->getResourceVersion();
            $message = "Interaction '" . $interaction::NAME . "' not supported on resource '$resource_type'[$version]" .
                ($request_resource->getProfile() ? " with profile '$profile'" : "");
            throw new CFHIRExceptionNotSupported($message);
        }
        $messages_supported = $enc_sender_fhir->getMessagesSupportedForResource($resource);
        $messages_supported = array_filter(
            $messages_supported,
            function (CMessageSupported $message_supported) use ($interaction) {
                return (new $message_supported->message)::NAME === $interaction::NAME;
            }
        );

        $message_supported = reset($messages_supported);
        $request->attributes->set(CRequestInteraction::KEY_INTERN_MESSAGE_SUPPORTED, $message_supported->_id);
    }

    /**
     * @param Request $request
     * @param CUser   $user
     *
     * @return void
     * @throws Exception
     */
    public static function authenticateSender(Request $request, CUser $user): void
    {
        $enc_sender_fhir = new CSenderFHIR();

        // Chargement du sender HTTP
        if ($sender_http = $enc_sender_fhir->loadFromUser($user)) {
            // keep sender
            self::$sender_http = $sender_http;
            $request->attributes->set('fhir_sender_id', $sender_http->_id);
        }
    }

    /**
     * @param Response $response
     *
     * @throws Exception
     */
    public static function stop(Response $response): void
    {
        $statut_acquittement = $response->getStatusCode() >= 400 ? 'err' : self::$exchange_status;
        $str                 = 'HTTP/' . $response->getProtocolVersion() . " " . $response->getStatusCode() . "\r\n";
        $str                 .= $response->headers->__toString();
        $output              = $str . "\r\n" . $response->getContent();

        if ($exchange_http = self::$exchange_http) {
            $exchange_http->date_echange  = CMbDT::dateTime();
            $exchange_http->output        = serialize($output);
            $exchange_http->response_time = self::$chrono->total;
            $exchange_http->store();
        }

        if ($exchange_fhir = self::$exchange_fhir) {
            $exchange_fhir->send_datetime       = CMbDT::dateTime();
            $exchange_fhir->statut_acquittement = $statut_acquittement;
            $exchange_fhir->acquittement_valide = 1;
            $exchange_fhir->_acquittement       = $response->getContent();
            $exchange_fhir->store();
        }

        CApp::$chrono->start();
    }

    /**
     * @warning do not use in production need ref with cached url generator
     *
     * @param string $route_name
     * @param array  $parameters
     *
     * @return string
     * @throws CFHIRException
     * @deprecated
     */
    public static function getUrl($route_name, $parameters = [])
    {
        $instance = new self();

        if (!$instance->container) {
            return RouterBridge::generateUrl(
                $route_name,
                $parameters,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $instance->generateUrl(
            $route_name,
            $parameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * @param CFHIRResponse $response
     *
     * @return Response
     */
    public function renderFHIRResponse(CFHIRResponse $response): Response
    {
        return $response->output();
    }
}
