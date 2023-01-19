<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Transformer\Parser;

use DOMNode;
use Exception;
use Ox\Core\CMbDT;
use Ox\Interop\Xds\CXDSTools;
use Ox\Interop\Xds\CXDSXmlDocument;
use Ox\Interop\Xds\CXDSXPath;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSClass;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSConfidentiality;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSDocumentEntry;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSDocumentEntryAuthor;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSEventCodeList;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSFormat;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSHealthcareFacilityType;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSPracticeSetting;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSType;
use Ox\Interop\Xds\Structure\XDSElementInterface;

class EbXMLToDocumentEntry implements XDSParserInterface
{
    /** @var CXDSXPath */
    private $xpath;

    /** @var CXDSDocumentEntry */
    private $document_entry;

    /**
     * EbXMLToDocumentEntry constructor.
     */
    public function __construct()
    {
        $this->document_entry = new CXDSDocumentEntry();
    }

    /**
     * @param string $content
     *
     * @return XDSElementInterface|null
     * @throws Exception
     */
    public function parse(string $content): ?XDSElementInterface
    {
        $dom = new CXDSXmlDocument();
        $dom->loadXML($content);

        return $this->parseNode($dom);
    }

    /**
     * @param DOMNode $node
     *
     * @return CXDSDocumentEntry|null
     * @throws Exception
     */
    public function parseNode(DOMNode $node): ?XDSElementInterface
    {
        $this->xpath = $xpath = new CXDSXPath($node->ownerDocument);

        if ($node->localName === "ExtrinsicObject") {
            $document_entry_node = $node;
        } else {
            $document_entry_nodes = $xpath->query("//rim:ExtrinsicObject", $node);
            if ($document_entry_nodes->length < 1) {
                return null;
            }
            $document_entry_node = $document_entry_nodes->item(0);
        }

        $this->map($document_entry_node);

        return $this->document_entry;
    }

    /**
     * @param DOMNode $document_entry_node
     *
     * @throws Exception
     */
    private function map(DOMNode $document_entry_node)
    {
        $this->mapMetadata($document_entry_node);

        $this->mapPatientId($document_entry_node);

        $this->mapSourceId($document_entry_node);

        $this->mapSourcePatientInfo($document_entry_node);

        $this->mapLegalAuthenticator($document_entry_node);

        $this->mapComponents($document_entry_node);

        $author_classification = 'urn:uuid:93606bcf-9494-43ec-9b4e-a7748d1a838d';
        $authors               = $this->xpath->query(
            "rim:Classification[@classificationScheme='$author_classification']",
            $document_entry_node
        );
        foreach ($authors as $author) {
            $this->mapAuthor($author);
        }
    }

    /**
     * @param DOMNode $entry_node
     *
     * @throws Exception
     */
    private function mapMetadata(DOMNode $entry_node)
    {
        $doc_entry = $this->document_entry;
        // entryUUID
        $doc_entry->entryUUID = $this->xpath->queryAttributNode('.', $entry_node, 'id');

        // logical_id
        $doc_entry->logical_id = $this->xpath->queryAttributNode('.', $entry_node, 'lid');

        // status
        $doc_entry->availabilityStatus = $this->xpath->queryAttributNode('.', $entry_node, 'status');

        // mimeType
        $doc_entry->mimeType = $this->xpath->queryAttributNode('.', $entry_node, 'mimeType');

        // hash
        $doc_entry->hash = $this->xpath->getSlot($entry_node, 'hash');

        // size
        $doc_entry->size = $this->xpath->getSlot($entry_node, 'size');

        // creation_datetime
        $doc_entry->creation_datetime = $this->xpath->getSlot($entry_node, 'creationTime');

        // language_code
        $doc_entry->language_code = $this->xpath->getSlot($entry_node, 'languageCode');

        // repository_unique_id
        $doc_entry->repository_unique_id = $this->xpath->getSlot($entry_node, 'repositoryUniqueId');

        // service_start_time
        if ($service_start_time = $this->xpath->getSlot($entry_node, 'serviceStartTime')) {
            $doc_entry->service_start_time = CMbDT::dateTime($service_start_time);
        }

        // service_stop_time
        if ($service_stop_time = $this->xpath->getSlot($entry_node, 'serviceStopTime')) {
            $doc_entry->service_stop_time = CMbDT::dateTime($service_stop_time);
        }

        // URI
        $doc_entry->URI = $this->xpath->getSlot($entry_node, 'URI');

        // document availability
        $doc_entry->documentAvailability = $this->xpath->getSlot($entry_node, 'documentAvailability');

        // comments
        $doc_entry->comments = $this->xpath->getDescription($entry_node);

        // title
        $doc_entry->title = $this->xpath->getName($entry_node);

        // version
        $doc_entry->version = $this->xpath->getVersionInfo($entry_node);

        // unique_id
        $doc_entry->unique_id = $this->xpath->getExternalIdentifier(
            $entry_node,
            'urn:uuid:2e82c1f6-a085-4c72-9da3-8640a32e42ab'
        );
    }

    /**
     * @param DOMNode $entry_node
     *
     * @throws Exception
     */
    protected function mapComponents(DOMNode $entry_node)
    {
        // class
        if ($class = $this->xpath->getClassificationEntries($entry_node, 'urn:uuid:41a5887f-8865-4c09-adf7-e362475b143a')) {
            $this->document_entry->_xds_class = CXDSClass::fromValues($class);
        }

        // format
        if ($format = $this->xpath->getClassificationEntries($entry_node, 'urn:uuid:a09d5840-386c-46f2-b5ad-9c3699a4309d')) {
            $this->document_entry->_format = CXDSFormat::fromValues($format);
        }

        // confidentiality
        $confidentiality_schema = 'urn:uuid:f4f85eac-e6cb-4883-b524-f2705394840f';
        $confidentialities = $this->xpath->query("rim:Classification[@classificationScheme='$confidentiality_schema']", $entry_node);
        foreach ($confidentialities as $confidentiality) {
            if ($conf = $this->xpath->getClassificationEntries($confidentiality, null)) {
                $this->document_entry->addConfidentiality(CXDSConfidentiality::fromValues($conf));
            }
        }

        // event_code_list
        $event_code_schema = 'urn:uuid:2c6b8cb7-8b2a-4051-b291-b1ae6a575ef4';
        $event_codes = $this->xpath->query("rim:Classification[@classificationScheme='$event_code_schema']", $entry_node);
        foreach ($event_codes as $event_code) {
            if ($event = $this->xpath->getClassificationEntries($event_code, null)) {
                $this->document_entry->addEventCodeList(CXDSEventCodeList::fromValues($event));
            }
        }

        // healthcare_facility_type
        if ($healthcare = $this->xpath->getClassificationEntries($entry_node, 'urn:uuid:f33fb8ac-18af-42cc-ae0e-ed0b0bdb91e1')) {
            $this->document_entry->_healthcare_facility_type = CXDSHealthcareFacilityType::fromValues($healthcare);
        }

        // practice_setting
        if ($practice_setting = $this->xpath->getClassificationEntries($entry_node, 'urn:uuid:cccf5598-8b07-4b77-a05e-ae952c785ead')) {
            $this->document_entry->_practice_setting = CXDSPracticeSetting::fromValues($practice_setting);
        }

        // type
        if ($type = $this->xpath->getClassificationEntries($entry_node, 'urn:uuid:f0306f51-975f-434e-a61c-c59651d33983')) {
            $this->document_entry->_type = CXDSType::fromValues($type);
        }
    }

    /**
     * @param DOMNode $entry_node
     */
    protected function mapPatientId(DOMNode $entry_node)
    {
        $patient_id = $this->xpath->getExternalIdentifier($entry_node, 'urn:uuid:58a6f841-87b3-4a3e-92fd-a8ffeff98427');

        if ($patient_id) {
            $this->document_entry->patient_id = CXDSTools::parseHL7v2Components($patient_id);
        }
    }

    /**
     * @param DOMNode $entry_node
     */
    protected function mapSourceId(DOMNode $entry_node)
    {
        $source_id = $this->xpath->getSlot($entry_node, 'sourcePatientId');

        if ($source_id) {
            $this->document_entry->source_patient_id = CXDSTools::parseHL7v2Components($source_id);
        }
    }

    /**
     * @param DOMNode $entry_node
     */
    protected function mapLegalAuthenticator(DOMNode $entry_node)
    {
        $legal_authenticator = $this->xpath->getSlot($entry_node, 'legalAuthenticator');

        if ($legal_authenticator) {
            $this->document_entry->setLegalAuthenticator(CXDSTools::parseHL7v2Components($legal_authenticator));
        }
    }

    /**
     * @param DOMNode $entry_node
     */
    protected function mapSourcePatientInfo(DOMNode $entry_node)
    {
        if (!$source_patient_info = $this->xpath->getSlot($entry_node, 'sourcePatientInfo', true)) {
            return;
        }
        $components = [];
        foreach ($source_patient_info as $value) {
            $parts = explode('|', $value);
            $PID = $parts[0];
            $content = $parts[1];

            if (isset($components[$PID])) {
                if (!is_array($components[$PID])) {
                    $components[$PID] = [$components[$PID]];
                }

                $components[$PID][] = CXDSTools::parseHL7v2Components($content);
            } else {
                $components[$PID][] = CXDSTools::parseHL7v2Components($content);
            }
        }

        $this->document_entry->setSourcePatientInfo($components);
    }

    /**
     * @param DOMNode $author_node
     */
    protected function mapAuthor(DOMNode $author_node)
    {
        $author = new CXDSDocumentEntryAuthor();

        // author_institution
        if ($author_institution = $this->xpath->getSlot($author_node, 'authorInstitution')) {
            $author->setAuthorInstitution(CXDSTools::parseHL7v2Components($author_institution));
        }

        // author_person
        if ($author_person = $this->xpath->getSlot($author_node, 'authorPerson')) {
            $author->setAuthorPerson(CXDSTools::parseHL7v2Components($author_person));
        }

        // author_role
        if ($author_role = $this->xpath->getSlot($author_node, 'authorRole')) {
            $author->setAuthorRole($author_role);
        }

        // author_speciality
        if ($author_specialty = $this->xpath->getSlot($author_node, 'authorSpecialty')) {
            $author->setAuthorSpeciality(CXDSTools::parseHL7v2Components($author_specialty));
        }

        if ($author->author_speciality || $author->author_person || $author->author_role || $author->author_institution) {
            $this->document_entry->addDocumentEntryAuthor($author);
        }
    }
}
