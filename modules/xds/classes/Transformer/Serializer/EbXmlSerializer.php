<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Transformer\Serializer;

use DOMElement;
use DOMNode;
use Ox\Core\CMbArray;
use Ox\Core\CMbSecurity;
use Ox\Interop\Xds\CXDSTools;
use Ox\Interop\Xds\CXDSXmlDocument;
use Ox\Interop\Xds\Exception\CXDSException;
use Ox\Interop\Xds\Structure\CXDSAssociation;
use Ox\Interop\Xds\Structure\CXDSAuthor;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSDocumentEntry;
use Ox\Interop\Xds\Structure\SubmissionSet\CXDSSubmissionSet;
use Ox\Interop\Xds\Structure\SubmissionSet\CXDSSubmissionSetAuthor;
use Ox\Interop\Xds\Structure\XDSElementInterface;

class EbXmlSerializer implements XDSSerializerInterface
{
    /** @var CXDSXmlDocument */
    private $document;

    /** @var CXDSSubmissionSet */
    private $current_submission_set;

    /** @var DOMNode */
    private $root;

    /** @var string */
    private $content_serialized;

    /**
     * EbXmlSerializer constructor.
     */
    public function __construct()
    {
        $this->document = new CXDSXmlDocument();
    }

    /**
     * @param DOMNode $root
     */
    public function setRoot(DOMNode $root): void
    {
        $this->root = $root;
    }

    /**
     * @param XDSElementInterface $xds_element
     *
     * @return string
     * @throws CXDSException
     */
    public function serialize(XDSElementInterface $xds_element): string
    {
        if ($this->content_serialized) {
            return $this->content_serialized;
        }

        $request_node = $this->document->createSubmitObjectsRequestRoot($this->root);
        $list_node    = $this->document->createRegistryObjectListRoot($request_node);

        switch (true) {
            case $xds_element instanceof CXDSSubmissionSet:
                $this->mapSubmissionSet($xds_element, $list_node);
                break;

            case $xds_element instanceof CXDSDocumentEntry:
                $this->mapDocumentEntry($xds_element, $list_node);
                break;

            default:
                throw new CXDSException("Element not supported for serialization");
        }

        return $this->content_serialized = $this->document->saveXML();
    }

    /**
     * @throws CXDSException
     */
    protected function mapSubmissionSet(CXDSSubmissionSet $submission_set, DOMNode $parent): void
    {
        $this->current_submission_set = $submission_set;
        $registry_node                = $this->document->createRimRoot('RegistryPackage', null, $parent);

        // entryUUID - id
        $submission_set_id = $submission_set->entryUUID;
        $this->document->addAttribute($registry_node, 'id', $submission_set_id);

        // availabilityStatus - status
        if ($status = $submission_set->availabilityStatus) {
            $this->document->addAttribute($registry_node, 'status', $status);
        }

        // submissionTime - slot
        if ($submission_time = $submission_set->submissionTime) {
            $submission_time = CXDSTools::formatDatetime($submission_time);
            $this->document->createSlot('submissionTime', $submission_time, $registry_node);
        }

        // title - name
        if ($name = $submission_set->title) {
            $this->document->createName($name, $registry_node);
        }

        // comments - Description
        if ($description = $submission_set->comments) {
            $this->document->createDescription($description, $registry_node);
        }

        $content_type = $this->current_submission_set->_submission_set_content_type;

        // contentType - classification
        $classification_node = $this->document->createClassification(
            $registry_node,
            $submission_set_id,
            'urn:uuid:aa543740-bdda-424e-8c96-df4873be8500',
            $content_type->code
        );
        $this->document->createSlot('codingScheme', $content_type->code_system, $classification_node);
        $this->document->createName($content_type->display_name, $classification_node);

        // Définie que c'est un submissionset - Classification interne
        $classification_node = $this->document->createClassification(
            $registry_node,
            $submission_set_id,
        );
        $this->document->addAttribute(
            $classification_node,
            'classificationNode',
            'urn:uuid:a54d6aa5-d40d-43f9-88c5-b4633d873bdd'
        );

        // authors - classification
        foreach ($submission_set->_submission_set_author as $author) {
            $this->mapAuthor($author, $submission_set->entryUUID, $registry_node);
        }

        // patient_id - ExternalIdentifier
        $external_identifier = $this->document->createExternalIdentifier(
            $registry_node,
            $submission_set_id,
            $submission_set->getPatientId(),
            'urn:uuid:6b5aea1a-874d-4603-a4bc-96a0a7b38446'
        );
        $this->document->createName('XDSSubmissionSet.patientId', $external_identifier);

        // source_id - ExternalIdentifier
        $external_identifier = $this->document->createExternalIdentifier(
            $registry_node,
            $submission_set_id,
            $submission_set->source_id,
            'urn:uuid:554ac39e-e3fe-47fe-b233-965d2a147832',
        );
        $this->document->createName('XDSSubmissionSet.sourceId', $external_identifier);

        // uniqueId - ExternalIdentifier
        $external_identifier = $this->document->createExternalIdentifier(
            $registry_node,
            $submission_set_id,
            $submission_set->unique_id,
            'urn:uuid:96fdda7c-d067-4183-912e-bf5ee74998a8',
        );
        $this->document->createName('XDSSubmissionSet.uniqueId', $external_identifier);

        // documents
        foreach ($submission_set->documents as $document) {
            $this->mapDocumentEntry($document, $parent);
        }

        // associations
        foreach ($submission_set->associations as $association) {
            $this->mapAssociation($association, $parent);
        }
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     * @param DOMNode           $parent
     */
    protected function mapDocumentEntry(CXDSDocumentEntry $document_entry, DOMNode $parent): void
    {
        $extrinsic_object_node = $this->document->createRimRoot('ExtrinsicObject', null, $parent);
        // templated id of document entry (required for dmp)
        $this->document->addAttribute($extrinsic_object_node, 'objectType', 'urn:uuid:7edca82f-054d-47f2-a032-9b2a5b5186c1');

        // entryUUID - id (attribute) [1..1]
        $document_id = $document_entry->entryUUID;
        $this->document->addAttribute($extrinsic_object_node, 'id', $document_id);

        // logicalID - lid (attribute) [0..1]
        if ($document_entry->logical_id) {
            $this->document->addAttribute($extrinsic_object_node, 'lid', $document_entry->logical_id);
        }

        // mimeType - mimeType (attribute) [1..1]
        $this->document->addAttribute($extrinsic_object_node, 'mimeType', $document_entry->mimeType);

        // availabilityStatus - status [1..1]
        $default_status = 'urn:oasis:names:tc:ebxml-regrep:StatusType:Approved';
        $this->document->addAttribute(
            $extrinsic_object_node,
            'status',
            $document_entry->availabilityStatus ?: $default_status
        );

        // hash - slot [1..1]
        if ($document_entry->hash) {
            $this->document->createSlot('hash', $document_entry->hash, $extrinsic_object_node);
        }

        // size - slot [1..1]
        if ($document_entry->size !== null) {
            $this->document->createSlot('size', $document_entry->size, $extrinsic_object_node);
        }

        // creationTime - slot [1..1]
        $creation_datetime = CXDSTools::formatDatetime($document_entry->creation_datetime);
        $this->document->createSlot('creationTime', $creation_datetime, $extrinsic_object_node);

        // languageCode - slot [1..1]
        $this->document->createSlot('languageCode', $document_entry->language_code, $extrinsic_object_node);

        // legalAuthenticator - slot [1..1]
        if ($legal_authenticator = $document_entry->getLegalAuthenticator()) {
            $this->document->createSlot(
                'legalAuthenticator',
                $legal_authenticator,
                $extrinsic_object_node
            );
        }

        // repositoryUniqueId - slot [1..1]
        if ($document_entry->repository_unique_id) {
            $this->document->createSlot(
                'repositoryUniqueId',
                $document_entry->repository_unique_id,
                $extrinsic_object_node
            );
        }

        // serviceStartTime - slot [1..1]
        $start_time = CXDSTools::formatDatetime($document_entry->service_start_time);
        $this->document->createSlot('serviceStartTime', $start_time, $extrinsic_object_node);

        // serviceStopTime - slot [0..1]
        if ($document_entry->service_stop_time) {
            $stop_time = CXDSTools::formatDatetime($document_entry->service_stop_time);
            $this->document->createSlot('serviceStopTime', $stop_time, $extrinsic_object_node);
        }

        // sourcePatientId - slot [1..1]
        $this->document->createSlot('sourcePatientId', $document_entry->getSourcePatientId(), $extrinsic_object_node);

        // sourcePatientInfo - slot [0..1]
        if ($document_entry->source_patient_info) {
            $all_PID = CMbArray::array_flatten($document_entry->getSourcePatientInfo());
            $this->document->createSlot('sourcePatientInfo', $all_PID, $extrinsic_object_node);
        }

        // URI - slot [1..1]
        if ($document_entry->URI) {
            $this->document->createSlot('URI', $document_entry->URI, $extrinsic_object_node);
        }

        // documentAvailability - slot [0..1]
        if ($document_entry->documentAvailability) {
            $this->document->createSlot(
                'documentAvailability',
                $document_entry->documentAvailability,
                $extrinsic_object_node
            );
        }

        // title - name [1..1]
        $this->document->createName($document_entry->title, $extrinsic_object_node);

        // comments - description [0..1]
        if ($document_entry->comments) {
            $this->document->createDescription($document_entry->comments, $extrinsic_object_node);
        }

        // version - VersionInfo [0..1]
        if ($document_entry->version) {
            $version_node = $this->document->createRimRoot('VersionInfo', null, $extrinsic_object_node);
            $this->document->addAttribute($version_node, 'versionName', $document_entry->version);
        }

        // DocumentEntry_author - classification [1..*]
        foreach ($document_entry->_document_entry_author as $author) {
            $this->mapAuthor($author, $document_id, $extrinsic_object_node);
        }

        // class - classification [1..1]
        $class_node = $this->document->createClassification(
            $extrinsic_object_node,
            $document_id,
            'urn:uuid:41a5887f-8865-4c09-adf7-e362475b143a',
            $document_entry->_xds_class->code
        );
        $this->document->createSlot('codingScheme', $document_entry->_xds_class->code_system, $class_node);
        $this->document->createName($document_entry->_xds_class->display_name, $class_node);

        // confidentiality - classification [1..3]
        foreach ($document_entry->_confidentiality as $confidentiality) {
            $confidentiality_node = $this->document->createClassification(
                $extrinsic_object_node,
                $document_id,
                'urn:uuid:f4f85eac-e6cb-4883-b524-f2705394840f',
                $confidentiality->code
            );
            $this->document->createSlot('codingScheme', $confidentiality->code_system, $confidentiality_node);
            $this->document->createName($confidentiality->display_name, $confidentiality_node);
        }

        // eventCodeList - classification [0..*]
        foreach ($document_entry->_event_code_list as $event_code_list) {
            $event_code_node = $this->document->createClassification(
                $extrinsic_object_node,
                $document_id,
                'urn:uuid:2c6b8cb7-8b2a-4051-b291-b1ae6a575ef4',
                $event_code_list->code
            );
            $this->document->createSlot('codingScheme', $event_code_list->code_system, $event_code_node);
            $this->document->createName($event_code_list->display_name, $event_code_node);
        }

        // format - classification [1..1]
        $format_node = $this->document->createClassification(
            $extrinsic_object_node,
            $document_id,
            'urn:uuid:a09d5840-386c-46f2-b5ad-9c3699a4309d',
            $document_entry->_format->code
        );
        $this->document->createSlot('codingScheme', $document_entry->_format->code_system, $format_node);
        $this->document->createName($document_entry->_format->display_name, $format_node);

        // healthcareFacilityType - classification [1..1]
        $healthcare_node = $this->document->createClassification(
            $extrinsic_object_node,
            $document_id,
            'urn:uuid:f33fb8ac-18af-42cc-ae0e-ed0b0bdb91e1',
            $document_entry->_healthcare_facility_type->code
        );
        $this->document->createSlot(
            'codingScheme',
            $document_entry->_healthcare_facility_type->code_system,
            $healthcare_node
        );
        $this->document->createName($document_entry->_healthcare_facility_type->display_name, $healthcare_node);

        // practiceSetting - classification [1..1]
        $practice_setting_node = $this->document->createClassification(
            $extrinsic_object_node,
            $document_id,
            'urn:uuid:cccf5598-8b07-4b77-a05e-ae952c785ead',
            $document_entry->_practice_setting->code
        );
        $this->document->createSlot(
            'codingScheme',
            $document_entry->_practice_setting->code_system,
            $practice_setting_node
        );
        $this->document->createName($document_entry->_practice_setting->display_name, $practice_setting_node);

        // type - classification [1..1]
        $type_node = $this->document->createClassification(
            $extrinsic_object_node,
            $document_id,
            'urn:uuid:f0306f51-975f-434e-a61c-c59651d33983',
            $document_entry->_type->code
        );
        $this->document->createSlot('codingScheme', $document_entry->_type->code_system, $type_node);
        $this->document->createName($document_entry->_type->display_name, $type_node);

        // patientId - externalIdentifier [1..1]
        $patient_id_node = $this->document->createExternalIdentifier(
            $extrinsic_object_node,
            $document_id,
            $document_entry->getPatientId(),
            'urn:uuid:58a6f841-87b3-4a3e-92fd-a8ffeff98427'
        );
        $this->document->createName('XDSDocumentEntry.patientId', $patient_id_node);

        // uniqueId - externalIdentifier [1..1]
        $unique_id_node = $this->document->createExternalIdentifier(
            $extrinsic_object_node,
            $document_id,
            $document_entry->unique_id,
            'urn:uuid:2e82c1f6-a085-4c72-9da3-8640a32e42ab'
        );
        $this->document->createName('XDSDocumentEntry.uniqueId', $unique_id_node);

        // map content of document
        if ($document_entry->_content_file) {
            $document = $this->document->createDocumentRepositoryElement($this->root, 'Document', null);
            $this->document->addAttribute($document, 'id', $document_id);
            $document->nodeValue = base64_encode($document_entry->_content_file);
        }
    }

    /**
     * @param CXDSAssociation $association
     * @param DOMNode         $parent
     *
     * @throws CXDSException
     */
    protected function mapAssociation(CXDSAssociation $association, DOMNode $parent): void
    {
        $default_status = 'urn:oasis:names:tc:ebxml-regrep:StatusType:Approved';

        $default_submission_set_status = null;
        if (in_array($association->type, [CXDSAssociation::TYPE_HAS_MEMBER, CXDSAssociation::TYPE_SIGN])) {
            $default_submission_set_status = 'Original';
        }

        /** @var CXDSDocumentEntry|CXDSSubmissionSet $source */
        $source = $association->from;
        /** @var CXDSDocumentEntry|CXDSSubmissionSet $target */
        $target = $association->to;

        $association_node = $this->document->createRimRoot('Association', null, $parent);
        $this->document->addAttribute($association_node, 'id', CMbSecurity::generateUUID());
        $this->document->addAttribute($association_node, 'status', $association->status ?: $default_status);
        $this->document->addAttribute(
            $association_node,
            'associationType',
            $this->getAssociationTypeMapping($association->type)
        );
        $this->document->addAttribute($association_node, 'sourceObject', $source->entryUUID);
        $this->document->addAttribute($association_node, 'objectType', "urn:oasis:names:tc:ebxml-regrep:ObjectType:RegistryObject:Association");
        $this->document->addAttribute($association_node, 'targetObject', $target->entryUUID);

        if ($association->type === CXDSAssociation::TYPE_UPDATE_AVAILABILITY_STATUS) {
            $this->document->createSlot(
                'NewStatus',
                $association->new_availabilityStatus,
                $association_node
            );

            $this->document->createSlot(
                'OriginalStatus',
                $target->availabilityStatus,
                $association_node
            );
        } elseif ($status = $association->submissionSetStatus ?: $default_submission_set_status) {
            $this->document->createSlot(
                'SubmissionSetStatus',
                $status,
                $association_node
            );
        }

        if ($association->previousVersion) {
            $this->document->createSlot('PreviousVersion', $association->previousVersion, $association_node);
        }
    }

    /**
     * @param string $type
     *
     * @return string
     * @throws CXDSException
     */
    protected function getAssociationTypeMapping(string $type): string
    {
        switch ($type) {
            case CXDSAssociation::TYPE_HAS_MEMBER:
                return 'urn:oasis:names:tc:ebxml-regrep:AssociationType:HasMember';
            case CXDSAssociation::TYPE_UPDATE_AVAILABILITY_STATUS:
                return 'urn:ihe:iti:2010:AssociationType:UpdateAvailabilityStatus';
            case CXDSAssociation::TYPE_REPLACE:
                return 'urn:ihe:iti:2007:AssociationType:RPLC';
            case CXDSAssociation::TYPE_SIGN:
                return 'urn:ihe:iti:2007:AssociationType:signs';

            default:
                throw new CXDSException('Invalid xds mapping association type');
        }
    }

    /**
     * @return CXDSXmlDocument|null
     */
    public function getXmlDocument(): ?CXDSXmlDocument
    {
        return $this->document;
    }

    /**
     * @param CXDSSubmissionSetAuthor $author
     * @param string                  $entryUUID
     * @param DOMElement              $parent
     *
     * @return DOMNode
     */
    protected function mapAuthor(CXDSAuthor $author, string $entryUUID, DOMElement $parent): DOMNode
    {
        $classificationScheme = $author instanceof CXDSSubmissionSetAuthor
            ? 'urn:uuid:a7058bb9-b4e4-4307-ba5b-e3f0ab85e12d'
            : 'urn:uuid:93606bcf-9494-43ec-9b4e-a7748d1a838d';

        $author_node = $this->document->createClassification(
            $parent,
            $entryUUID,
            $classificationScheme,
            ''
        );

        // authorInstitution - Slot
        if ($author_institution = $author->getAuthorInstitution()) {
            $this->document->createSlot('authorInstitution', $author_institution, $author_node);
        }

        // authorPerson - Slot
        if ($author_person = $author->getAuthorPerson()) {
            $this->document->createSlot('authorPerson', $author_person, $author_node);
        }

        // authorRole - Slot
        if ($author_role = $author->getAuthorRole()) {
            $this->document->createSlot('authorRole', $author_role, $author_node);
        }

        // authorSpecialty - Slot
        if ($author_speciality = $author->getAuthorSpeciality()) {
            $this->document->createSlot('authorSpecialty', $author_speciality, $author_node);
        }

        return $author_node;
    }
}
