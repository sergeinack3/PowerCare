<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Handle;

use DOMNode;
use DOMNodeList;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Interop\Cda\CCDADomDocument;
use Ox\Interop\Cda\CCDAReport;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Parser\CCDAParserFactory;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Eai\CItemReport;
use Ox\Interop\Eai\Repository\PatientRepository;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;
use Symfony\Component\EventDispatcher\EventDispatcher;

class CCDAHandle
{
    /** @var string */
    public const EVENT_BEFORE_STORE_FILE = 'before_store_file';
    /** @var string */
    public const EVENT_AFTER_STORE_FILE = 'after_store_file';

    /** @var CPatient */
    protected $patient;
    /** @var CCodable */
    protected $target_object;
    /** @var CCDADomDocument */
    protected $cda_dom_document;
    /** @var CCDAMeta */
    protected $meta;
    /** @var CCDAReport */
    protected $report;
    /** @var EventDispatcher */
    protected $dispatcher;

    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();
    }

    /**
     * Handle document CDA
     *
     * @param CCDAParserFactory $parser
     *
     * @throws CCDAException
     */
    final public function handle(CCDADomDocument $document): void
    {
        $this->cda_dom_document = $document;

        // Handle patient
        $this->handlePatient();

        // Handle metadata
        $this->handleMetadata();

        // Handle components
        if ($this->patient && $this->patient->_id) {
            $this->handleComponents();
        }
    }

    /**
     * Dispatch object for an event
     *
     * @param object $object
     * @param string $event_name
     *
     * @return void
     */
    public function dispatch(object $object, string $event_name): void
    {
        $this->dispatcher->dispatch($object, $event_name);
    }

    /**
     * @param string   $event_name
     * @param callable $listener
     *
     * @return void
     */
    public function attachEvent(string $event_name, callable $listener): void
    {
        $this->dispatcher->addListener($event_name, $listener);
    }

    /**
     * Handle patient
     *
     * @return void
     * @throws CCDAException
     */
    protected function handlePatient(): void
    {
        $patientRole = $this->cda_dom_document->getPatientRole();
        $domain      = CDomain::getMasterDomainPatient();

        $INS_NIR_TEST = $this->getIdentifier($patientRole, CPatientINSNIR::OID_INS_NIR_TEST);
        $INS_NIR      = $this->getIdentifier($patientRole, CPatientINSNIR::OID_INS_NIR);
        $INS_NIA      = $this->getIdentifier($patientRole, CPatientINSNIR::OID_INS_NIA);
        $IPP          = ($domain && $domain->OID) ? $this->getIdentifier($patientRole, $domain->OID) : null;

        $tag_patient = $this->getCDADomDocument()->getSender()->_tag_patient;

        $strategy = $this->getCDADomDocument()->getSender()->_ref_config_cda->search_patient_strategy;
        $patient = (new PatientRepository($strategy))
            ->withINS($INS_NIR, $INS_NIA, $INS_NIR_TEST)
            ->withIPP($IPP, $tag_patient)
            //->withPatientSearched($patient) // todo faire le mapping patient
            ->find();

        // todo if handle_patient ==> create patient

        // Patient non retrouvé
        if (!$patient || !$patient->_id) {
            $this->report->addData(
                CCDAException::patientIdentifierNotFound()->getMessage(),
                CItemReport::SEVERITY_ERROR
            );

            return;
        }

        $this->setPatient($patient);
    }

    /**
     * Get patient identifier
     *
     * @param DOMNode $node patientRole
     * @param string  $oid  OID
     *
     * @return string|null
     */
    public function getIdentifier(DOMNode $node, string $OID): ?string
    {
        return $this->cda_dom_document->queryAttributeNodeValue(
            "id[@root='$OID']",
            $node,
            "extension"
        );
    }

    /**
     * @return CCDADomDocument
     */
    public function getCDADomDocument(): CCDADomDocument
    {
        return $this->cda_dom_document;
    }

    /**
     * Handle metadata
     *
     * @return void
     */
    protected function handleMetadata(): void
    {
        // Handle Meta
        $this->handleMeta();

        // Dans le cas où le patient n'est pas retrouvé par ex.
        if (!$this->getMeta()->_id) {
            return;
        }

        // Handle documentationOf : associations du document à des actes
        $this->handleDocumentationOf();

        // Handle participants : participant|author|authenticator|legalAuthenticator|custodian
        $this->handleParticipants();

        // Handle order : association du document à une prescription
        $this->handleOrder();

        // Handle type of care : association du document à une prise en charge
        $this->handleTypeOfCare();
    }

    /**
     * Handle meta : metadata common to all CDA documents
     *
     * @return void
     */
    protected function handleMeta(): void
    {
        $this->meta = new CCDAMeta();
        $this->meta->handle($this);
    }

    /**
     * Handle documentationOf : associations du document à des actes
     *
     * @return void
     */
    protected function handleDocumentationOf(): void
    {
        $cda_meta_documentation_of = new CCDAMetaDocumentationOf();
        $cda_meta_documentation_of->handle($this);
    }

    /**
     * Handle participants : participant|author|authenticator|legalAuthenticator|custodian
     *
     * @return void
     */
    protected function handleParticipants(): void
    {
        $this->handleAuthor();
        $this->handleAuthenticator();
        $this->handleLegalAuthenticator();
        $this->handleCustodian();
        $this->handleParticipant();
    }

    /**
     * Handle participants : author
     *
     * @return void
     */
    protected function handleAuthor(): void
    {
        $cda_meta_participant = new CCDAMetaParticipant();
        $cda_meta_participant->handle(
            $this,
            CCDAMetaParticipant::TYPE_AUTHOR,
            $this->getCDADomDocument()->getAuthor()
        );
    }

    /**
     * Handle participants : authenticator
     *
     * @return void
     */
    protected function handleAuthenticator(): void
    {
        $cda_meta_participant = new CCDAMetaParticipant();
        $cda_meta_participant->handle(
            $this,
            CCDAMetaParticipant::TYPE_AUTHENTICATOR,
            $this->getCDADomDocument()->getAuthentificator()
        );
    }

    /**
     * Handle participants : legalAuthenticator
     *
     * @return void
     */
    protected function handleLegalAuthenticator(): void
    {
        $cda_meta_participant = new CCDAMetaParticipant();
        $cda_meta_participant->handle(
            $this,
            CCDAMetaParticipant::TYPE_LEGAL_AUTHENTICATOR,
            $this->getCDADomDocument()->getLegalAuthentificator()
        );
    }

    /**
     * Handle participants : custodian
     *
     * @return void
     */
    protected function handleCustodian(): void
    {
        $cda_meta_participant = new CCDAMetaParticipant();
        $cda_meta_participant->handle(
            $this,
            CCDAMetaParticipant::TYPE_CUSTODIAN,
            $this->getCDADomDocument()->getCustodian()
        );
    }

    /**
     * Handle participants : participant
     *
     * @return void
     */
    protected function handleParticipant(): void
    {
        $cda_meta_participant = new CCDAMetaParticipant();
        $cda_meta_participant->handle(
            $this,
            CCDAMetaParticipant::TYPE_PARTICIPANT,
            $this->getCDADomDocument()->getParticipant()
        );
    }

    /**
     * Handle order : association du document à une prescription
     *
     * @return void
     */
    protected function handleOrder(): void
    {
        $cda_meta_order = new CCDAMetaOrder();
        $cda_meta_order->handle(
            $this
        );
    }

    /**
     * Handle type of care : association du document à une prise en charge
     *
     * @return void
     */
    protected function handleTypeOfCare(): void
    {
        $cda_meta_type_of_care = new CCDAMetaTypeOfCare();
        $cda_meta_type_of_care->handle(
            $this
        );
    }

    /**
     * Handle components
     *
     * @return void
     * @throws CCDAException
     */
    protected function handleComponents(): void
    {
    }

    /**
     * @return void
     */
    public function setPatient(CPatient $patient): void
    {
        $this->patient = $patient;
    }

    /**
     * @return CPatient
     */
    public function getPatient(): ?CPatient
    {
        return $this->patient;
    }


    public function getAttributNode(string $nodeName, string $attName, DOMNode $contextNode = null): ?string
    {
        return $this->getCDADomDocument()->getValueAttributNode($this->getNode($nodeName, $contextNode), $attName);
    }

    public function getNode(string $nodeName, DOMNode $contextNode = null): ?DOMNode
    {
        return $this->getCDADomDocument()->queryNode($nodeName, $contextNode);
    }

    public function getValueAttributNode(string $nodeName, DOMNode $contextNode = null): ?string
    {
        return $this->getCDADomDocument()->getValueAttributNode($this->getNode($nodeName, $contextNode), 'value');
    }

    public function getCodeAttributNode(string $nodeName, DOMNode $contextNode = null): ?string
    {
        return $this->getCDADomDocument()->getValueAttributNode($this->getNode($nodeName, $contextNode), 'code');
    }

    public function getCodeSystemAttributNode(string $nodeName, DOMNode $contextNode = null): ?string
    {
        return $this->getCDADomDocument()->getValueAttributNode($this->getNode($nodeName, $contextNode), 'codeSystem');
    }

    public function getRootAttributNode(string $nodeName, DOMNode $contextNode = null): ?string
    {
        return $this->getCDADomDocument()->getValueAttributNode($this->getNode($nodeName, $contextNode), 'root');
    }

    public function getExtensionAttributNode(string $nodeName, DOMNode $contextNode = null): ?string
    {
        return $this->getCDADomDocument()->getValueAttributNode($this->getNode($nodeName, $contextNode), 'extension');
    }

    public function getLowAttributNode(DOMNode $contextNode = null): ?string
    {
        return $this->getCDADomDocument()->getValueAttributNode($this->getNode('low', $contextNode), 'value');
    }

    public function getHighAttributNode(DOMNode $contextNode = null): ?string
    {
        return $this->getCDADomDocument()->getValueAttributNode($this->getNode('high', $contextNode), 'value');
    }

    /**
     * Return dateTime
     *
     * @return string|null
     */
    public function getDateTime(string $dateTimeCDA): ?string
    {
        if (!$dateTimeCDA) {
            return null;
        }

        return CMbDT::dateTime($dateTimeCDA);
    }

    public function getMeta(): CCDAMeta
    {
        return $this->meta;
    }

    /**
     * Store nodes to JSON
     *
     * @param CStoredObject|CCDAMetaDocumentationOf|CCDAMetaParticipant|CCDAMetaOrder|CCDAMetaTypeOfCare $cda_meta_object
     * @param DOMNodeList|DOMNode|null                                                                   $nodes
     *
     * @return void
     */
    public function handleMetaUnStructuredData(CStoredObject $cda_meta_object, $nodes = null): void
    {
        $cda_document = $this->getCDADomDocument();
        $data         = $cda_document->nodeToJson($nodes);
        if (!$data) {
            return;
        }

        // Hash des datas pour voir si on l'a déjà enregistré
        $cda_meta_object->data_hash = md5($data);
        $cda_meta_object->loadMatchingObjectEsc();

        $cda_meta_object->data = $data;
    }

    /**
     * @return CCDAReport
     */
    public function getReport(): CCDAReport
    {
        return $this->report;
    }

    /**
     * @param CCDAReport $report
     */
    public function setReport(CCDAReport $report): void
    {
        $this->report = $report;
    }

    /**
     * Get target object
     *
     * @param CStoredObject $object
     *
     * @return CStoredObject|null
     */
    public function getTargetObject(): ?CStoredObject
    {
        return $this->target_object;
    }

    /**
     * Set target object
     *
     * @param CStoredObject $object
     *
     * @return void
     */
    public function setTargetObject(CStoredObject $object): void
    {
        $this->target_object = $object;
    }
}
