<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Interactions;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CClassMap;
use Ox\Core\CMbString;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Eai\CMessageSupported;
use Ox\Interop\Fhir\Actors\CReceiverFHIR;
use Ox\Interop\Fhir\Api\Request\CRequestFormats;
use Ox\Interop\Fhir\Api\Response\CFHIRResponse;
use Ox\Interop\Fhir\CExchangeFHIR;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Exception\CFHIRExceptionNotSupported;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Utilities\SearchParameters\AbstractSearchParameter;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameter;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterString;

/**
 * Description
 */
class CFHIRInteraction implements IShortNameAutoloadable
{
    /** @var string Name */
    public const NAME = '';

    /** @var string[] */
    public const INTERACTIONS = [
        CFHIRInteractionCapabilities::class,
        CFHIRInteractionCreate::class,
        CFHIRInteractionDelete::class,
        CFHIRInteractionHistory::class,
        CFHIRInteractionRead::class,
        CFHIRInteractionSearch::class,
        CFHIRInteractionUpdate::class,
    ];

    /** @var string */
    public const METHOD = 'GET';

    /** @var string Profil
     * @see CReceiverFHIR::getSpec()->messages
     */
    public $profil;

    /** @var string Resource type */
    public $resourceType;

    /** @var string Format */
    public $format;

    // todo remove ?
    /** @var string Complement URL */
    public $complement_url;

    /** @var array Parameters */
    public $query_parameters = [];

    // todo remove _sender et _receiver
    /** @var CReceiverFHIR */
    public $_receiver;

    /** @var CInteropSender */
    public $_sender;

    /** @var CFHIRResource|null */
    public $resource;

    public ?CExchangeFHIR $_ref_fhir_exchange = null;

    /** @var CMessageSupported */
    private $message_supported;

    /**
     * CFHIRInteraction constructor.
     *
     * @param string|CFHIRResource $resource Resource type to search in (Patient, etc)
     * @param string               $format   Requested return type
     */
    public function __construct($resource = null, ?string $format = '')
    {
        $this->setResource($resource);

        $this->setFormat($format);

        $this->profil = CReceiverFHIR::PROFILE_FHIR; // todo a ref
    }

    /**
     * Make Interaction object
     *
     * @param string $interaction Interaction name, possibly with heading "_"
     *
     * @return self
     * @throws CFHIRException
     */
    public static function make(string $interaction): self
    {
        if (!$interaction) {
            throw new CFHIRException("Empty interaction name");
        }

        $interaction_class = preg_replace('/[^\w]/', ' ', $interaction);
        $interaction_class = CMbString::capitalize(strtolower($interaction_class));
        $interaction_class = str_replace([' ', '_'], '', $interaction_class);

        $class = "CFHIRInteraction" . $interaction_class;
        if (!class_exists($class)) {
            throw new CFHIRException("Unsupported interaction '$interaction'");
        }

        return new $class();
    }

    /**
     * @param string $name
     *
     * @return string<static>|null
     * @throws Exception
     */
    public static function getFromName(string $name): ?string
    {
        $interactions = CClassMap::getInstance()->getClassChildren(self::class, false, true);

        foreach ($interactions as $interaction) {
            if ($interaction::NAME === $name) {
                return $interaction;
            }
        }

        return null;
    }

    /**
     * Get the resource method name
     *
     * @return string
     */
    public function getResourceMethodName(): string
    {
        $names = array_map(
            function ($name) {
                return ucfirst($name);
            },
            explode('-', $this::NAME)
        );

        $name = implode('', $names);

        return "interaction$name";
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param CMessageSupported $message_supported
     *
     * @return $this
     */
    public function setMessageSupported(CMessageSupported $message_supported): self
    {
        $this->message_supported = $message_supported;

        return $this;
    }

    /**
     * @return CMessageSupported
     */
    public function getMessageSupported(): CMessageSupported
    {
        return $this->message_supported;
    }

    /**
     * Add a parameter to the query
     *
     * @param string      $field    Field to search in
     * @param string|null $value    Value to search
     * @param string|null $type     Class of type for search
     * @param string|null $modifier Modifier
     *
     * @return void
     */
    public function addQueryParameter(string $field, ?string $value, ?string $modifier = null, ?string $type = SearchParameterString::class): void
    {
        if ($value === null || $value === "") {
            return;
        }

        if (!is_subclass_of($type, AbstractSearchParameter::class)) {
            return;
        }

        /** @var AbstractSearchParameter $type */
        $type = new $type($field);

        $this->query_parameters[] = new SearchParameter($type, $value, $modifier);
    }

    /**
     * @param SearchParameter $parameter
     */
    public function addParameter(SearchParameter $parameter): void
    {
        $this->query_parameters[] = $parameter;
    }

    /**
     * Build the query
     *
     * @param array|string|null $data
     *
     * @return array
     */
    public function buildQuery($data = null): array
    {
        return [
            "event" => $this->getPath(),
            "data"  => $this->getBody($data),
        ];
    }

    public function buildQueryParameters(): array
    {
        $parameters   = $this->query_parameters;
        $parameters[] = new SearchParameter(new SearchParameterString('_format'), $this->format);

        $params = [];
        foreach ($parameters as $_param) {
            $params[] = $_param->toQuery();
        }

        if ($this->complement_url) {
            $params[] = urlencode($this->complement_url);
        }

        return $params;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        if (!$parameters = $this->buildQueryParameters()) {
            return $this->getBasePath();
        }

        return $this->getBasePath() . '?' . implode("&", $parameters);
    }

    /**
     * @return string
     */
    public function getBasePath(): ?string
    {
        return $this->resourceType;
    }

    /**
     * @param array|null|string $data
     *
     * @return string|null
     */
    public function getBody($data): ?string
    {
        return null;
    }

    /**
     * Handles the intercation result to make a response
     *
     * @param CFHIRResource $resource FHIR resource
     * @param mixed         $result   Result
     *
     * @return CFHIRResponse
     * @throws CFHIRException
     */
    public function handleResult(CFHIRResource $resource, $result): CFHIRResponse
    {
        return new CFHIRResponse($this, $this->format);
    }

    /**
     * @return CFHIRResource|null
     */
    public function getResource(): ?CFHIRResource
    {
        return $this->resource;
    }

    /**
     * @param string|CFHIRResource $resource
     *
     * @return self
     * @throw CFHIRExceptionNotSupported
     */
    public function setResource($resource): self
    {
        if (is_object($resource) && !$resource instanceof CFHIRResource) {
            throw new CFHIRExceptionNotSupported('The object : "' . get_class($resource) . '" is not a resource fhir');
        }

        if (is_string($resource) && !is_subclass_of($resource, CFHIRResource::class)) {
            throw new CFHIRExceptionNotSupported("'$resource' : is not a resource fhir");
        }

        if (is_string($resource)) {
            $resource = new $resource();
        }

        if ($this->resource = $resource) {
            $this->resourceType = $resource::RESOURCE_TYPE;
        }

        return $this;
    }

    /**
     * @param string|null $format
     *
     * @return self
     */
    public function setFormat(?string $format): self
    {
        if (!in_array($format, [CRequestFormats::CONTENT_TYPE_XML, CRequestFormats::CONTENT_TYPE_JSON,], true)) {
            $format = null;
        }

        $this->format = $format ?: CRequestFormats::CONTENT_TYPE_XML;

        return $this;
    }
}
