<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbSecurity;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Fhir\Actors\CReceiverFHIR;
use Ox\Interop\Fhir\Actors\CSenderFHIR;
use Ox\Interop\Fhir\Actors\IActorFHIR;
use Ox\Interop\Fhir\Api\Response\CFHIRResponse;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectHandleInterface;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectSearcherInterface;
use Ox\Interop\Fhir\Contracts\Mapping\ResourceMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeMeta;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Exception\CFHIRExceptionNotFound;
use Ox\Interop\Fhir\Exception\CFHIRExceptionNotSupported;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\R4\CFHIRDefinition;
use Ox\Interop\Fhir\Serializers\CFHIRParser;
use Ox\Interop\Fhir\Serializers\CFHIRSerializer;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameter;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterBool;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterNumber;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterString;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CSenderHTTP;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * FHIR generic resource
 */
class CFHIRResource implements ResourceInterface
{
    // constants
    /** @var string[] */
    public const FHIR_RESOURCE_AVAILABLE = [
        'R4' => self::FHIR_VERSION_R4,
    ];

    /** @var string */
    public const FHIR_VERSION_R4 = '4.0';

    /** @var string */
    public const RESOURCE_TYPE = '';

    /** @var string */
    public const PROFILE_TYPE = '';

    /** @var CFHIR */
    public const PROFILE_CLASS = CFHIR::class;

    /** @var string */
    public const VERSION_NORMATIVE = '';

    /** @var string[] */
    public const FORCE_AVAILABLE_FHIR_VERSIONS = [];

    /** @var string */
    public const RESOURCE_CONTEXT_PROFILING = '';

    /** @var bool */
    protected const ACCEPT_VERSION_ID = false;

    /** @var int */
    private const FHIR_VERSION_PART = 4;

    // Resource attributes
    protected ?CFHIRDataTypeString $id = null;

    protected ?CFHIRDataTypeMeta $meta = null;

    protected ?CFHIRDataTypeUri $implicitRules = null;

    protected ?CFHIRDataTypeCode $language = null;

    // Object attributes
    /** @var CSenderHTTP */
    public $_sender;

    /** @var CReceiverFHIR */
    public $_receiver;

    /** @var CFHIRInteraction */
    protected $interaction;

    /** @var mixed */
    protected $object;

    /** @var CCapabilitiesResource */
    protected $capabilities;

    /** @var array<ParameterBag, ParameterBag> */
    public $parameters;

    /** @var bool */
    protected $summary = false;

    /** @var DelegatedObjectMapperInterface|ResourceMappingInterface */
    protected $object_mapping;

    /** @var DelegatedObjectHandleInterface */
    protected $object_handle;

    /** @var DelegatedObjectSearcherInterface */
    protected $object_searcher;

    /** @var bool */
    protected $is_contained = false;

    /**
     * @param bool $pretty
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function toXML(bool $pretty = false): string
    {
        $serializer = CFHIRSerializer::serialize($this, 'xml', ['pretty' => $pretty]);

        return $serializer->getResourceSerialized();
    }

    /**
     * @param bool $pretty
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function toJSON(bool $pretty = false): string
    {
        $serializer = CFHIRSerializer::serialize($this, 'json', ['pretty' => $pretty]);

        return $serializer->getResourceSerialized();
    }

    /**
     * Get resource type
     *
     * @return string
     */
    public function getResourceType(): string
    {
        return $this::RESOURCE_TYPE;
    }

    /**
     * @return string
     */
    public static function getCanonical(): string
    {
        $end = static::PROFILE_TYPE ?: static::RESOURCE_TYPE;

        return trim((static::PROFILE_CLASS)::BASE_PROFILE, '/') . "/" . $end;
    }

    /**
     * @param CFHIRInteraction $interaction
     */
    public function setInteraction(?CFHIRInteraction $interaction): void
    {
        $this->interaction = $interaction;
    }

    /**
     * @return CFHIRInteraction|null
     */
    public function getInteraction(): ?CFHIRInteraction
    {
        return $this->interaction;
    }

    /**
     * @return string[]
     */
    public function getAvailableFHIRVersions(): array
    {
        $versions           = [];
        $normative_version  = $this::VERSION_NORMATIVE;
        $available_versions = $this::FHIR_RESOURCE_AVAILABLE;
        $resource           = $this->getFhirParentResource();
        $base_fhir_version  = $resource->getResourceFHIRVersion();

        foreach ($available_versions as $version) {
            $add = $normative_version && $version >= $normative_version;
            $add = $add || $version === $base_fhir_version;
            $add = $add || in_array($version, $this::FORCE_AVAILABLE_FHIR_VERSIONS);
            if ($add) {
                $versions[] = $version;
            }
        }

        return $versions;
    }

    /**
     * @return $this
     */
    public function getFhirParentResource(): self
    {
        if ($this->isFHIRResource()) {
            return $this;
        }

        $exception = new CFHIRException(
            'The class "' . get_class($this) . '" should be has a parent declared like FHIR resource.'
        );

        foreach (class_parents($this) as $class) {
            if ($class === CFHIRResource::class) {
                throw $exception;
            }

            /** @var CFHIRResource $resource */
            $resource = new $class();
            if ($resource->isFHIRResource()) {
                return $resource;
            }
        }

        throw $exception;
    }

    /**
     * @return bool
     */
    public function isFHIRResource(): bool
    {
        return str_starts_with($this->getCapabilities()->getProfile(), CFHIR::BASE_PROFILE);
    }

    /**
     * @return bool
     */
    public function isProfileResource(): bool
    {
        return !$this->isFHIRResource();
    }

    /**
     * @return CCapabilitiesResource
     */
    public function getCapabilities(): CCapabilitiesResource
    {
        if ($this->capabilities) {
            return $this->capabilities;
        }

        return $this->capabilities = $this->generateCapabilities();
    }

    /**
     * @return string
     */
    public function getProfile(): string
    {
        return $this->getCapabilities()->getProfile();
    }

    /**
     * @param CCapabilitiesResource $capabilities
     */
    public function setCapabilities(CCapabilitiesResource $capabilities): void
    {
        $this->capabilities = $capabilities;
    }

    /**
     * @return CCapabilitiesResource
     */
    protected function generateCapabilities(): CCapabilitiesResource
    {
        $version_part = explode('\\', get_class($this))[self::FHIR_VERSION_PART];
        $fhir_version = CMbArray::get(self::FHIR_RESOURCE_AVAILABLE, $version_part);

        return $this->capabilities = (new CCapabilitiesResource())
            ->setType($this::RESOURCE_TYPE)
            ->setProfile(($this::PROFILE_CLASS)::BASE_PROFILE . ($this::PROFILE_TYPE ?: $this::RESOURCE_TYPE))
            ->setVersion($fhir_version)
            ->addSearchAttributes(
                [
                    new SearchParameterNumber('_id'),
                    new SearchParameterNumber('_count'),
                    new SearchParameterNumber('_offset'),
                    new SearchParameterString('_profile'),
                    new SearchParameterString('_include'),
                    new SearchParameterString('_type'),
                    new SearchParameterString('_format'),
                    new SearchParameterString('_summary'),
                    new SearchParameterBool('_pretty'),
                ]
            );
    }

    /**
     * @param DelegatedObjectMapperInterface $object_mapper
     *
     * @return $this
     */
    public function setMapper(DelegatedObjectMapperInterface $object_mapper): self
    {
        $this->object_mapping = $object_mapper;

        return $this;
    }

    /**
     * @param CStoredObject $object
     *
     * @return DelegatedObjectMapperInterface|null
     * @deprecated use system with configuration
     *
     */
    protected function setMapperOld(CStoredObject $object): ?DelegatedObjectMapperInterface
    {
        return null;
    }

    /**
     * Return the fhir version of resource
     *
     * @return string
     */
    public function getResourceFHIRVersion(): string
    {
        $resource = $this->getFhirParentResource();

        return $resource->getResourceVersion();
    }

    /**
     * Return the version of resource or profile
     *
     * @return string
     */
    public function getResourceVersion(): string
    {
        return $this->getCapabilities()->getVersion();
    }

    /**
     * Get resource Id
     *
     * @return string
     */
    public function getResourceId(): ?string
    {
        if (!$this->id) {
            return null;
        }

        return $this->id->getValue();
    }

    /**
     * @param CInteropActor|null $actor
     */
    public function setInteropActor(?CInteropActor $actor): void
    {
        if ($actor instanceof CInteropReceiver) {
            $this->_receiver = $actor;
        } elseif ($actor instanceof CInteropSender) {
            $this->_sender = $actor;
        }
    }

    /**
     * @return  CInteropActor|null|IActorFHIR $actor
     */
    public function getInteropActor(): ?CInteropActor
    {
        return $this->_receiver ?: $this->_sender;
    }

    public function getReceiver(): ?CReceiverFHIR
    {
        return $this->_receiver;
    }

    public function getSender(): ?CSenderHTTP
    {
        return $this->_sender;
    }

    /**
     * Process data
     *
     * @param CFHIRInteraction  $interaction
     * @param array|string|null $data Data
     *
     * @return CFHIRResponse
     * @throws CFHIRException
     *
     */
    public function process(CFHIRInteraction $interaction, $data = null): CFHIRResponse
    {
        $this->interaction = $interaction;
        $this->interaction->setResource($this);

        $method = $this->interaction->getResourceMethodName();
        if (!method_exists($this, $method)) {
            $name = $interaction::NAME;
            throw new CFHIRException("Unknown interaction type: '$name'", 404);
        }

        $result = $this->$method($data);

        return $this->interaction->handleResult($this, $result);
    }

    /**
     * @param array|string|null $data
     * @param string|null       $format
     *
     * @return CFHIRParser
     * @throws InvalidArgumentException
     */
    public function interactionCreate($data, ?string $format = null): CFHIRParser
    {
        return CFHIRParser::parse($data, $format);
    }

    /**
     * @return $this
     */
    public function buildSelf(): self
    {
        /** @var CFHIRResource $resource */
        return $this->buildFrom(new $this());
    }

    /**
     * @param CFHIRResource|null $resource
     *
     * @return mixed|CFHIRResource|null
     */
    public function buildFrom(CFHIRResource $resource = null): CFHIRResource
    {
        if (!$resource) {
            $resource = new $this();
        }

        $resource->parameters  = $this->parameters;
        $resource->_sender     = $this->_sender;
        $resource->_receiver   = $this->_receiver;
        $resource->interaction = $this->interaction;

        return $resource;
    }

    /**
     * @param mixed $object
     */
    public function setObject($object): void
    {
        $this->object = $object;
    }

    /**
     * @param mixed $object
     *
     * @return CFHIRResource
     * @throws InvalidArgumentException
     * @throws Exception
     */
    final public function mapFrom($object): self
    {
        $this->object = $object;

        $delegated_object_mapper = $this->getDelegatedMapper();
        if (!$delegated_object_mapper) {
            throw new CFHIRException(CAppUI::tr("ResourceInterface-msg-empty for mapping"));
        }

        // support mapping
        if (!$delegated_object_mapper->isSupported($this, $object)) {
            throw new CFHIRExceptionNotSupported(
                CAppUI::tr(
                    "DelegatedObjectMapperInterface-msg-not supported",
                    get_class($this),
                    (string) $object
                )
            );
        }

        $delegated_object_mapper->setResource($this, $object);

        $this->object_mapping = $delegated_object_mapper->getMapping();

        if (!$this->object) {
            $this->object = $object;
        }

        // summary resource
        $summary = $this->getParameterSearch('_summary');
        if ($summary && $summary->getValue() === 'true') {
            $this->summary = true;
        }

        // link datatype with resource ($this)
        foreach (CFHIRDefinition::getFields($this) as $field) {
            // map properties
            $method_map = 'map' . ucfirst($field);
            if (method_exists($this, $method_map)) {
                $this->$method_map();
            }

            /** @var CFHIRDataType $field */
            if (!$fields = $this->{$field}) {
                continue;
            }

            if (!is_array($fields)) {
                $fields = [$fields];
            }

            /** @var CFHIRDataType $_field */
            foreach ($fields as $_field) {
                $_field->setParentResource($this);
                $_field->setParent($this);
            }
        }

        return $this;
    }

    /**
     * Try to find a mapper from this resource
     *
     * @return DelegatedObjectMapperInterface|null
     * @throws Exception
     */
    public function getDelegatedMapper(): ?DelegatedObjectMapperInterface
    {
        if ($this->object_mapping) {
            return $this->object_mapping;
        }

        // old system, don't use it now, it will be deleted
        if ($this->object && ($object_mapping = $this->setMapperOld($this->object))) {
            return $object_mapping;
        }

        // try to find it from actor
        if ($actor = $this->getInteropActor()) {
            if (!$actor instanceof IActorFHIR) {
                $actor = new CSenderFHIR($actor);
            }

            if ($object_mapping = $actor->getDelegatedMapper($this)) {
                return $object_mapping;
            }
        }

        return null;
    }

    /**
     * @return DelegatedObjectSearcherInterface|null
     * @throws Exception
     */
    public function getDelegatedSearcher(): ?DelegatedObjectSearcherInterface
    {
        if ($this->object_searcher) {
            return $this->object_searcher;
        }


        // try to find it from actor
        if ($actor = $this->getInteropActor()) {
            if (!$actor instanceof IActorFHIR) {
                $actor = new CSenderFHIR($actor);
            }

            if ($object_mapping = $actor->getDelegatedSearcher($this)) {
                return $object_mapping;
            }
        }

        return null;
    }

    /**
     * Get delegated object handle
     *
     * @return DelegatedObjectHandleInterface|null
     * @throws Exception
     */
    public function getDelegatedHandle(): ?DelegatedObjectHandleInterface
    {
        if ($this->object_handle) {
            return $this->object_handle;
        }

        // try to find it from actor
        if ($actor = $this->getInteropActor()) {
            if (!$actor instanceof IActorFHIR) {
                $actor = new CSenderFHIR($actor);
            }

            if ($object_handle = $actor->getDelegatedHandle($this)) {
                return $object_handle;
            }
        }

        return null;
    }

    /**
     * Handle object in function of handle configured
     *
     * @param CFHIRResource|null $resource
     *
     * @return $this
     * @throws Exception
     */
    public function handle(?CFHIRResource $resource = null): ?CFHIRResource
    {
        if (!$resource) {
            $resource = $this;
        }

        if (!$delegated_handle = $this->getDelegatedHandle()) {
            throw new CFHIRExceptionNotFound('DelegatedObjectHandleInterface-msg-configuration none object');
        }

        return $delegated_handle->handle($resource);
    }

    /**
     * Map property id
     */
    protected function mapId(): void
    {
        $this->id = $this->object_mapping->mapId();
    }

    /**
     * Map property meta
     */
    protected function mapMeta(): void
    {
        $this->meta = $this->object_mapping->mapMeta();
    }

    /**
     * Map property language (only fr-FR => French (France) is supported)
     */
    protected function mapLanguage(): void
    {
        $this->language = $this->object_mapping->mapLanguage();
    }

    /**
     * Perform a history query based on the current object data
     *
     * @return CStoredObject|null
     * @throws CFHIRExceptionNotFound|Exception
     */
    public function interactionHistoryInstance(): ?CStoredObject
    {
        $object = null;
        if ($identifier = ($this->getId() ? $this->getId()->getValue() : null)) {
            $object = CStoredObject::loadByUuid($identifier);

            if ($object && !$object->_history) {
                $object->loadHistory();
            }
        }

        return $this->outWithPerm($object);
    }

    /**
     * @return CStoredObject|null
     */
    public function getObject(): CStoredObject
    {
        return $this->object;
    }

    /**
     * @param CStoredObject $object|null
     *
     * @return CStoredObject
     */
    protected function outWithPerm(?CStoredObject $object): ?CStoredObject
    {
        if (!$object || !$object->getPerm(PERM_READ || !$object->_id)) {
            return null;
        }

        return $object;
    }

    /**
     * @param CFHIRDataTypeString $id
     *
     * @return CFHIRResource
     */
    public function setId(?CFHIRDataTypeString $id): CFHIRResource
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getId(): ?CFHIRDataTypeString
    {
        return $this->id;
    }

    /**
     * @param CFHIRDataTypeMeta|null $meta
     *
     * @return CFHIRResource
     */
    public function setMeta(?CFHIRDataTypeMeta $meta): CFHIRResource
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @param CFHIRDataTypeUri|null $implicitRules
     *
     * @return CFHIRResource
     */
    public function setImplicitRules(?CFHIRDataTypeUri $implicitRules): CFHIRResource
    {
        $this->implicitRules = $implicitRules;

        return $this;
    }

    /**
     * @return CFHIRDataTypeUri|null
     */
    public function getImplicitRules(): ?CFHIRDataTypeUri
    {
        return $this->implicitRules;
    }

    /**
     * Map property ImplicitRules
     */
    protected function mapImplicitRules(): void
    {
        $this->implicitRules = $this->getImplicitRules();
    }


    /**
     * @param CFHIRDataTypeCode|null $language
     *
     * @return CFHIRResource
     */
    public function setLanguage(?CFHIRDataTypeCode $language): CFHIRResource
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getLanguage(): ?CFHIRDataTypeCode
    {
        return $this->language;
    }

    /**
     * @return CFHIRDataTypeMeta|null
     */
    public function getMeta(): ?CFHIRDataTypeMeta
    {
        return $this->meta;
    }

    /**
     * Perform a search query based on the current object data
     *
     * @param mixed $data Data to interact with
     *
     * @return CStoredObject
     * @throws Exception
     */
    public function interactionRead(?array $data): ?CStoredObject
    {
        $object = null;
        if ($identifier = ($this->getId() ? $this->getId()->getValue() : null)) {
            $object = CStoredObject::loadByUuid($identifier);
        }

        return $this->outWithPerm($object);
    }

    /**
     * Perform a search query based on the current object data
     *
     * @param array       $data Data to handle
     * @param string|null $format
     *
     * @return CStoredObject[]
     * @throws CFHIRExceptionNotFound
     * @throws Exception
     * @throws Exception
     */
    public function interactionSearchType(?array $data, ?string $format = null): array
    {
        $object_searcher = $this->getDelegatedSearcher();
        if (!$object_searcher) {
            throw new CFHIRException(CAppUI::tr("DelegatedObjectSearcherInterface-msg-configuration none object"));
        }

        // _id
        if ($param_id = $this->getParameterSearch('_id')) {
            $object = CStoredObject::loadByUuid($param_id->getValue());

            return [
                "list"     => $object ? [$object] : [],
                "total"    => $object ? 1 : 0,
                "step"     => 0,
                "offset"   => 0,
                "paginate" => false,
            ];
        }

        // limit
        $offset  = $this->getOffset();
        $objects = $object_searcher->search($this, $this->getLimit($offset));

        if ($count_parameter = $this->getParameterSearch('_count')) {
            $step = $count_parameter->getValue();
        } else {
            $step = $this->getLimit();
        }

        return [
            "list"     => $objects,
            "total"    => $object_searcher->getTotal(),
            "step"     => $step,
            "offset"   => $offset,
            "paginate" => true,
        ];
    }

    /**
     * @param array $data
     *
     * @return int
     */
    protected function getOffset(): int
    {
        if (!$offset_parameter = $this->getParameterSearch('_offset')) {
            return 0;
        }

        return (int)$offset_parameter->getValue();
    }

    /**
     * @param ParameterBag $params
     *
     */
    public function setParameterSearch(ParameterBag $params): void
    {
        if (!$this->parameters) {
            $this->parameters = ['fhir' => new ParameterBag(), 'all' => new ParameterBag()];
        }

        foreach ($params->all() as $key => $value) {
            if ($value instanceof SearchParameter) {
                $values   = $this->getSearchParameters($value->getParameterName()) ?: [];
                $values[] = $value;
                $this->parameters['fhir']->set($value->getParameterName(), $values);
            } else {
                $this->parameters['all']->set($key, $value);
            }
        }
    }

    /**
     * @param string $parameter_name
     *
     * @return SearchParameter|null
     */
    public function getParameterSearch(string $key): ?SearchParameter
    {
        if (!$this->parameters) {
            return null;
        }

        /** @var ParameterBag $parameters */
        if (!$parameters = $this->parameters['fhir'] ?? null) {
            return null;
        }

        /** @var SearchParameter[] $search_parameters */
        $search_parameters = $parameters->get($key);

        return $search_parameters ? end($search_parameters) : null;
    }

    /**
     * @param string $parameter_name
     *
     * @return SearchParameter[]|null
     */
    public function getSearchParameters(?string $key = null): ?array
    {
        if (!$this->parameters) {
            return null;
        }

        if (!$parameters = $this->parameters['fhir'] ?? null) {
            return null;
        }

        return $key ? $parameters->get($key) : $parameters->all();
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getParameterBrut(string $key)
    {
        if (!$this->parameters) {
            return null;
        }

        /** @var ParameterBag $parameters */
        $parameters = $this->parameters['all'] ?? null;

        if ($parameters) {
            return $parameters->get($key);
        }

        return null;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getParametersBrut()
    {
        if (!$this->parameters) {
            return null;
        }

        $parameters = $this->parameters['all'] ?? null;

        return $parameters ? $parameters->all() : null;
    }

    /**
     * @param array       $data
     * @param string|null $offset
     *
     * @return string
     */
    protected function getLimit(?string $offset = null): string
    {
        $limit = null;
        if ($count_parameter = $this->getParameterSearch('_count')) {
            $limit = $count_parameter->getValue();
        }

        // limit
        $limit = $limit ? min($limit, CFHIRResponse::SEARCH_MAX_ITEMS) : CFHIRResponse::SEARCH_MAX_ITEMS;

        // add offset
        if ($offset) {
            $limit = "$offset, $limit";
        }

        return $limit;
    }

    /**
     * @return CFHIRResource[]
     * @throws Exception
     */
    public function findProfiles(): array
    {
        // todo ref this function
        // todo [profile] a changer
        // find context fhir version
        if ($this->isFHIRResource()) {
            $fhir_version = $this->getResourceFHIRVersion();
        } else {
            $fhir_version = ($this->getFhirParentResource())->getResourceFHIRVersion();
        }

        //$resources = (new CFHIRMap())->getResources($this->getResourceType());

        return array_filter(
            [],
            function (CFHIRResource $resource) {
                return $resource instanceof $this;
            }
        );
    }

    /**
     * @return string
     */
    public function getInteractions(): array
    {
        return $this->getCapabilities()->getInteractions();
    }

    /**
     * @param CFHIRResource[] $resources
     *
     * @return string[]
     */
    public function getProfiles(array $resources): array
    {
        return array_map(
            function ($resource) {
                return $resource->getProfile();
            },
            $resources
        );
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function formatGender(?string $value): string
    {
        switch ($value) {
            case 'm':
                $gender = 'male';
                break;

            case 'f':
                $gender = 'female';
                break;

            default:
                $gender = 'unknown';
        }

        return $gender;
    }


    /**
     * @param string|CFHIRResource $resource_or_class
     * @param CStoredObject        $object
     *
     * @return CFHIRDataTypeReference
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function addReference($resource_or_class, ?CStoredObject $object = null): CFHIRDataTypeReference
    {
        if (is_string($resource_or_class)) {
            if (!is_subclass_of($resource_or_class, CFHIRResource::class)) {
                throw new CFHIRException('parameter given to resource_class should be an object of CFHIRResource');
            }

            if (!$object) {
                throw new CFHIRException(
                    'Add reference between resources must have an Object when resource class is given'
                );
            }

            $resource_or_class = new $resource_or_class();
        } else {
            if (!$resource_or_class instanceof CFHIRResource) {
                throw new CFHIRException('parameter given to resource_class should be an object of CFHIRResource');
            }
        }

        return CFHIRDataTypeReference::build(
            [
                'reference' => $this->getResourceIdentifier($resource_or_class, $object),
            ]
        );
    }

    /**
     * @param CFHIRResource $resource
     * @param CStoredObject $object
     *
     * @return string
     */
    protected function getResourceIdentifier(CFHIRResource $resource, ?CStoredObject $object): string
    {
        if (!$object) {
            $identifier = ($resource->id && !$resource->id->isNull())
                ? $resource->id->getValue()
                : CMbSecurity::generateUUID();
        } else {
            $identifier = $this->getInternalId($object);
        }

        return $resource::RESOURCE_TYPE . "/" . $identifier;
    }

    protected function getOxIdentifier(CStoredObject $object): string
    {
        return $object->getUuid();
    }

    /**
     * @param CStoredObject $object
     *
     * @return string
     */
    protected function getInternalId(CStoredObject $object): string
    {
        // If we send a resource (POST | PUT)
        // todo gestion dans le client de la sauvegarde de l'idex lors de la création + update
        if ($this->_receiver) {
            $idex = CIdSante400::getMatch($object->_class, $this->_receiver->_tag_fhir, null, $object->_id);
            if ($idex && $idex->_id) {
                return $idex->id400;
            }
        }

        return $this->getOxIdentifier($object);
    }

    /**
     * @return bool
     */
    public function isSummary(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isContained(): bool
    {
        return $this->is_contained;
    }

    /**
     * @param CMediusers $mediusers
     *
     * @return CFHIRDataTypeCoding[]
     * @throws Exception
     */
    public function setPractitionerSpecialty(CMediusers $mediusers): array
    {
        $current_user = CMediusers::get();
        $function_id  = null;
        $group_id     = null;

        if (CAppUI::isCabinet()) {
            $function_id = $current_user->function_id;
        } elseif (CAppUI::isGroup()) {
            $group_id = CGroups::loadCurrent()->_id;
        }

        $medecin = new CMedecin();
        $medecin = $medecin->loadFromRPPS($mediusers->rpps, $function_id, $group_id);

        if (!$medecin->_id || !$medecin->disciplines) {
            return [];
        }

        $exploded_code = explode(' : ', $medecin->disciplines);

        $system      = 'https://mos.esante.gouv.fr/NOS/TRE_R38-SpecialiteOrdinale/FHIR/TRE-R38-SpecialiteOrdinale';
        $code        = $exploded_code[0];
        $displayName = $exploded_code[1];

        return CFHIRDataTypeCoding::addCoding($system, $code, $displayName, []);
    }
}
