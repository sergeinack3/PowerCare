<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Analyzer;

use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Core\CMbArray;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Interop\Xds\CXDSTools;
use Ox\Interop\Xds\Exception\CXDSException;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSClass;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSConfidentiality;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSDocumentEntry;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSDocumentEntryAuthor;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSEventCodeList;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSFormat;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSHealthcareFacilityType;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSPracticeSetting;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSType;
use Ox\Interop\Xds\Structure\SubmissionSet\CXDSSubmissionSet;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Psr\SimpleCache\InvalidArgumentException;

class SignatureMetadataAnalyzer implements MetadataAnalyzerInterface
{
    /** @var CXDSSubmissionSet */
    private $submission_set;

    /** @var string */
    private $content;

    /**
     * @param CXDSSubmissionSet $submission_set
     *
     * @return SignatureMetadataAnalyzer
     */
    public function setSubmissionSet(CXDSSubmissionSet $submission_set): self
    {
        $this->submission_set = $submission_set;

        return $this;
    }

    /**
     * @param CFile $document_item
     *
     * @return CXDSDocumentEntry
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException|CXDSException
     */
    public function generateMetadata(CDocumentItem $document_item): CXDSDocumentEntry
    {
        $document_entry = new CXDSDocumentEntry();
        if (!$this->isSupported($document_item)) {
            return $document_entry;
        }

        // authors
        $this->setAuthors($document_entry);

        // class
        $this->setClass($document_entry);

        // eventCodeList
        $this->setEventCodeList($document_entry);

        // confidentiality
        $this->setConfidentiality($document_entry);

        // format
        $this->setFormat($document_entry);

        // healthcare facility type
        $this->setHealthcareFacilityType($document_entry);

        // practiceSetting
        $this->setPracticeSetting($document_entry);

        // type
        $this->setType($document_entry);

        // metadata document_entry
        $this->setMetadataDocumentEntry($document_entry);

        return $document_entry;
    }

    public function isSupported(CDocumentItem $document_item): bool
    {
        return !is_null($this->submission_set);
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

        $this->setTitle($document_entry);

        $this->setUniqueId($document_entry);

        $this->setPatientId($document_entry);

        $this->setSourcePatientId($document_entry);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setMimetype(CXDSDocumentEntry $document_entry): void
    {
        $document_entry->mimeType = 'text/xml';
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setHash(CXDSDocumentEntry $document_entry): void
    {
        // not used for now
        // $document_entry->hash = sha1($this->content);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setSize(CXDSDocumentEntry $document_entry): void
    {
        // not used for now
        //$document_entry->size = strlen($this->content);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setCreationDatetime(CXDSDocumentEntry $document_entry): void
    {
        $document_entry->creation_datetime = $this->submission_set->submissionTime ?: CXDSTools::getTimeUtc();
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setPatientId(CXDSDocumentEntry $document_entry): void
    {
        $document_entry->patient_id = $this->submission_set->patient_id;
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setSourcePatientId(CXDSDocumentEntry $document_entry): void
    {
        $document_entry->source_patient_id = $this->submission_set->patient_id;
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function serviceDatetime(CXDSDocumentEntry $document_entry): void
    {
        $document_entry->service_start_time = $document_entry->creation_datetime;
        $document_entry->service_stop_time  = $document_entry->creation_datetime;
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setLanguageCode(CXDSDocumentEntry $document_entry): void
    {
        $document_entry->language_code = 'art';
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setTitle(CXDSDocumentEntry $document_entry): void
    {
        $document_entry->title = 'Source'; // should be equals to display_name of eventCodeList
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setUniqueId(CXDSDocumentEntry $document_entry): void
    {
        $document_entry->unique_id = $this->submission_set->unique_id . '0';
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setLegalAuthenticator(CXDSDocumentEntry $document_entry): void
    {
        $author = reset($document_entry->_document_entry_author);

        $document_entry->setLegalAuthenticator($author->author_person);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setAuthors(CXDSDocumentEntry $document_entry): void
    {
        $authors = [];
        foreach ($this->submission_set->_submission_set_author as $xds_author) {
            $author                     = new CXDSDocumentEntryAuthor();
            $author->author_institution = $xds_author->author_institution;
            $author->author_person      = $xds_author->author_person;
            $author->author_role        = $xds_author->author_role;
            $author->author_speciality  = $xds_author->author_speciality;

            $authors[] = $author;
        }

        $document_entry->_document_entry_author = $authors;
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setClass(CXDSDocumentEntry $document_entry)
    {
        $class               = new CXDSClass();
        $class->code         = 'urn:oid:1.3.6.1.4.1.19376.1.2.1.1.1';
        $class->code_system  = 'URN';
        $class->display_name = 'Digital Signature';

        $document_entry->setXdsClass($class);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    private function setConfidentiality(CXDSDocumentEntry $document_entry)
    {
        $confidentiality = CXDSConfidentiality::getMasquage('N');
        $document_entry->addConfidentiality($confidentiality);

        $confidentiality = CXDSConfidentiality::getMasquage('MASQUE_PS');
        $document_entry->addConfidentiality($confidentiality);

        $confidentiality = CXDSConfidentiality::getMasquage('INVISIBLE_PATIENT');
        $document_entry->addConfidentiality($confidentiality);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    private function setEventCodeList(CXDSDocumentEntry $document_entry)
    {
        $event_code_list               = new CXDSEventCodeList();
        $event_code_list->code         = '1.2.840.10065.1.12.1.14';
        $event_code_list->code_system  = '1.2.840.10065.1.12';
        $event_code_list->display_name = 'Source';

        $document_entry->addEventCodeList($event_code_list);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     *
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    protected function setFormat(CXDSDocumentEntry $document_entry)
    {
        $format               = new CXDSFormat();
        $format->code         = 'http://www.w3.org/2000/09/xmldsig#';
        $format->code_system  = 'URN';
        $format->display_name = 'Default Signature Style';

        $document_entry->setFormat($format);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setHealthcareFacilityType(CXDSDocumentEntry $document_entry)
    {
        if (!$this->submission_set->group) {
            throw CXDSException::missingConfigGroup();
        }

        $values                   = CANSValueSet::getHealthcareFacilityTypeCode($this->submission_set->group);
        $healthcare               = new CXDSHealthcareFacilityType();
        $healthcare->code         = CMbArray::get($values, 'code');
        $healthcare->code_system  = CMbArray::get($values, 'codeSystem');
        $healthcare->display_name = CMbArray::get($values, 'displayName');

        $document_entry->setHealthcareFacilityType($healthcare);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     *
     * @throws \Exception
     */
    protected function setPracticeSetting(CXDSDocumentEntry $document_entry)
    {
        $values = CANSValueSet::getPracticeSettingCode();
        $practice_setting               = new CXDSPracticeSetting();
        $practice_setting->code         = CMbArray::get($values, 'code');
        $practice_setting->code_system  = CMbArray::get($values, 'codeSystem');
        $practice_setting->display_name = CMbArray::get($values, 'displayName');

        $document_entry->setPracticeSetting($practice_setting);
    }

    /**
     * @param CXDSDocumentEntry $document_entry
     */
    protected function setType(CXDSDocumentEntry $document_entry)
    {
        $type               = new CXDSType();
        $type->code         = 'E1762';
        $type->display_name = 'Full Document';
        $type->code_system  = 'ASTM';

        $document_entry->setType($type);
    }
}
