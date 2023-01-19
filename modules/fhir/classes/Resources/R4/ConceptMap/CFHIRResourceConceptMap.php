<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\ConceptMap;

use DOMDocument;
use DOMNode;
use Ox\Core\CMbArray;
use Ox\Interop\Fhir\Actors\CReceiverFHIR;
use Ox\Interop\Fhir\CFHIRXPath;
use Ox\Interop\Fhir\Contracts\Mapping\R4\ConceptMapMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceConceptMapInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeMarkdown;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\ConceptMap\CFHIRDataTypeConceptMapElement;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\ConceptMap\CFHIRDataTypeConceptMapGroup;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\ConceptMap\CFHIRDataTypeConceptMapTarget;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactDetail;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeUsageContext;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Phast\CAideSaisieConceptMapLiaison;
use Ox\Mediboard\CompteRendu\CAideSaisie;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * FIHR patient resource
 */
class CFHIRResourceConceptMap extends CFHIRDomainResource implements ResourceConceptMapInterface
{
    /** @var string */
    public const RESOURCE_TYPE = 'ConceptMap';

    public const URL_SNOMED      = "http://snomed.info/sct";
    public const URL_CIM10       = "https://www.atih.sante.fr/cim-10";
    public const TAG_CODE_SNOMED = "code_snomed";
    public const TAG_CODE_CIM10  = "code_cim10";

    protected ?CFHIRDataTypeBoolean $experimental = null;

    protected ?CFHIRDataTypeUri $url = null;

    protected ?CFHIRDataTypeString $version = null;

    protected ?CFHIRDataTypeString $name = null;

    protected ?CFHIRDataTypeString $title = null;

    protected ?CFHIRDataTypeCode $status = null;

    protected ?CFHIRDataTypeDateTime $date = null;

    protected ?CFHIRDataTypeString $publisher = null;

    protected ?CFHIRDataTypeMarkdown $description = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $jurisdiction = [];

    protected ?CFHIRDataTypeMarkdown $purpose = null;

    protected ?CFHIRDataTypeMarkdown $copyright = null;

    protected ?CFHIRDataType $source = null;

    protected ?CFHIRDataType $target = null;

    /** @var CFHIRDataTypeConceptMapGroup[] */
    protected array $group = [];

    /** @var CFHIRDataTypeUsageContext[] */
    protected array $useContext = [];

    /** @var CFHIRDataTypeContactDetail[] */
    protected array $contact = [];

    /** @var ConceptMapMappingInterface */
    protected $object_mapping;

    /*public function build(CStoredObject $object, CFHIREvent $event): void
    {
        parent::build($object, $event);

        if (!$object instanceof CAideSaisie) {
            throw  new CFHIRException("Object is not aide saisie");
        }

        // Cas d'un ajout d'un code dans le conceptMap, on met donc l'identifiant de la resource du serveur
        if ($object->_ref_concept_map->_id) {
            $this->setId(new CFHIRDataTypeString($object->_ref_concept_map->identifier_concept_map));
        }

        $group = $this->getGroupAideSaisie($object);

        $resource = new CFHIRResourceConceptMap();

        $url             = "http://" . CAppUI::conf("mb_oid") . "/" . CAppUI::conf("product_name") . "/"
            . $object->class . "-" . $object->field;
        $this->url       = new CFHIRDataTypeString($url);
        $this->status    = "draft";
        $this->name      = new CFHIRDataTypeString(CAppUI::tr("$object->class-$object->field"));
        $this->publisher = new CFHIRDataTypeString($group->_view);
        $this->purpose   = new CFHIRDataTypeString("__Objet__:" . $object->class . "|__Champ__:" . $object->field);

        $codes = [];
        if ($object->_ref_concept_map->_id) {
            $aide_saisie_liaison             = new CAideSaisieConceptMapLiaison();
            $where                           = [];
            $where["identifier_concept_map"] = " = '" . $object->_ref_concept_map->_id . "' ";
            $aide_saisie_liaisons            = $aide_saisie_liaison->loadList(
                ["identifier_concept_map" => " = '" . $object->_ref_concept_map->identifier_concept_map . "' "]
            );

            $elements_snomed = $elements_cim10 = [];
            // On ajoute les codes qui sont déjà dans le conceptMap
            foreach ($aide_saisie_liaisons as $_aide_saisie_liaison) {
                $aide_saisie = $_aide_saisie_liaison->loadRefAideSaisie();
                $display     = "__Libelle__:$aide_saisie->name|__Description__:$aide_saisie->text";

                // Récupération des codes que l'on a déjà récupérés
                $idex_cim10  = CIdSante400::getMatchFor($aide_saisie, self::TAG_CODE_CIM10);
                $idex_snomed = CIdSante400::getMatchFor($aide_saisie, self::TAG_CODE_SNOMED);

                $elements_snomed = $idex_snomed->_id
                    ? $this->addElementWithTarget($elements_snomed, $aide_saisie->_id, $display, $idex_snomed)
                    : $this->addElement($elements_snomed, $aide_saisie->_id, $display);

                $elements_cim10 = $idex_cim10->_id
                    ? $this->addElementWithTarget($elements_cim10, $aide_saisie->_id, $display, $idex_cim10)
                    : $this->addElement($elements_cim10, $aide_saisie->_id, $display);
            }

            $display         = "__Libelle__:$object->name|__Description__:$object->text";
            $elements_snomed = $this->addElement($elements_snomed, $object->_id, $display);
            $elements_cim10  = $this->addElement($elements_cim10, $object->_id, $display);

            // Ajout des elements dans le Group SNOMED
            $this->group[] = CFHIRDataTypeConceptMapGroup::build(
                [
                    "source"  => new CFHIRDataTypeString("$object->class-$object->field"),
                    "target"  => self::URL_SNOMED,
                    "element" => $elements_snomed,
                ]
            );

            // Ajout des elements dans le Group CIM10
            $this->group[] = CFHIRDataTypeConceptMapGroup::build(
                [
                    "source"  => new CFHIRDataTypeString("$object->class-$object->field"),
                    "target"  => self::URL_CIM10,
                    "element" => $elements_cim10,
                ]
            );
        } else {
            $display = "__Libelle__:$object->name|__Description__:$object->text";

            $codes[] = [
                "code"    => new CFHIRDataTypeCode($object->_id),
                "display" => new CFHIRDataTypeString($display),
            ];

            $this->group[] = CFHIRDataTypeConceptMapGroup::build(
                [
                    "source"  => new CFHIRDataTypeString("$object->class-$object->field"),
                    "target"  => self::URL_SNOMED,
                    "element" => CFHIRDataTypeConceptMapElement::build(
                        [
                            "code"    => new CFHIRDataTypeCode($object->_id),
                            "display" => new CFHIRDataTypeString($display),
                        ]
                    ),
                ]
            );

            $this->group[] = CFHIRDataTypeConceptMapGroup::build(
                [
                    "source"  => new CFHIRDataTypeString("$object->class-$object->field"),
                    "target"  => self::URL_CIM10,
                    "element" => CFHIRDataTypeConceptMapElement::build(
                        [
                            "code"    => new CFHIRDataTypeCode($object->_id),
                            "display" => new CFHIRDataTypeString($display),
                        ]
                    ),
                ]
            );
        }
    }*/

    /**
     * Add element
     *
     * @param array  $element
     * @param string $code
     * @param string $display
     *
     * @return array
     */
    public function addElement(array $element, string $code, string $display): array
    {
        $element[] = [
            "element" => CFHIRDataTypeConceptMapElement::build(
                [
                    "code"    => new CFHIRDataTypeCode($code),
                    "display" => new CFHIRDataTypeString($display),
                ]
            ),
        ];

        return $element;
    }

    /**
     * @param array       $element
     * @param string      $code
     * @param string      $display
     * @param CIdSante400 $idex
     *
     * @return array
     */
    public function addElementWithTarget(array $element, string $code, string $display, CIdSante400 $idex): array
    {
        $datas = explode("|", $idex->id400);

        $element[] = [
            "element" => CFHIRDataTypeConceptMapElement::build(
                [
                    "code"    => new CFHIRDataTypeCode($code),
                    "display" => new CFHIRDataTypeString($display),
                    "target"  => [
                        CFHIRDataTypeConceptMapTarget::build(
                            [
                                "code"        => new CFHIRDataTypeString(CMbArray::get($datas, 0)),
                                "display"     => new CFHIRDataTypeString(CMbArray::get($datas, 1)),
                                "equivalence" => new CFHIRDataTypeString(CMbArray::get($datas, 2)),
                            ]
                        ),
                    ],
                ]
            ),
        ];

        return $element;
    }

    /**
     * Get group
     *
     * @param CAideSaisie $aide_saisie aide saisie
     *
     * @return CGroups
     */
    public function getGroupAideSaisie(CAideSaisie $aide_saisie): CGroups
    {
        $group_id = null;
        // Récupération de l'établissement directement de l'aide à la saisie
        $group_id = $aide_saisie->group_id ? $aide_saisie->group_id : null;

        // Récupération de l'établissement à partir de la fonction de l'aide à la saisie
        if (!$group_id) {
            if ($aide_saisie->function_id) {
                $function = new CFunctions();
                $function->load($aide_saisie->function_id);
                $group_id = $function->loadRefGroup()->_id;
            }
        }

        // Récupération de l'établissement à partir de l'utilisateur de l'aide à la saisie
        if (!$group_id) {
            if ($aide_saisie->user_id) {
                $user = new CMediusers();
                $user->load($aide_saisie->user_id);

                $group_id = $user->loadRefFunction()->loadRefGroup()->_id;
            }
        }

        // Dans le pire des cas, on prend l'établissement courant
        if (!$group_id) {
            $group_id = CGroups::loadCurrent()->_id;
        }

        $group = new CGroups();
        $group->load($group_id);

        return $group;
    }

    /**
     * Mapping ConceptMap resource to create CAideSaisieConceptMapLiaison
     *
     * @param CFHIRXPath    $xpath             xpath
     * @param DOMNode       $node_doc_manifest node doc manifest
     * @param CFHIRResource $resource          resource
     *
     * @return CAideSaisieConceptMapLiaison
     * @throws CFHIRException
     */
    public static function mapping(
        DOMDocument $dom,
        CAideSaisie $aide_saisie,
        CReceiverFHIR $receiver_fhir
    ): CAideSaisieConceptMapLiaison {
        $xpath = new CFHIRXPath($dom);

        $liaison_aide_saisie                 = new CAideSaisieConceptMapLiaison();
        $liaison_aide_saisie->aide_saisie_id = $aide_saisie->_id;
        $liaison_aide_saisie->class          = $aide_saisie->class;
        $liaison_aide_saisie->field          = $aide_saisie->field;
        $liaison_aide_saisie->loadMatchingObject();

        if ($liaison_aide_saisie->_id) {
            throw new CFHIRException("Link with concept map always exist");
        }

        //$liaison_aide_saisie->url                    = $receiver_fhir->_source->_location_resource;
        $node_concept_map                            = $xpath->query("fhir:ConceptMap", $dom)->item(0);
        $liaison_aide_saisie->identifier_concept_map = $xpath->getAttributeValue("fhir:id", $node_concept_map);
        $liaison_aide_saisie->store();

        return $liaison_aide_saisie;
    }

    /**
     * MappingCodes
     *
     * @param string $response
     * @param int    $concept_map_id
     *
     * @throws \Exception
     */
    public static function mappingCodes(string $response, int $concept_map_id): void
    {
        $dom = new DOMDocument();
        $dom->loadXML($response);
        $xpath = new CFHIRXPath($dom);

        // On enlève tous les codes existants et on remplace par les nouveaux (comme ça gestion de la modification)
        self::deleteCodes($concept_map_id);

        $nodes_group = $xpath->query("fhir:ConceptMap/fhir:group", $dom);
        foreach ($nodes_group as $_node_group) {
            $terminologie = $xpath->getAttributeValue("fhir:target", $_node_group);

            if ($terminologie != self::URL_CIM10 && $terminologie != self::URL_SNOMED) {
                continue;
            }

            $nodes_element = $xpath->query("fhir:element", $_node_group);
            if (!$nodes_element) {
                continue;
            }

            foreach ($nodes_element as $_node_element) {
                $aide_saisie_id = $xpath->getAttributeValue("fhir:code", $_node_element);
                $aide_saisie    = new CAideSaisie();
                $aide_saisie->load($aide_saisie_id);
                if (!$aide_saisie->_id) {
                    continue;
                }

                $targets_node = $xpath->query("fhir:target", $_node_element);
                if (!$targets_node) {
                    continue;
                }

                foreach ($targets_node as $_target_node) {
                    $value = $xpath->getAttributeValue(
                            "fhir:code",
                            $_target_node
                        ) . "|" .
                        $xpath->getAttributeValue("fhir:display", $_target_node) . "|" .
                        $xpath->getAttributeValue("fhir:equivalence", $_target_node);
                    $idex  = CIdSante400::getMatch(
                        $aide_saisie->_class,
                        self::getTagIdex($terminologie),
                        $value,
                        $aide_saisie->_id
                    );
                    $idex->store();
                }
            }
        }
    }

    /**
     * Delete all codes CIM10 and SNOMED for an concept map
     *
     * @param $concept_map_id
     *
     * @throws \Exception
     */
    public static function deleteCodes(int $concept_map_id): void
    {
        $aide_saisie_liaison  = new CAideSaisieConceptMapLiaison();
        $aide_saisie_liaisons = $aide_saisie_liaison->loadList(
            ["identifier_concept_map" => " = '$concept_map_id' "]
        );

        foreach ($aide_saisie_liaisons as $_aide_saisie_liaison) {
            $aide_saisie = $_aide_saisie_liaison->loadRefAideSaisie();

            $idex                  = new CIdSante400();
            $where                 = [];
            $where["object_class"] = " = '$aide_saisie->_class' ";
            $where["object_id"]    = " = '$aide_saisie->_id' ";
            $where[]               = " tag = '" . self::TAG_CODE_SNOMED . "' OR tag = '" . self::TAG_CODE_CIM10 . "' ";
            foreach ($idex->loadList($where) as $_idex) {
                $_idex->purge();
            }
        }
    }

    /**
     * Get tag idex for terminologie name
     *
     * @param string $terminologie terminologie
     *
     * @return null|string
     */
    public static function getTagIdex(string $terminologie): ?string
    {
        switch ($terminologie) {
            case self::URL_SNOMED:
                return self::TAG_CODE_SNOMED;
            case self::URL_CIM10:
                return self::TAG_CODE_CIM10;
            default:
                return null;
        }
    }

    /**
     * Map property url
     */
    protected function mapUrl(): void
    {
        $this->url = $this->object_mapping->mapUrl();
    }

    /**
     * Map property version
     */
    protected function mapVersion(): void
    {
        $this->version = $this->object_mapping->mapVersion();
    }

    /**
     * Map property name
     */
    protected function mapName(): void
    {
        $this->name = $this->object_mapping->mapName();
    }

    /**
     * Map property title
     */
    protected function mapTitle(): void
    {
        $this->title = $this->object_mapping->mapTitle();
    }

    /**
     * Map property status
     */
    protected function mapStatus(): void
    {
        $this->status = $this->object_mapping->mapStatus();
    }

    /**
     * Map property experimental
     */
    protected function mapExperimental(): void
    {
        $this->experimental = $this->object_mapping->mapExperimental();
    }

    /**
     * Map property date
     */
    protected function mapDate(): void
    {
        $this->date = $this->object_mapping->mapDate();
    }

    /**
     * Map property Publisher
     */
    protected function mapPublisher(): void
    {
        $this->publisher = $this->object_mapping->mapPublisher();
    }

    /**
     * Map property Contact
     */
    protected function mapContact(): void
    {
        $this->contact = $this->object_mapping->mapContact();
    }

    /**
     * Map property Description
     */
    protected function mapDescription(): void
    {
        $this->description = $this->object_mapping->mapDescription();
    }

    /**
     * Map property UseContext
     */
    protected function mapUseContext(): void
    {
        $this->useContext = $this->object_mapping->mapUseContext();
    }

    /**
     * Map property Jurisdiction
     */
    protected function mapJurisdiction(): void
    {
        $this->jurisdiction = $this->object_mapping->mapJurisdiction();
    }

    /**
     * Map property Purpose
     */
    protected function mapPurpose(): void
    {
        $this->purpose = $this->object_mapping->mapPurpose();
    }

    /**
     * Map property CopyRight
     */
    protected function mapCopyright(): void
    {
        $this->copyright = $this->object_mapping->mapCopyright();
    }

    /**
     * Map property Source
     */
    protected function mapSource(): void
    {
        $this->source = $this->object_mapping->mapSource();
    }

    /**
     * Map property Target
     */
    protected function mapTarget(): void
    {
        $this->target = $this->object_mapping->mapTarget();
    }

    /**
     * Map property Group
     */
    protected function mapGroup(): void
    {
        $this->group = $this->object_mapping->mapGroup();
    }

    /**
     * @param CFHIRDataTypeUri|null $url
     *
     * @return CFHIRResourceConceptMap
     */
    public function setUrl(?CFHIRDataTypeUri $url): CFHIRResourceConceptMap
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
     * @param CFHIRDataTypeString|null $version
     *
     * @return CFHIRResourceConceptMap
     */
    public function setVersion(?CFHIRDataTypeString $version): CFHIRResourceConceptMap
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
     * @param CFHIRDataTypeString|null $name
     *
     * @return CFHIRResourceConceptMap
     */
    public function setName(?CFHIRDataTypeString $name): CFHIRResourceConceptMap
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
     * @param CFHIRDataTypeString|null $title
     *
     * @return CFHIRResourceConceptMap
     */
    public function setTitle(?CFHIRDataTypeString $title): CFHIRResourceConceptMap
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
     * @param CFHIRDataTypeCode|null $status
     *
     * @return CFHIRResourceConceptMap
     */
    public function setStatus(?CFHIRDataTypeCode $status): CFHIRResourceConceptMap
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
     * @return CFHIRResourceConceptMap
     */
    public function setDate(?CFHIRDataTypeDateTime $date): CFHIRResourceConceptMap
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
     * @return CFHIRResourceConceptMap
     */
    public function setPublisher(?CFHIRDataTypeString $publisher): CFHIRResourceConceptMap
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
     * @param CFHIRDataTypeMarkdown|null $description
     *
     * @return CFHIRResourceConceptMap
     */
    public function setDescription(?CFHIRDataTypeMarkdown $description): CFHIRResourceConceptMap
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
     * @param CFHIRDataTypeCodeableConcept ...$jurisdiction
     *
     * @return CFHIRResourceConceptMap
     */
    public function setJurisdiction(CFHIRDataTypeCodeableConcept ...$jurisdiction): CFHIRResourceConceptMap
    {
        $this->jurisdiction = $jurisdiction;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$jurisdiction
     *
     * @return CFHIRResourceConceptMap
     */
    public function addJurisdiction(CFHIRDataTypeCodeableConcept ...$jurisdiction): CFHIRResourceConceptMap
    {
        $this->jurisdiction = array_merge($this->jurisdiction, $jurisdiction);

        return $this;
    }

    /**
     * @return array
     */
    public function getJurisdiction(): array
    {
        return $this->jurisdiction;
    }

    /**
     * @param CFHIRDataTypeMarkdown|null $purpose
     *
     * @return CFHIRResourceConceptMap
     */
    public function setPurpose(?CFHIRDataTypeMarkdown $purpose): CFHIRResourceConceptMap
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
     * @param CFHIRDataTypeMarkdown|null $copyright
     *
     * @return CFHIRResourceConceptMap
     */
    public function setCopyright(?CFHIRDataTypeMarkdown $copyright): CFHIRResourceConceptMap
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
     * @param CFHIRDataType|null $source
     *
     * @return CFHIRResourceConceptMap
     */
    public function setSource(?CFHIRDataType $source): CFHIRResourceConceptMap
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return CFHIRDataType|null
     */
    public function getSource(): ?CFHIRDataType
    {
        return $this->source;
    }

    /**
     * @param CFHIRDataType|null $target
     *
     * @return CFHIRResourceConceptMap
     */
    public function setTarget(?CFHIRDataType $target): CFHIRResourceConceptMap
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return CFHIRDataType|null
     */
    public function getTarget(): ?CFHIRDataType
    {
        return $this->target;
    }

    /**
     * @param CFHIRDataTypeConceptMapGroup ...$group
     *
     * @return CFHIRResourceConceptMap
     */
    public function setGroup(CFHIRDataTypeConceptMapGroup ...$group): CFHIRResourceConceptMap
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @param CFHIRDataTypeConceptMapGroup ...$group
     *
     * @return CFHIRResourceConceptMap
     */
    public function addGroup(CFHIRDataTypeConceptMapGroup ...$group): CFHIRResourceConceptMap
    {
        $this->group = array_merge($this->group, $group);

        return $this;
    }

    /**
     * @return CFHIRDataTypeConceptMapGroup[]
     */
    public function getGroup(): array
    {
        return $this->group;
    }

    /**
     * @param CFHIRDataTypeUsageContext ...$useContext
     *
     * @return CFHIRResourceConceptMap
     */
    public function setUseContext(CFHIRDataTypeUsageContext ...$useContext): CFHIRResourceConceptMap
    {
        $this->useContext = $useContext;

        return $this;
    }

    /**
     * @param CFHIRDataTypeUsageContext ...$useContext
     *
     * @return CFHIRResourceConceptMap
     */
    public function addUseContext(CFHIRDataTypeUsageContext ...$useContext): CFHIRResourceConceptMap
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
     * @param CFHIRDataTypeContactDetail ...$contact
     *
     * @return CFHIRResourceConceptMap
     */
    public function setContact(CFHIRDataTypeContactDetail ...$contact): CFHIRResourceConceptMap
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @param CFHIRDataTypeContactDetail ...$contact
     *
     * @return CFHIRResourceConceptMap
     */
    public function addContact(CFHIRDataTypeContactDetail ...$contact): CFHIRResourceConceptMap
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

    /**
     * @param CFHIRDataTypeBoolean|null $experimental
     *
     * @return CFHIRResourceConceptMap
     */
    public function setExperimental(?CFHIRDataTypeBoolean $experimental): CFHIRResourceConceptMap
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
     * @param null $identifier
     *
     * @return CFHIRResourceConceptMap
     */
    public function setIdentifier(?CFHIRDataTypeIdentifier ...$identifier): self
    {
        $this->identifier = empty($identifier) ? null : reset($identifier);

        return $this;
    }

    /**
     * @return CFHIRDataTypeIdentifier|null
     */
    public function getIdentifier()
    {
        if (is_array($this->identifier)) {
            return empty($this->identifier) ? null : reset($this->identifier);
        } else {
            return $this->identifier;
        }
    }
}
