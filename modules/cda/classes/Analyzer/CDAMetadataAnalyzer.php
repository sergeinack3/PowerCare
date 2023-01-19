<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Analyzer;

use DOMNode;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Interop\Cda\CCDADomDocument;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Interop\InteropResources\valueset\CValueSet;
use Ox\Interop\Xds\Analyzer\MetadataAnalyzerInterface;
use Ox\Interop\Xds\CXDSTools;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSClass;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSConfidentiality;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSDocumentEntry;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSDocumentEntryAuthor;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSEventCodeList;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSFormat;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSHealthcareFacilityType;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSPracticeSetting;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSType;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatientINSNIR;
use Psr\SimpleCache\InvalidArgumentException;

class CDAMetadataAnalyzer implements MetadataAnalyzerInterface
{
    /** @var CCDADomDocument */
    private $cda_doc;

    /** @var CFile */
    private $file;

    /** @var string */
    private $content;

    /** @var CValueSet */
    private $jdv;

    /**
     * CDAMetadataAnalyzer constructor.
     *
     * @param CFile|null $file
     */
    public function __construct(?CFile $file = null)
    {
        $this->file = $file;
        $this->jdv  = new CANSValueSet();
    }

    private function initializeCDADocument(): bool
    {
        if ($this->cda_doc) {
            return true;
        }

        $document_item = $this->file;
        if (!$content = $document_item->getContent()) {
            if (!$content = $document_item->getBinaryContent()) {
                return false;
            }
        }

        $this->content = $content;

        $domCDA                     = new CCDADomDocument("ISO-8859-1");
        $domCDA->preserveWhiteSpace = false;
        if (!$domCDA->loadXML($content)) {
            return false;
        }

        $domCDA->getContentNodes();
        if (!$domCDA->getSetId()) {
            return false;
        }

        $this->cda_doc = $domCDA;

        return true;
    }

    /**
     * Generate CDocumentEntry for a CDocumentItem
     *
     * @param CDocumentItem $document_item
     *
     * @return CXDSDocumentEntry
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    public function generateMetadata(CDocumentItem $document_item): CXDSDocumentEntry
    {
        if (!$document_item instanceof CFile) {
            return new CXDSDocumentEntry();
        }

        $this->file = $document_item;
        $this->initializeCDADocument();
        $document_entry                = new CXDSDocumentEntry();

        // metadata document_entry
        $this->setMetadataDocumentEntry($document_entry);

        // authors
        $this->setAuthors($document_entry);

        // class
        $this->setClass($document_entry);

        // confidentiality
        $this->setConfidentiality($document_entry);

        // eventCodeList
        $this->setEventCodeList($document_entry);

        // format
        $this->setFormat($document_entry);

        // healthcare facility type
        $this->setHealthcareFacilityType($document_entry);

        // practiceSetting
        $this->setPracticeSetting($document_entry);

        // type
        $this->setType($document_entry);

        return $document_entry;
    }

    /**
     * @param CDocumentItem $document_item
     *
     * @return bool
     */
    public function isSupported(CDocumentItem $document_item): bool
    {
        $this->file = $document_item;
        if (!$document_item instanceof CFile) {
            return false;
        }

        if (!in_array($document_item->file_type, ['application/xml', "text/xml"])) {
            return false;
        }

        if (!$this->initializeCDADocument()) {
            return false;
        }

        $this->cda_doc->getContentNodes();

        return true;
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setMetadataDocumentEntry(CXDSDocumentEntry $document_entry)
    {
        $this->setMimetype($document_entry);

        $this->setHash($document_entry);

        $this->setSize($document_entry);

        $this->setCreationDatetime($document_entry);

        $this->setLanguageCode($document_entry);

        $this->setLegalAuthenticator($document_entry);

        $this->serviceDatetime($document_entry);

        $this->setPatientInformations($document_entry);

        $this->setTitle($document_entry);

        $this->setUniqueId($document_entry);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setMimetype(CXDSDocumentEntry $document_entry): void
    {
        $document_entry->mimeType = "text/xml";
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setHash(CXDSDocumentEntry $document_entry): void
    {
        $document_entry->hash = sha1($this->content);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setSize(CXDSDocumentEntry $document_entry): void
    {
        $document_entry->size = strlen($this->content);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setCreationDatetime(CXDSDocumentEntry $document_entry): void
    {
        $creation_time = $this->cda_doc->queryAttributeNodeValue('effectiveTime', null, 'value');

        $document_entry->creation_datetime = CMbDT::dateTime(CXDSTools::getTimeUtc($creation_time));
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function serviceDatetime(CXDSDocumentEntry $document_entry): void
    {
        // service_start_time
        $start_time = $this->cda_doc->queryAttributeNodeValue(
            'documentationOf/serviceEvent/effectiveTime/low',
            null,
            'value'
        );

        $document_entry->service_start_time = CMbDT::dateTime(CXDSTools::getTimeUtc($start_time));

        // service_end_time
        $stop_time = $this->cda_doc->queryAttributeNodeValue(
            'documentationOf/serviceEvent/effectiveTime/high',
            null,
            'value'
        );

        if ($stop_time) {
            $document_entry->service_stop_time = CMbDT::dateTime(CXDSTools::getTimeUtc($stop_time));
        }
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setLanguageCode(CXDSDocumentEntry $document_entry): void
    {
        $language_code = $this->cda_doc->queryAttributeNodeValue('languageCode', null, 'code');

        $document_entry->language_code = $language_code;
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setTitle(CXDSDocumentEntry $document_entry): void
    {
        $document_entry->title = $this->cda_doc->queryTextNode('title');
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setUniqueId(CXDSDocumentEntry $document_entry): void
    {
        $root      = $this->cda_doc->queryAttributeNodeValue('id', null, 'root');
        $extension = $this->cda_doc->queryAttributeNodeValue('id', null, 'extension');

        $identifier                = $extension ? "$root^$extension" : $root;
        $document_entry->unique_id = $identifier;
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setLegalAuthenticator(CXDSDocumentEntry $document_entry): void
    {
        if (!$this->cda_doc->queryNode('legalAuthenticator')) {
            return;
        }

        // Comp 1 Identifier
        $id = $this->cda_doc->queryAttributeNodeValue('legalAuthenticator/assignedEntity/id', null, 'extension');

        // Comp 2 Family
        $family = $this->cda_doc->queryTextNode('legalAuthenticator/assignedEntity/assignedPerson/name/family');

        // Comp 3 Given
        $given = $this->cda_doc->queryTextNode('legalAuthenticator/assignedEntity/assignedPerson/name/given');

        // Comp 9 Assigned authority
        $id_oid = $this->cda_doc->queryAttributeNodeValue('legalAuthenticator/assignedEntity/id', null, 'root');

        // Comp 10 Type of name
        $type_of_name = 'D';

        // Comp 13 Type of identifier
        $type = $this->getTypeofIdPerson($id_oid);

        $document_entry->setLegalAuthenticator(
            [
                'CX.1'  => $id,
                'CX.2'  => $family,
                'CX.3'  => $given,
                'CX.9'  => "&$id_oid&ISO",
                'CX.10' => $type_of_name,
                'CX.13' => $type,
            ]
        );
    }

    protected function setAuthors(CXDSDocumentEntry $document_entry): void
    {
        $author_nodes = $this->cda_doc->query('author');
        foreach ($author_nodes as $author_node) {
            $entry_author = new CXDSDocumentEntryAuthor();

            $this->setAuthorInstitution($entry_author, $author_node);

            $this->setAuthorPerson($entry_author, $author_node);

            $this->setAuthorRole($entry_author, $author_node);

            $this->setAuthorSpecialty($entry_author, $author_node);

            $document_entry->addDocumentEntryAuthor($entry_author);
        }
    }

    protected function setAuthorInstitution(CXDSDocumentEntryAuthor $entry_author, DOMNode $author_node): void
    {
        $represented_orga = $this->cda_doc->queryNode("assignedAuthor/representedOrganization", $author_node);
        $null_flavor      = $represented_orga->attributes->getNamedItem('nullFlavor');
        if (!$represented_orga || ($null_flavor && $null_flavor->textContent)) {
            return;
        }

        // Comp 1
        $name_node   = $this->cda_doc->queryNode('name', $represented_orga);
        $null_flavor = $name_node->attributes->getNamedItem('nullFlavor');
        if (($null_flavor && $null_flavor->textContent)) {
            return;
        }

        $name = null;
        if ($name_node->textContent) {
            $name = $this->cda_doc->queryTextNode('.', $name_node);
        }

        // Comp 6
        $oid = $this->cda_doc->queryAttributeNodeValue('id', $represented_orga, 'root');

        // Comp 7
        $type_id = 'IDNST';

        // Comp 10
        $id = $this->cda_doc->queryAttributeNodeValue('id', $represented_orga, 'extension');

        $entry_author->setAuthorInstitution(
            [
                'CX.1'  => $name,
                'CX.6'  => "&$oid&ISO",
                'CX.7'  => $type_id,
                'CX.10' => $id,
            ]
        );
    }

    protected function setPatientInformations(CXDSDocumentEntry $document_entry): void
    {
        $data_patient_ids = $this->extractIds();

        // PatientId
        $INS     = CMbArray::get($data_patient_ids, 'INS');
        $INS_OID = CMbArray::get($data_patient_ids, 'INS_OID');
        if ($INS && $INS_OID) {
            $document_entry->setPatientId($INS, $INS_OID);
        }

        // SourcePatientId
        $PI         = CMbArray::getRecursive($data_patient_ids, 'other_id extension');
        $PI_oid     = CMbArray::getRecursive($data_patient_ids, 'other_id root');
        $source_id  = ($PI && $PI_oid) ? $PI : $INS;
        $source_oid = ($PI && $PI_oid) ? $PI_oid : $INS_OID;
        $document_entry->setSourcePatientId($source_id, $source_oid);

        // SourcePatientInfo
        $this->setSourcePatientInfo($document_entry);
    }

    /**
     * @return array
     */
    protected function extractIds(): array
    {
        $patientRole  = $this->cda_doc->queryNode('recordTarget/patientRole');
        $INS_NIR_TEST = $this->getIdentifier($patientRole, CPatientINSNIR::OID_INS_NIR_TEST);
        $INS_NIR      = $this->getIdentifier($patientRole, CPatientINSNIR::OID_INS_NIR);
        $INS_NIA      = $this->getIdentifier($patientRole, CPatientINSNIR::OID_INS_NIA);

        $other_ids = [];
        $id_nodes  = $this->cda_doc->query('recordTarget/patientRole/id');
        foreach ($id_nodes as $node) {
            $root = $this->cda_doc->queryAttributeNodeValue('.', $node, 'root');
            if (in_array(
                $root,
                [CPatientINSNIR::OID_INS_NIR_TEST, CPatientINSNIR::OID_INS_NIA, CPatientINSNIR::OID_INS_NIR]
            )) {
                continue;
            }

            $other_ids[] = [
                'root'      => $root,
                'extension' => $this->cda_doc->queryAttributeNodeValue('.', $node, 'extension'),
            ];
        }

        $INS_OID = CPatientINSNIR::OID_INS_NIR;
        if (!$INS = $INS_NIR) {
            $INS_OID = CPatientINSNIR::OID_INS_NIA;
            if (!$INS = $INS_NIA) {
                $INS     = $INS_NIR_TEST;
                $INS_OID = CPatientINSNIR::OID_INS_NIR_TEST;
            }
        }

        $data = [];
        if ($INS) {
            $data['INS']     = $INS;
            $data['INS_OID'] = $INS_OID;
        }

        if ($other_ids) {
            $data['other_id'] = reset($other_ids);
        }

        return $data;
    }

    /**
     * Get patient identifier
     *
     * @param DOMNode $node patientRole
     * @param string  $oid  OID
     *
     * @return string|null
     */
    protected function getIdentifier(DOMNode $node, string $OID): ?string
    {
        return $this->cda_doc->queryAttributeNodeValue(
            "id[@root='$OID']",
            $node,
            "extension"
        );
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     *
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    protected function setClass(CXDSDocumentEntry $document_entry)
    {
        $type_code = $this->cda_doc->queryAttributeNodeValue('code', null, 'code');

        // search specific type code in xds value set
        if (!$class = $this->jdv->searchClassCode($type_code)) {
            return;
        }

        $xds_class               = new CXDSClass();
        $xds_class->code         = $class['code'];
        $xds_class->code_system  = $class['codeSystem'];
        $xds_class->display_name = $class['displayName'];

        $document_entry->setXdsClass($xds_class);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    private function setConfidentiality(CXDSDocumentEntry $document_entry)
    {
        $confidentiality_code         = $this->cda_doc->queryAttributeNodeValue('confidentialityCode', null, 'code');
        $confidentiality_code_system  = $this->cda_doc->queryAttributeNodeValue(
            'confidentialityCode',
            null,
            'codeSystem'
        );
        $confidentiality_display_name = $this->cda_doc->queryAttributeNodeValue(
            'confidentialityCode',
            null,
            'displayName'
        );

        $xds_confidentiality               = new CXDSConfidentiality();
        $xds_confidentiality->code         = $confidentiality_code;
        $xds_confidentiality->code_system  = $confidentiality_code_system;
        $xds_confidentiality->display_name = $confidentiality_display_name;

        $document_entry->setConfidentiality([$xds_confidentiality]);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    private function setEventCodeList(CXDSDocumentEntry $document_entry)
    {
        $document_of_nodes = $this->cda_doc->query('documentationOf');

        foreach ($document_of_nodes as $node) {
            if (!$event_code_node = $this->cda_doc->queryNode('serviceEvent/code', $node)) {
                continue;
            }

            if ($this->cda_doc->queryAttributeNodeValue('.',  $event_code_node, 'nullFlavor')) {
                continue;
            }

            $event_code               = new CXDSEventCodeList();
            $event_code->code         = $this->cda_doc->queryAttributeNodeValue('.', $event_code_node, 'code');
            $event_code->display_name = $this->cda_doc->queryAttributeNodeValue('.', $event_code_node, 'displayName');
            $event_code->code_system  = $this->cda_doc->queryAttributeNodeValue('.', $event_code_node, 'codeSystem');

            $document_entry->addEventCodeList($event_code);
        }
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     *
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    private function setFormat(CXDSDocumentEntry $document_entry)
    {
        // level 3
        if ($this->cda_doc->getLevel() === CCDADomDocument::LEVEL_3) {
            $template_id_nodes     = $this->cda_doc->query('templateId');
            $last_template_id_node = $template_id_nodes->item($template_id_nodes->length - 1);
            $code                  = $this->cda_doc->queryAttributeNodeValue('.', $last_template_id_node, 'root');

            if (!$coding_format = $this->jdv->searchFormatCode($code, 3)) {
                return;
            }
        } else {        // level 1F
            $structure_document = $this->cda_doc->getNonXMLBody();
            $code               = $this->cda_doc->queryAttributeNodeValue('text', $structure_document, 'mediaType');

            if (!$coding_format = $this->jdv->searchFormatCode($code, 1) ?: $this->jdv->searchFormatCode(
                'text/plain',
                1
            )) {
                return;
            }
        }

        $code         = CMbArray::get($coding_format, 'code');
        $display_name = CMbArray::get($coding_format, 'displayName');
        $code_system  = CMbArray::get($coding_format, 'codeSystem');

        $xds_format               = new CXDSFormat();
        $xds_format->code         = $code;
        $xds_format->code_system  = $code_system;
        $xds_format->display_name = $display_name;

        $document_entry->setFormat($xds_format);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setHealthcareFacilityType(CXDSDocumentEntry $document_entry)
    {
        if (!$healthcare_node = $this->cda_doc->queryNode(
            'componentOf/encompassingEncounter/location/healthCareFacility'
        )) {
            return;
        }

        $xds_healthcare               = new CXDSHealthcareFacilityType();
        $xds_healthcare->code         = $this->cda_doc->queryAttributeNodeValue('code', $healthcare_node, 'code');
        $xds_healthcare->code_system  = $this->cda_doc->queryAttributeNodeValue('code', $healthcare_node, 'codeSystem');
        $xds_healthcare->display_name = $this->cda_doc->queryAttributeNodeValue(
            'code',
            $healthcare_node,
            'displayName'
        );

        $document_entry->setHealthcareFacilityType($xds_healthcare);
    }

    protected function setPracticeSetting(CXDSDocumentEntry $document_entry)
    {
        if (!$organization_node = $this->cda_doc->queryNode(
            'documentationOf/serviceEvent/performer/assignedEntity/representedOrganization'
        )) {
            return;
        }

        $xds_pratice_setting               = new CXDSPracticeSetting();
        $xds_pratice_setting->code         = $this->cda_doc->queryAttributeNodeValue(
            'standardIndustryClassCode',
            $organization_node,
            'code'
        );
        $xds_pratice_setting->code_system  = $this->cda_doc->queryAttributeNodeValue(
            'standardIndustryClassCode',
            $organization_node,
            'codeSystem'
        );
        $xds_pratice_setting->display_name = $this->cda_doc->queryAttributeNodeValue(
            'standardIndustryClassCode',
            $organization_node,
            'displayName'
        );

        $document_entry->setPracticeSetting($xds_pratice_setting);
    }

    protected function setType(CXDSDocumentEntry $document_entry)
    {
        $code_node = $this->cda_doc->queryNode('code');

        $xds_type               = new CXDSType();
        $xds_type->code         = $this->cda_doc->queryAttributeNodeValue('.', $code_node, 'code');
        $xds_type->code_system  = $this->cda_doc->queryAttributeNodeValue('.', $code_node, 'codeSystem');
        $xds_type->display_name = $this->cda_doc->queryAttributeNodeValue('.', $code_node, 'displayName');

        $document_entry->setType($xds_type);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setSourcePatientInfo(CXDSDocumentEntry $document_entry)
    {
        // PID-5 : family / given ...
        $PID_5 = $this->getPID5();

        // PID-7 : birthdate
        $birth_date = $this->cda_doc->queryAttributeNodeValue(
            'recordTarget/patientRole/patient/birthTime',
            null,
            'value'
        );
        $PID_7      = [
            'CX.1' => CMbDT::format(CMbDT::date($birth_date), '%Y%m%d'),
        ];

        // PID-8 : Gender sex
        $gender_sex = $this->cda_doc->queryAttributeNodeValue(
            "recordTarget/patientRole/patient/administrativeGenderCode",
            null,
            "code"
        );
        $PID_8      = [
            'CX.1' => $gender_sex,
        ];

        // PID-11 : birth place
        if ($place_node = $this->cda_doc->queryNode('recordTarget/patientRole/patient/birthplace/place')) {
            $postal = $this->cda_doc->queryTextNode('addr/county', $place_node);
            $city   = $this->cda_doc->queryTextNode('addr/city', $place_node);
            $name   = $this->cda_doc->queryTextNode('name', $place_node);

            $PID_11 = [
                'CX.1' => $name,
                'CX.3' => $city,
                'CX.5' => $postal,
                'CX.7' => 'BDL',
                'CX.9' => $postal,
            ];
        }

        $document_entry->setSourcePatientInfo(
            [
                'PID-5'  => $PID_5,
                'PID-7'  => $PID_7,
                'PID-8'  => $PID_8,
                'PID-11' => $PID_11 ?? null,
            ]
        );
    }

    protected function getPID5(): array
    {
        $name_node = $this->cda_doc->queryNode('recordTarget/patientRole/patient/name');

        // PID-5
        $PID_5 = [];

        $family      = $this->cda_doc->queryTextNode('family[@qualifier="BR"]', $name_node);
        $first_given = $this->cda_doc->queryTextNode('given[@qualifier="BR"]', $name_node);
        $given_nodes = $this->cda_doc->query('given[not(@qualifier)]', $name_node);
        $other_given = [];
        foreach ($given_nodes as $given_node) {
            $other_given[] = $this->cda_doc->queryTextNode('.', $given_node);
        }

        if (!$first_given) {
            $first_given = reset($other_given);
        }

        // PID-5 : L
        if ($family || $first_given || $other_given) {
            $given   = $other_given ? implode("", $other_given) : null;
            $PID_5[] = [
                'CX.1' => $family,
                'CX.2' => $first_given,
                'CX.3' => $given,
                'CX.7' => 'L',
            ];
        }

        $data_used = [];
        $family_used = $this->cda_doc->queryTextNode('family[@qualifier="CL"]', $name_node);
        $given_used  = $this->cda_doc->queryTextNode('given[@qualifier="CL"]', $name_node);
        if ($family_used || $given_used) {
            $data_used[] = [
                'family_used' => $family_used,
                'given_used'  => $given_used,
            ];
        }

        $family_used = $this->cda_doc->queryTextNode('family[@qualifier="SP"]', $name_node);
        $given_used  = $this->cda_doc->queryTextNode('given[@qualifier="SP"]', $name_node);
        if ($family_used || $given_used) {
            $data_used[] = [
                'family_used' => $family_used,
                'given_used'  => $given_used,
            ];
        }

        // PID-5 : D
        if ($data_used) {
            foreach ($data_used as $values) {
                $family_used = CMbArray::get($values, 'family_used');
                $given_used = CMbArray::get($values, 'given_used');
                if ($family_used || $given_used) {
                    $PID_5[] = [
                        'CX.1' => $family_used ?: $family,
                        'CX.2' => $given_used ?: $first_given,
                        'CX.7' => 'D',
                    ];
                }
            }
        }

        return $PID_5;
    }

    /**
     * @param CXDSDocumentEntryAuthor $entry_author
     * @param                         $author_node
     */
    protected function setAuthorPerson(CXDSDocumentEntryAuthor $entry_author, $author_node)
    {
        // comp 1
        $id = $this->cda_doc->queryAttributeNodeValue('assignedAuthor/id', $author_node, 'extension');

        // comp 2
        if ($author_person = $this->cda_doc->queryNode('assignedAuthor/assignedPerson', $author_node)) {
            $name = $this->cda_doc->queryTextNode('name/family', $author_person);
        } else {
            $name = $this->cda_doc->queryTextNode('assignedAuthor/assignedAuthoringDevice/softwareName', $author_node);
        }

        // comp 3
        if ($author_person) {
            $given = $this->cda_doc->queryTextNode('name/given', $author_person);
        } else {
            $given = $this->cda_doc->queryTextNode(
                'assignedAuthor/assignedAuthoringDevice/manufacturerModelName',
                $author_node
            );
        }

        // comp 9
        $oid = $this->cda_doc->queryAttributeNodeValue('assignedAuthor/id', $author_node, 'root');

        // comp 10
        $type_of_name = $author_person ? 'D' : 'U';

        // comp 13
        $type_oid = $this->getTypeofIdPerson($oid);

        $entry_author->setAuthorPerson(
            [
                'CX.1'  => $id,
                'CX.2'  => $name,
                'CX.3'  => $given,
                'CX.9'  => "&$oid&ISO",
                'CX.10' => $type_of_name,
                'CX.13' => $type_oid,
            ]
        );
    }

    /**
     * @param string $oid
     *
     * @return string
     */
    protected function getTypeofIdPerson(string $oid): string
    {
        $type = "RI";
        if (in_array(
            $oid,
            [CPatientINSNIR::OID_INS_NIA, CPatientINSNIR::OID_INS_NIR, CPatientINSNIR::OID_INS_NIR_TEST]
        )) {
            $type = 'NH';
        }

        if ($oid == CMedecin::OID_IDENTIFIER_NATIONAL) {
            $type = 'IDNPS';
        }

        return $type;
    }

    /**
     * @param CXDSDocumentEntryAuthor $entry_author
     * @param                         $author_node
     */
    protected function setAuthorRole(CXDSDocumentEntryAuthor $entry_author, $author_node)
    {
        if (!$function_code = $this->cda_doc->queryAttributeNodeValue('functionCode', $author_node, 'displayName')) {
            return;
        }

        $entry_author->setAuthorRole($function_code);
    }

    /**
     * @param CXDSDocumentEntryAuthor $entry_author
     * @param                         $author_node
     */
    protected function setAuthorSpecialty(CXDSDocumentEntryAuthor $entry_author, $author_node)
    {
        if (!$code_node = $this->cda_doc->queryNode('assignedAuthor/code', $author_node)) {
            return;
        }

        $entry_author->setAuthorSpeciality(
            [
                'CX.1' => $this->cda_doc->queryAttributeNodeValue('.', $code_node, 'code'),
                'CX.2' => $this->cda_doc->queryAttributeNodeValue('.', $code_node, 'displayName'),
                'CX.3' => $this->cda_doc->queryAttributeNodeValue('.', $code_node, 'codeSystem'),
            ]
        );
    }
}
