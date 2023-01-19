<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\CapabilityStatement;

use DOMDocument;
use DOMNode;
use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Interop\Fhir\Actors\CSenderFHIR;
use Ox\Interop\Fhir\CFHIRXPath;
use Ox\Interop\Fhir\Contracts\Mapping\R4\CapabilityStatementMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceCapabilityStatementInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCanonical;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDate;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeMarkdown;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CapabilityStatement\CFHIRDataTypeCapabilityStatementInteraction;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CapabilityStatement\CFHIRDataTypeCapabilityStatementResource;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CapabilityStatement\CFHIRDataTypeCapabilityStatementRest;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CapabilityStatement\CFHIRDataTypeCapabilityStatementSoftware;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactDetail;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeUsageContext;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCapabilities;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Utilities\CCapabilities;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;

/**
 * FIHR CapabilityStatement resource
 */
class CFHIRResourceCapabilityStatement extends CFHIRDomainResource implements ResourceCapabilityStatementInterface
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = 'CapabilityStatement';

    /** @var string */
    public const VERSION_NORMATIVE = '4.0';

    // attributes
    protected ?CFHIRDataTypeUri     $url          = null;
    protected ?CFHIRDataTypeString  $name         = null;
    protected ?CFHIRDataTypeString  $title        = null;
    protected ?CFHIRDataTypeBoolean $experimental = null;

    /**
     * @var CFHIRDataTypeContactDetail[]
     */
    protected array                  $contact     = [];
    protected ?CFHIRDataTypeMarkdown $description = null;
    /**
     * @var CFHIRDataTypeCodeableConcept[]
     */
    protected array $jurisdiction = [];
    /**
     * @var CFHIRDataTypeUsageContext[]
     */
    protected array                  $useContext = [];
    protected ?CFHIRDataTypeMarkdown $purpose    = null;
    protected ?CFHIRDataTypeMarkdown $copyright  = null;
    /**
     * @var CFHIRDataTypeCanonical[]
     */
    protected array $imports = [];
    /**
     * @var CFHIRDataTypeCanonical[]
     */
    protected array $instantiates = [];

    protected ?CFHIRDataTypeString $version = null;

    protected ?CFHIRDataTypeCode $status = null;

    protected ?CFHIRDataTypeDateTime $date = null;

    protected ?CFHIRDataTypeString $publisher = null;

    protected ?CFHIRDataTypeCode $kind = null;

    protected ?CFHIRDataTypeCapabilityStatementSoftware $software = null;

    protected ?CFHIRDataTypeCode $fhirVersion = null;

    /**
     * @var CFHIRDataTypeCode[]
     */
    protected array $patchFormat = [];

    /**
     * @var CFHIRDataTypeCanonical[]
     */
    protected array $implementationGuide = [];

    protected ?CFHIRDataTypeCode $acceptUnknown = null;

    /** @var CFHIRDataTypeCode[] */
    protected array $format = [];

    /** @var CFHIRDataTypeCapabilityStatementRest[] */
    protected array $rest = [];

    protected ?CFHIRDataTypeString $base_fhir_version = null;

    /** @var CapabilityStatementMappingInterface */
    protected $object_mapping;

    /**
     * @return CCapabilitiesResource
     */
    public function generateCapabilities(): CCapabilitiesResource
    {
        return parent::generateCapabilities()
            ->addInteractions([CFHIRInteractionCapabilities::NAME]);
    }

    /**
     * @param string $data
     *
     * @return CCapabilities|null
     * @throws Exception
     */
    public function deserialize(string $data): ?CCapabilities
    {
        $dom = new DOMDocument();
        $dom->loadXML($data);
        $dom->formatOutput = true;

        $xpath = new CFHIRXPath($dom);

        $resources      = [];
        $resource_nodes = $xpath->query('/fhir:' . $this::RESOURCE_TYPE . "/fhir:rest/fhir:resource");
        /** @var DOMNode $node */
        foreach ($resource_nodes as $node) {
            $resource_type = $xpath->queryAttributNode('fhir:type', $node, 'value');
            $profile       = $xpath->queryAttributNode('fhir:profile', $node, 'value');

            $interaction_nodes = $xpath->query('fhir:interaction', $node);
            $interactions      = [];
            foreach ($interaction_nodes as $interaction_node) {
                $interactions[] = $xpath->queryAttributNode('fhir:code', $interaction_node, 'value');
            }

            $supported_profiles       = [];
            $supported_profiles_nodes = $xpath->query('fhir:supportedProfile', $node);
            /** @var DOMNode $profiles_node */
            foreach ($supported_profiles_nodes as $profiles_node) {
                if ($value = $profiles_node->attributes->getNamedItem('value')->nodeValue) {
                    $supported_profiles[] = $value;
                }
            }

            $resources[] = [
                'type'              => $resource_type,
                'profile'           => $profile,
                'supportedProfiles' => $supported_profiles,
                'interactions'      => $interactions,
                'updateCreate'      => false // allow client to define its own id on update request (PUT)
            ];
        }

        return (new CCapabilities())
            ->addResources($resources);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function interactionCapabilities(): void
    {
        if (!$this->_sender) {
            throw new CFHIRException('Impossible to find sender');
        }

        $this->status = new CFHIRDataTypeCode("active");

        $this->date = new CFHIRDataTypeDate(CApp::getVersion()->getReleaseDate());

        $this->publisher = new CFHIRDataTypeString("Not provided");

        $this->kind = new CFHIRDataTypeCode("instance");

        $this->software = CFHIRDataTypeCapabilityStatementSoftware::build(
            [
                "name"        => new CFHIRDataTypeString(CAppUI::conf("product_name")),
                "version"     => new CFHIRDataTypeString((string)CApp::getVersion()),
                "releaseDate" => new CFHIRDataTypeDate(CApp::getVersion()->getReleaseDate()),
            ]
        );

        $this->fhirVersion = new CFHIRDataTypeCode($this->base_fhir_version);

        $this->acceptUnknown = new CFHIRDataTypeCode("no");

        $this->format = [
            new CFHIRDataTypeCode("application/fhir+xml"),
            new CFHIRDataTypeCode("application/fhir+json"),
        ];

        $enc_sender_fhir = new CSenderFHIR($this->_sender);

        $available_resources = $enc_sender_fhir->getAvailableResources();

        $resources = [];
        foreach ($available_resources as $resource) {
            if ($resource::RESOURCE_TYPE === self::RESOURCE_TYPE) {
                continue;
            }

            $interactions = $this->_sender ?
                $enc_sender_fhir->getAvailableInteractions($resource) : $resource->getInteractions();

            $interactions = array_map(
                function ($interaction) {
                    return CFHIRDataTypeCapabilityStatementInteraction::build(
                        ["code" => new CFHIRDataTypeCode($interaction)]
                    );
                },
                $interactions
            );

            $profile = new CFHIRDataTypeCanonical($resource->getProfile());

            /** @var CFHIRResource[] $extension_classes */

            if ($this->_sender) {
                $supportedProfile = $enc_sender_fhir->getAvailableProfiles($resource);
            } else {
                $supportedProfile = [];
                foreach ($resource->findProfiles() as $extension_resource) {
                    if ($extension_resource::PROFILE_TYPE) {
                        $supportedProfile[] = new CFHIRDataTypeCanonical($extension_resource::PROFILE_TYPE);
                    }
                }
            }

            $resources[] = CFHIRDataTypeCapabilityStatementResource::build(
                [
                    'type'             => new CFHIRDataTypeString($resource::RESOURCE_TYPE),
                    'profile'          => $profile,
                    'supportedProfile' => $supportedProfile,
                    'interaction'      => $interactions,
                ]
            );
        }

        $this->rest[] = CFHIRDataTypeCapabilityStatementRest::build(
            [
                "mode"     => new CFHIRDataTypeCode("server"),
                "resource" => $resources,
            ]
        );
    }

    /**
     * @param CFHIRDataTypeString|null $base_fhir_version
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setBaseFHIRVersion(?CFHIRDataTypeString $base_fhir_version): self
    {
        $this->base_fhir_version = $base_fhir_version;

        return $this;
    }

    /**
     * @param CFHIRDataTypeString|null $version
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setVersion(?CFHIRDataTypeString $version): CFHIRResourceCapabilityStatement
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getVersion(): ?CFHIRDataTypeString
    {
        return $this->version;
    }

    /**
     * @param CFHIRDataTypeCode|null $status
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setStatus(?CFHIRDataTypeCode $status): CFHIRResourceCapabilityStatement
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getStatus(): ?CFHIRDataTypeCode
    {
        return $this->status;
    }

    /**
     * @param CFHIRDataTypeDateTime|null $date
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setDate(?CFHIRDataTypeDateTime $date): CFHIRResourceCapabilityStatement
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return CFHIRDataTypeDateTime|null
     */
    public function getDate(): ?CFHIRDataTypeDateTime
    {
        return $this->date;
    }

    /**
     * @param CFHIRDataTypeString|null $publisher
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setPublisher(?CFHIRDataTypeString $publisher): CFHIRResourceCapabilityStatement
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getPublisher(): ?CFHIRDataTypeString
    {
        return $this->publisher;
    }

    /**
     * @param CFHIRDataTypeCode|null $kind
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setKind(?CFHIRDataTypeCode $kind): CFHIRResourceCapabilityStatement
    {
        $this->kind = $kind;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getKind(): ?CFHIRDataTypeCode
    {
        return $this->kind;
    }

    /**
     * @param CFHIRDataTypeCapabilityStatementSoftware|null $software
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setSoftware(?CFHIRDataTypeCapabilityStatementSoftware $software): CFHIRResourceCapabilityStatement
    {
        $this->software = $software;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCapabilityStatementSoftware|null
     */
    public function getSoftware(): ?CFHIRDataTypeCapabilityStatementSoftware
    {
        return $this->software;
    }

    /**
     * @param CFHIRDataTypeCode|null $fhirVersion
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setFhirVersion(?CFHIRDataTypeCode $fhirVersion): CFHIRResourceCapabilityStatement
    {
        $this->fhirVersion = $fhirVersion;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getFhirVersion(): ?CFHIRDataTypeCode
    {
        return $this->fhirVersion;
    }

    /**
     * @param CFHIRDataTypeCode|null $acceptUnknown
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setAcceptUnknown(?CFHIRDataTypeCode $acceptUnknown): CFHIRResourceCapabilityStatement
    {
        $this->acceptUnknown = $acceptUnknown;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getAcceptUnknown(): ?CFHIRDataTypeCode
    {
        return $this->acceptUnknown;
    }

    /**
     * @param CFHIRDataTypeCode ...$format
     *
     * @return self
     */
    public function setFormat(CFHIRDataTypeCode ...$format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCode ...$format
     *
     * @return self
     */
    public function addFormat(CFHIRDataTypeCode ...$format): self
    {
        $this->format = array_merge($this->format, $format);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode[]
     */
    public function getFormat(): array
    {
        return $this->format;
    }

    /**
     * @param CFHIRDataTypeCapabilityStatementRest ...$rest
     *
     * @return self
     */
    public function setRest(CFHIRDataTypeCapabilityStatementRest ...$rest): self
    {
        $this->rest = $rest;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCapabilityStatementRest ...$rest
     *
     * @return self
     */
    public function addRest(CFHIRDataTypeCapabilityStatementRest ...$rest): self
    {
        $this->rest = array_merge($this->rest, $rest);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCapabilityStatementRest[]
     */
    public function getRest(): array
    {
        return $this->rest;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getBaseFhirVersion(): ?CFHIRDataTypeString
    {
        return $this->base_fhir_version;
    }

    /**
     * @param CFHIRDataTypeCanonical ...$implementationGuide
     *
     * @return self
     */
    public function setImplementationGuide(CFHIRDataTypeCanonical ...$implementationGuide): self
    {
        $this->implementationGuide = $implementationGuide;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCanonical ...$implementationGuide
     *
     * @return self
     */
    public function addImplementationGuide(CFHIRDataTypeCanonical ...$implementationGuide): self
    {
        $this->implementationGuide = array_merge($this->implementationGuide, $implementationGuide);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCanonical[]
     */
    public function getImplementationGuide(): array
    {
        return $this->implementationGuide;
    }

    /**
     * @param CFHIRDataTypeMarkdown|null $copyright
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setCopyright(?CFHIRDataTypeMarkdown $copyright): CFHIRResourceCapabilityStatement
    {
        $this->copyright = $copyright;

        return $this;
    }

    /**
     * @return CFHIRDataTypeMarkdown|null
     */
    public function getCopyright(): ?CFHIRDataTypeMarkdown
    {
        return $this->copyright;
    }

    /**
     * @param CFHIRDataTypeMarkdown|null $purpose
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setPurpose(?CFHIRDataTypeMarkdown $purpose): CFHIRResourceCapabilityStatement
    {
        $this->purpose = $purpose;

        return $this;
    }

    /**
     * @return CFHIRDataTypeMarkdown|null
     */
    public function getPurpose(): ?CFHIRDataTypeMarkdown
    {
        return $this->purpose;
    }

    /**
     * @param CFHIRDataTypeMarkdown|null $description
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setDescription(?CFHIRDataTypeMarkdown $description): CFHIRResourceCapabilityStatement
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return CFHIRDataTypeMarkdown|null
     */
    public function getDescription(): ?CFHIRDataTypeMarkdown
    {
        return $this->description;
    }

    /**
     * @param CFHIRDataTypeBoolean|null $experimental
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setExperimental(?CFHIRDataTypeBoolean $experimental): CFHIRResourceCapabilityStatement
    {
        $this->experimental = $experimental;

        return $this;
    }

    /**
     * @return CFHIRDataTypeBoolean|null
     */
    public function getExperimental(): ?CFHIRDataTypeBoolean
    {
        return $this->experimental;
    }

    /**
     * @param CFHIRDataTypeString|null $title
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setTitle(?CFHIRDataTypeString $title): CFHIRResourceCapabilityStatement
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getTitle(): ?CFHIRDataTypeString
    {
        return $this->title;
    }

    /**
     * @param CFHIRDataTypeString|null $name
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setName(?CFHIRDataTypeString $name): CFHIRResourceCapabilityStatement
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getName(): ?CFHIRDataTypeString
    {
        return $this->name;
    }

    /**
     * @param CFHIRDataTypeUri|null $url
     *
     * @return CFHIRResourceCapabilityStatement
     */
    public function setUrl(?CFHIRDataTypeUri $url): CFHIRResourceCapabilityStatement
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return CFHIRDataTypeUri|null
     */
    public function getUrl(): ?CFHIRDataTypeUri
    {
        return $this->url;
    }

    /**
     * * Map property url
     */
    protected function mapUrl(): void
    {
        $this->url = $this->object_mapping->mapUrl();
    }

    /**
     * * Map property version
     */
    protected function mapVersion(): void
    {
        $this->version = $this->object_mapping->mapVersion();
    }

    /**
     * * Map property name
     */
    protected function mapName(): void
    {
        $this->name = $this->object_mapping->mapName();
    }

    /**
     * * Map property title
     */
    protected function mapTitle(): void
    {
        $this->title = $this->object_mapping->mapTitle();
    }

    /**
     * * Map property status
     */
    protected function mapStatus(): void
    {
        $this->status = $this->object_mapping->mapStatus();
    }

    /**
     * * Map property experimental
     */
    protected function mapExperimental(): void
    {
        $this->experimental = $this->object_mapping->mapExperimental();
    }

    /**
     * * Map property date
     */
    protected function mapDate(): void
    {
        $this->date = $this->object_mapping->mapDate();
    }

    /**
     * * Map property publisher
     */
    protected function mapPublisher(): void
    {
        $this->publisher = $this->object_mapping->mapPublisher();
    }

    /**
     * * Map property contact
     */
    protected function mapContact(): void
    {
        $this->contact = $this->object_mapping->mapContact();
    }

    /**
     * * Map property description
     */
    protected function mapDescription(): void
    {
        $this->description = $this->object_mapping->mapDescription();
    }

    /**
     * * Map property useContext
     */
    protected function mapUseContext(): void
    {
        $this->useContext = $this->object_mapping->mapUseContext();
    }

    /**
     * * Map property jurisdiction
     */
    protected function mapJurisdiction(): void
    {
        $this->jurisdiction = $this->object_mapping->mapJurisdiction();
    }

    /**
     * * Map property purpose
     */
    protected function mapPurpose(): void
    {
        $this->purpose = $this->object_mapping->mapPurpose();
    }


    /**
     * * Map property copyright
     */
    protected function mapCopyright(): void
    {
        $this->copyright = $this->object_mapping->mapCopyright();
    }

    /**
     * * Map property kind
     */
    protected function mapKind(): void
    {
        $this->kind = $this->object_mapping->mapKind();
    }

    /**
     * * Map property instantiates
     */
    protected function mapInstantiates(): void
    {
        $this->instantiates = $this->object_mapping->mapInstantiates();
    }

    /**
     * * Map property imports
     */
    protected function mapImports(): void
    {
        $this->imports = $this->object_mapping->mapImports();
    }

    /**
     * * Map property software
     */
    protected function mapSoftware(): void
    {
        $this->software = $this->object_mapping->mapSoftware();
    }

    /**
     * * Map property fhirVersion
     */
    protected function mapFhirVersion(): void
    {
        $this->fhirVersion = $this->object_mapping->mapFhirVersion();
    }

    /**
     * * Map property format
     */
    protected function mapFormat(): void
    {
        $this->format = $this->object_mapping->mapFormat();
    }

    /**
     * * Map property patchFormat
     */
    protected function mapPatchFormat(): void
    {
        $this->patchFormat = $this->object_mapping->mapPatchFormat();
    }

    /**
     * * Map property implementationGuide
     */
    protected function mapImplementationGuide(): void
    {
        $this->implementationGuide = $this->object_mapping->mapImplementationGuide();
    }

    /**
     * * Map property rest
     */
    protected function mapRest(): void
    {
        $this->rest = $this->object_mapping->mapRest();
    }

    /**
     * @param CFHIRDataTypeCode ...$patchFormat
     *
     * @return self
     */
    public function setPatchFormat(CFHIRDataTypeCode ...$patchFormat): self
    {
        $this->patchFormat = $patchFormat;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCode ...$patchFormat
     *
     * @return self
     */
    public function addPatchFormat(CFHIRDataTypeCode ...$patchFormat): self
    {
        $this->patchFormat = array_merge($this->patchFormat, $patchFormat);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode[]
     */
    public function getPatchFormat(): array
    {
        return $this->patchFormat;
    }

    /**
     * @param CFHIRDataTypeCanonical ...$instantiates
     *
     * @return self
     */
    public function setInstantiates(CFHIRDataTypeCanonical ...$instantiates): self
    {
        $this->instantiates = $instantiates;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCanonical ...$instantiates
     *
     * @return self
     */
    public function addInstantiates(CFHIRDataTypeCanonical ...$instantiates): self
    {
        $this->instantiates = array_merge($this->instantiates, $instantiates);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCanonical[]
     */
    public function getInstantiates(): array
    {
        return $this->instantiates;
    }

    /**
     * @param CFHIRDataTypeCanonical ...$imports
     *
     * @return self
     */
    public function setImports(CFHIRDataTypeCanonical ...$imports): self
    {
        $this->imports = $imports;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCanonical ...$imports
     *
     * @return self
     */
    public function addImports(CFHIRDataTypeCanonical ...$imports): self
    {
        $this->imports = array_merge($this->imports, $imports);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCanonical[]
     */
    public function getImports(): array
    {
        return $this->imports;
    }

    /**
     * @param CFHIRDataTypeUsageContext ...$useContext
     *
     * @return self
     */
    public function setUseContext(CFHIRDataTypeUsageContext ...$useContext): self
    {
        $this->useContext = $useContext;

        return $this;
    }

    /**
     * @param CFHIRDataTypeUsageContext ...$useContext
     *
     * @return self
     */
    public function addUseContext(CFHIRDataTypeUsageContext ...$useContext): self
    {
        $this->useContext = array_merge($this->useContext, $useContext);

        return $this;
    }

    /**
     * @return CFHIRDataTypeUsageContext[]
     */
    public function getUseContext(): array
    {
        return $this->useContext;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$jurisdiction
     *
     * @return self
     */
    public function setJurisdiction(CFHIRDataTypeCodeableConcept ...$jurisdiction): self
    {
        $this->jurisdiction = $jurisdiction;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$jurisdiction
     *
     * @return self
     */
    public function addJurisdiction(CFHIRDataTypeCodeableConcept ...$jurisdiction): self
    {
        $this->jurisdiction = array_merge($this->jurisdiction, $jurisdiction);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getJurisdiction(): array
    {
        return $this->jurisdiction;
    }

    /**
     * @param CFHIRDataTypeContactDetail ...$contact
     *
     * @return self
     */
    public function setContact(CFHIRDataTypeContactDetail ...$contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @param CFHIRDataTypeContactDetail ...$contact
     *
     * @return self
     */
    public function addContact(CFHIRDataTypeContactDetail ...$contact): self
    {
        $this->contact = array_merge($this->contact, $contact);

        return $this;
    }

    /**
     * @return CFHIRDataTypeContactDetail[]
     */
    public function getContact(): array
    {
        return $this->contact;
    }
}
