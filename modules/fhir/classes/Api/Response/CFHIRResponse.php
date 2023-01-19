<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Api\Response;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Interop\Fhir\Controllers\CFHIRController;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Serializers\CFHIRSerializer;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * FHIR generic resource
 */
class CFHIRResponse implements IShortNameAutoloadable
{
    /** @var string */
    public const NS = "http://hl7.org/fhir";

    /** @var string HTTP code to output */
    public const HTTP_CODE = 200;

    /** @var int */
    public const SEARCH_MAX_ITEMS = 50;

    /** @var array */
    public static $headers = [];

    /** @var CFHIRResource|null */
    protected $resource;

    /** @var string */
    protected $format;

    /** @var CFHIRInteraction */
    protected $interaction;

    /**
     * CFHIRResponse constructor.
     *
     * @param CFHIRResource $resource
     * @param string        $format
     */
    public function __construct(CFHIRInteraction $interaction, string $format)
    {
        $this->interaction = $interaction;
        $this->resource    = $interaction->getResource();
        $this->format      = $format;
    }

    /**
     * @return int
     */
    protected function getStatusCode(): int
    {
        if ($this->interaction instanceof CFHIRInteractionCreate) {
            return 201;
        }

        return self::HTTP_CODE;
    }

    /**
     ** CFHIRResponse output.
     *
     * @return Response
     *
     * @throws InvalidArgumentException
     * @throws CFHIRException
     */
    public function output(): Response
    {
        $response = new Response();

        $response->headers->set("content-type", $this->format);
        $response->setStatusCode($this->getStatusCode());

        if ($this->resource) {
            // no print resource if only id in resource in create interaction
            $is_create = $this->interaction instanceof CFHIRInteractionCreate;
            if (!$is_create || $this->resource->getMeta()) {
                $pretty_parameter = $this->resource->getParameterSearch('_pretty');
                $pretty           = $pretty_parameter ? $pretty_parameter->getValue() : false;
                $serializer = CFHIRSerializer::serialize(
                    $this->resource,
                    $this->format,
                    [CFHIRSerializer::OPTION_OUTPUT_PRETTY => $pretty]
                );

                $serializedResource = $serializer->getResourceSerialized();

                $response->setContent($serializedResource);
            }

            $resource_id = $this->resource->getResourceId();
            if ($this->interaction instanceof CFHIRInteractionCreate && $this->getStatusCode() === 201 && $resource_id) {
                self::$headers['Location'] = CFHIRController::getUrl(
                    'fhir_read',
                    ['resource' => $this->resource::RESOURCE_TYPE, 'resource_id' => $resource_id]
                );
            }
        }

        foreach ($this::$headers as $_header_key => $_header_value) {
            $response->headers->set($_header_key, $_header_value);
        }

        return $response;
    }
}
