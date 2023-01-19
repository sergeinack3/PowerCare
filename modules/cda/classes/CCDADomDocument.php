<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbXMLDocument;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\InteropResources\CInteropResources;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;

/**
 * Permet de cré
 */
class CCDADomDocument extends CMbXMLDocument
{
    use CCDAXPathTrait;

    /** @var int */
    public const LEVEL_1 = 1;
    public const LEVEL_2 = 2;
    public const LEVEL_3 = 3;

    /** @var string */
    public $msg_xml;

    /** @var CExchangeCDA */
    public $_ref_exchange_cda;
    /** @var CInteropSender */
    public $_ref_sender;
    /** @var CCDAReport */
    protected $report;

    /** @var string ID du document */
    protected $id;
    /** @var DOMNode Type de document */
    protected $code;
    /** @var string Type de document avec le code LOINC + OID */
    protected $type_doc;
    /** @var string Title du document */
    protected $title;
    /** @var string ID du document remplacé */
    protected $relatedDocumentId;
    /** @var string Datetime of document */
    protected $effectiveTime;
    /** @var DOMNode */
    protected $confidentialityCode;
    /** @var string */
    protected $setId;
    /** @var DOMNode */
    protected $versionNumber;
    /** @var DOMNode Patient concerné par le document */
    protected $recordTarget;
    /** @var DOMNode Rôle patient */
    protected $patientRole;
    /** @var DOMNodeList PS, patient ou dispositif auteur(s) du document incluant l'organisation émettrice pour le
     * compte de laquelle le PS ou le dispositif a constitué le document */
    protected $author;
    /** @var DOMNode Organisation conservant le document et garantissant son cycle de vie */
    protected $custodian;
    /** @var DOMNodeList PS attestant la validité du document */
    protected $authenticator;
    /** @var DOMNode PS ou patient responsable du document */
    protected $legalAuthenticator;
    /** @var DOMNodeList PS participant, ayant joué dans l'élaboration du document, un rôle différent de celui d'auteur,
     * de responsable, d'opérateur de saisie, d'informateur ou de destinataire */
    protected $participant;
    /** @var DOMNode Prescription */
    protected $inFulfillmentOf;
    /** @var DOMNodeList Acte(s) rapporté(s) par le document. Pour l'acte principal, inclusion du PS exécutant,
     * de l'organisation pour laquelle il a exécuté l'acte et de son cadre d'exercice. Pour une expression personnelle
     * du patient, inclusion du patient et de sa démarche.
     */
    protected $documentationOf;
    /** @var DOMNode Document à remplacer */
    protected $relatedDocument;
    /** @var DOMNode Prise en charge renseignée par le document incluant le type de sortie, le PS responsable et
     * les PS impliqués dans la prise en charge ainsi que leur organisation et le lieu de prise en charge
     */
    protected $componentOf;
    /** @var DOMNode Composant */
    protected $component;
    /** @var int CDA level (1|2|3) */
    protected $level;
    /** @var DOMNode Structured body */
    protected $structuredBody;
    /** @var DOMNodeList */
    protected $structuredBodyComponents;
    /** @var DOMNode */
    protected $nonXMLBody;

    /**
     * @inheritdoc
     */
    function __construct($encoding = "UTF-8")
    {
        parent::__construct($encoding);

        $this->preserveWhiteSpace = true;
        $this->formatOutput       = false;
        $this->schemapath         = "modules/cda/resources";
        $this->schemafilename     = "$this->schemapath/CDA.xsd";

        $this->dom = $this;
    }

    /**
     * @inheritdoc
     */
    function schemaValidate($filename = null, $returnErrors = false, $display_errors = true)
    {
        // Pas de validation car le module des ressources n'est pas installé
        $file = $filename ?: $this->schemafilename;

        // Pas de validation car les schémas ne sont pas présents
        if (!CInteropResources::fileExists($file)) {
            trigger_error("Schemas are missing. Please add files in '$file' directory", E_USER_NOTICE);

            return true;
        }

        return parent::schemaValidate($file, $returnErrors, $display_errors);
    }

    /**
     * Ajoute du text en premier position
     *
     * @param DOMNode $nodeParent      DOMNode
     * @param String  $value           String
     * @param bool    $use_content_xml String
     *
     * @return void
     */
    function insertTextFirst($nodeParent, $value, $use_content_xml = false)
    {
        $value     = utf8_encode($value ?? '');
        $firstNode = $nodeParent->firstChild;

        if ($use_content_xml && $value !== "") {
            $fragment = $this->createDocumentFragment();
            $fragment->appendXML($value);
            $nodeParent->insertBefore($fragment, $firstNode);

            return;
        }

        $nodeParent->insertBefore($this->createTextNode($value), $firstNode);
    }

    /**
     * Know if document match with code in code system
     *
     * @param string $code_system
     * @param string $code
     *
     * @return bool
     */
    public function isDocumentCodeMatch(string $system, string $code): bool
    {
        if (!$this->code) {
            return false;
        }

        $document_system   = $this->getValueAttributNode($this->code, 'codeSystem');
        $document_code = $this->getValueAttributNode($this->code, 'code');

        return preg_match("~^(?:urn:(?:uuid|oid):)?$system$~", $document_system ?? '') && $document_code === $code;
    }

    /**
     * Get code of document
     *
     * @return string|null
     */
    public function getDocumentCode(): ?string
    {
        if (!$this->code) {
            return false;
        }

        return $this->getValueAttributNode($this->code, 'code');
    }

    /**
     * Get code system of document
     *
     * @return string|null
     */
    public function getDocumentSystemCode(): ?string
    {
        if (!$this->code) {
            return false;
        }

        return $this->getValueAttributNode($this->code, 'codeSystem');
    }

    /**
     * Caste l'élement spécifié
     *
     * @param DOMNode $nodeParent DOMNode
     * @param String  $value      String
     *
     * @return void
     */
    function castElement($nodeParent, $value)
    {
        $value                = utf8_encode($value);
        $attribute            = $this->createAttributeNS("http://www.w3.org/2001/XMLSchema-instance", "xsi:type");
        $attribute->nodeValue = $value;
        $nodeParent->appendChild($attribute);
    }

    /**
     * @inheritdoc
     */
    function purgeEmptyElementsNode($node, $removeParent = true)
    {
        // childNodes undefined for non-element nodes (eg text nodes)
        if ($node->childNodes) {
            // Copy childNodes array
            $childNodes = [];
            foreach ($node->childNodes as $childNode) {
                $childNodes[] = $childNode;
            }

            // Browse with the copy (recursive call)
            foreach ($childNodes as $childNode) {
                $this->purgeEmptyElementsNode($childNode);
            }
        }
        // Remove if empty
        if (!$node->hasChildNodes() && !$node->hasAttributes() && $node->nodeValue === "") {
            $node->parentNode->removeChild($node);
        }
    }

    /**
     * Handle document CDA
     *
     * @return string|null
     * @throws CCDAException
     */
    public function handle(): ?string
    {
        return null;
    }

    public function getInteropSender(): CInteropSender
    {
        return $this->_ref_sender;
    }

    /**
     * @param CInteropSender $ref_sender
     */
    public function setInteropSender(CInteropSender $ref_sender): void
    {
        $this->_ref_sender = $ref_sender;
    }

    /**
     * Get content Nodes
     */
    public function getContentNodes(): void
    {
        // Récupération des métadonnées du CDA
        $this->getHeaders();

        // Récupération des composants
        $this->getComponents();
    }

    public function getRelatedDocument(): ?DOMNode
    {
        return $this->relatedDocument;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(): void
    {
        switch ($this->getComponent()->firstChild->localName) {
            case 'structuredBody':
                $this->level = self::LEVEL_3;
                break;

            case 'nonXMLBody':
                $this->level = self::LEVEL_1;
                break;

            default:
                break;
        }
    }

    public function getComponent(): ?DOMNode
    {
        return $this->component;
    }

    public function getStructuredBody(): DOMNode
    {
        return $this->structuredBody;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(): void
    {
        $this->title = $this->queryTextNode('title');
    }

    /**
     * @return string
     */
    public function getEffectiveTime(): string
    {
        return $this->effectiveTime;
    }

    public function setEffectiveTime(): void
    {
        $datetime            = $this->getValueAttributNode($this->queryNode('effectiveTime'), 'value');
        $this->effectiveTime = CMbDT::dateTime($datetime);
    }

    public function getStructuredBodyComponents(): DOMNodeList
    {
        return $this->structuredBodyComponents;
    }

    public function getNonXMLBody(): DOMNode
    {
        return $this->nonXMLBody;
    }

    public function getPatientRole(): DOMNode
    {
        return $this->patientRole;
    }

    public function getTypeDoc(): ?string
    {
        return $this->type_doc;
    }

    public function getSender(): CInteropSender
    {
        return $this->_ref_sender;
    }

    public function getAuthor(): ?DOMNodeList
    {
        return $this->author;
    }

    public function getCustodian(): DOMNode
    {
        return $this->custodian;
    }

    public function getAuthentificator(): ?DOMNodeList
    {
        return $this->authenticator;
    }

    public function getLegalAuthentificator(): DOMNode
    {
        return $this->legalAuthenticator;
    }

    /**
     * @return DOMNode
     */
    public function getVersionNumber(): DOMNode
    {
        return $this->versionNumber;
    }

    /**
     * @return DOMNode
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return DOMNode
     */
    public function getSetId(): ?string
    {
        return $this->setId;
    }

    /**
     * @return DOMNode
     */
    public function getRelatedDocumentId(): ?string
    {
        return $this->relatedDocumentId;
    }

    public function getParticipant(): ?DOMNodeList
    {
        return $this->participant;
    }

    public function getInFulfillmentOf(): ?DOMNode
    {
        return $this->inFulfillmentOf;
    }

    public function getDocumentationOf(): ?DOMNodeList
    {
        return $this->documentationOf;
    }

    public function getComponentOf(): DOMNode
    {
        return $this->componentOf;
    }

    /**
     * CDA Report
     *
     * @return void
     */
    public function makeReport(): void
    {
        $this->report->makeReport();

        $this->_ref_exchange_cda->report = $this->report->toJson();
        $this->_ref_exchange_cda->store();
    }

    /**
     * CDA Report
     *
     * @return CCDAReport
     */
    public function getReport(): CCDAReport
    {
        if (!$this->report) {
            $this->report = new CCDAReport('Report');
        }

        return $this->report;
    }

    /**
     * Convert node or nodes list to json
     *
     * @param DOMNode|DOMNodeList $nodes
     *
     * @return string|null
     */
    public function nodeToJson($nodes): ?string
    {
        $dom = new DOMDocument();
        if ($nodes instanceof DOMNodeList) {
            $dom->appendChild(($dom->createElement('root')));

            foreach ($nodes as $n) {
                $dom->documentElement->appendChild($dom->importNode($n, true));
            }
        } elseif ($nodes instanceof DOMNode) {
            $dom->appendChild($dom->importNode($nodes, true));
        }

        return json_encode(simplexml_load_string($dom->saveXML()));
    }

    /**
     * Search the patient from the data contained in the document
     *
     * @return CPatient
     * @throws Exception
     *
     */
    public function getPatientFromDocument(): CPatient
    {
        $node = $this->getPatientRole();

        $prenom_naissance = $this->queryTextNode('patient/name/given[@qualifier="BR"]', $node);
        $prenom_utilise   = $this->queryTextNode('patient/name/given[@qualifier="CL"]', $node);
        $nom_naissance    = $this->queryTextNode('patient/cda:name/family[@qualifier="BR"]', $node);
        $nom_utilise      = $this->queryTextNode('patient/cda:name/family[@qualifier="CL"]', $node);
        $dob_node         = $this->queryNode('patient/birthTime', $node);
        $date_naissance   = null;
        if ($dob_node) {
            $date_naissance = $dob_node->getAttribute('value');
            if ($date_naissance && strlen($date_naissance) >= 8) {
                $date_naissance = substr($date_naissance, 0, 4) . '-' . substr($date_naissance, 4, 2) . '-' . substr(
                        $date_naissance,
                        6,
                        2
                    );
            }
        }

        $gender      = null;
        $gender_node = $this->queryNode('patient/administrativeGenderCode', $node);
        if ($gender_node) {
            $gender = utf8_decode(strtolower($gender_node->getAttribute('code')));
        }

        $mobile_phone = null;
        $mphone_node  = $this->queryNode('telecom[@use="MC"]', $node);
        if ($mphone_node) {
            $mobile_phone = utf8_decode(str_replace('tel:', '', $mphone_node->getAttribute('value')));
        }

        $patient                  = new CPatient();
        $patient->nom             = $nom_utilise;
        $patient->nom_jeune_fille = $nom_naissance;
        $patient->prenom          = $prenom_naissance;
        $patient->prenom_usuel    = $prenom_utilise;
        $patient->naissance       = $date_naissance;
        $patient->sexe            = $gender;
        $patient->tel2            = $mobile_phone;

        $INS_NIR_TEST  = $this->getIdentifier($node, CPatientINSNIR::OID_INS_NIR_TEST);
        $INS_NIR       = $this->getIdentifier($node, CPatientINSNIR::OID_INS_NIR);
        $INS_NIA       = $this->getIdentifier($node, CPatientINSNIR::OID_INS_NIA);
        $patient->_ins = ($INS_NIR ?: $INS_NIA) ?: $INS_NIR_TEST;
        if ($INS_NIR) {
            $patient->_oid = CPatientINSNIR::OID_INS_NIR;
        } elseif ($INS_NIA) {
            $patient->_oid = CPatientINSNIR::OID_INS_NIA;
        } elseif ($INS_NIR_TEST) {
            $patient->_oid = CPatientINSNIR::OID_INS_NIR_TEST;
        }

        return $patient;
    }

    protected function getRecordTarget(): DOMNode
    {
        return $this->recordTarget;
    }

    protected function getCode(): string
    {
        return $this->code;
    }

    private function getHeaders(): void
    {
        $this->setId();
        $this->setCode();
        $this->setTitle();
        $this->setEffectiveTime();
        $this->setConfidentialityCode();
        $this->setSetId();
        $this->setVersionNumber();
        $this->setRecordTarget();
        $this->setPatientRole();
        $this->setAuthor();
        $this->setCustodian();
        $this->setAuthentificator();
        $this->setLegalAuthentificator();
        $this->setParticipant();
        $this->setInFulfillmentOf();
        $this->setDocumentationOf();
        $this->setRelatedDocument();
        $this->setRelatedDocumentId();
        $this->setComponentOf();
    }

    private function setId(): void
    {
        $this->id = $this->constructId('id');
    }

    private function constructId(string $nodeName, DOMNode $contextNode = null): ?string
    {
        if (!$node = $this->queryNode($nodeName, $contextNode)) {
            return null;
        }

        return $this->getValueAttributNode($node, 'root') . $this->getValueAttributNode($node, 'extension');
    }

    private function setCode(): void
    {
        $this->code = $this->queryNode("code");

        $this->type_doc = $this->getValueAttributNode($this->code, 'codeSystem') .
            '^' . $this->getValueAttributNode($this->code, 'code');
    }

    private function setConfidentialityCode(): void
    {
        $this->confidentialityCode = $this->queryNode('confidentialityCode');
    }

    private function setSetId(): void
    {
        $this->setId = $this->constructId('setId');
    }

    private function setVersionNumber(): void
    {
        $this->versionNumber = $this->queryNode('versionNumber');
    }

    private function setRecordTarget(): void
    {
        $this->recordTarget = $this->queryNode("recordTarget");
    }

    private function setPatientRole(): void
    {
        $this->patientRole = $this->queryNode("patientRole", $this->getRecordTarget());
    }

    private function setAuthor(): void
    {
        $this->author = $this->queryNodes("author");
    }

    private function setCustodian(): void
    {
        $this->custodian = $this->queryNode("custodian");
    }

    private function setAuthentificator(): void
    {
        $this->authenticator = $this->queryNodes("authenticator");
    }

    private function setLegalAuthentificator(): void
    {
        $this->legalAuthenticator = $this->queryNode("legalAuthenticator");
    }

    private function setParticipant(): void
    {
        $this->participant = $this->queryNodes("participant");
    }

    private function setInFulfillmentOf(): void
    {
        $this->participant = $this->queryNodes("inFulfillmentOf");
    }

    private function setDocumentationOf(): void
    {
        $this->documentationOf = $this->queryNodes("documentationOf");
    }

    private function setRelatedDocument(): void
    {
        $this->relatedDocument = $this->queryNode("relatedDocument");
    }

    private function setRelatedDocumentId(): void
    {
        if (!$relatedDocument = $this->getRelatedDocument()) {
            return;
        }

        $this->relatedDocumentId = $this->constructId(
            'id',
            $this->queryNode('parentDocument', $relatedDocument)
        );
    }

    private function setComponentOf(): void
    {
        $this->componentOf = $this->queryNode("componentOf");
    }

    private function getComponents(): void
    {
        $this->setComponent();
        $this->setLevel();

        if ($this->getLevel() === self::LEVEL_1) {
            $this->setNonXMLBody();
        } elseif ($this->getLevel() === self::LEVEL_3) {
            $this->setStructuredBody();
            $this->setStructuredBodyComponents();
        }
    }

    private function setComponent(): void
    {
        $this->component = $this->queryNode("component");
    }

    private function setNonXMLBody(): void
    {
        $this->nonXMLBody = $this->queryNode("nonXMLBody", $this->getComponent());
    }

    private function setStructuredBody(): void
    {
        $this->structuredBody = $this->queryNode("structuredBody", $this->getComponent());
    }

    private function setStructuredBodyComponents(): void
    {
        $this->structuredBodyComponents = $this->queryNodes("component", $this->getStructuredBody());
    }
}
