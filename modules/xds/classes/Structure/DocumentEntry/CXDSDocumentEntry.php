<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure\DocumentEntry;

use Ox\Core\CMbSecurity;
use Ox\Interop\Cda\Analyzer\CDAMetadataAnalyzer;
use Ox\Interop\Xds\Analyzer\DocumentItemMetadataAnalyzer;
use Ox\Interop\Xds\Analyzer\MetadataAnalyzerInterface;
use Ox\Interop\Xds\CXDSTools;
use Ox\Interop\Xds\Exception\CXDSException;
use Ox\Interop\Xds\Structure\XDSElementInterface;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Patients\CPatientINSNIR;

class CXDSDocumentEntry implements XDSElementInterface
{
    /** @var string|null */
    public $entryUUID; // only server

    /** @var string|null */
    public $logical_id;

    /** @var string */
    public $mimeType;

    /** @var string|null */
    public $availabilityStatus; // only server

    /** @var string */
    public $hash;

    /** @var string */
    public $size;

    /** @var string */
    public $creation_datetime;

    /** @var string */
    public $language_code;

    /** @var string */
    public $repository_unique_id; // only server

    /** @var string */
    public $service_start_time;

    /** @var string|null */
    public $service_stop_time;

    /** @var array <INS, CODE_SYSTEM> */
    public $patient_id;

    /** @var array <CX-1, CX-4, CX-5> */
    public $source_patient_id;

    /** @var array <PID-5, PID-7, PID-8, PID-11> */
    public $source_patient_info;

    /** @var string */
    public $documentAvailability; // only server

    /** @var array <CX.1, CX.2, CX.3, CX.9, CX.10, CX.13> */
    public $legal_authenticator;

    /** @var string|null */
    public $URI; // only server // todo doit être utilisé dans ITI-32

    /** @var string */
    public $comments;

    /** @var string */
    public $title;

    /** @var string|null */
    public $version;

    /** @var string */
    public $unique_id;

    /** @var CXDSFormat */
    public $_format;

    /** @var CXDSDocumentEntryAuthor[] */
    public $_document_entry_author = [];

    /** @var CXDSClass */
    public $_xds_class;

    /** @var CXDSConfidentiality[] */
    public $_confidentiality = [];

    /** @var CXDSEventCodeList[] */
    public $_event_code_list = [];

    /** @var CXDSHealthcareFacilityType */
    public $_healthcare_facility_type;

    /** @var CXDSPracticeSetting */
    public $_practice_setting;

    /** @var CXDSType */
    public $_type;

    /** @var string */
    public $_content_file;

    public function __construct(string $entryUUID = null)
    {
        $this->entryUUID = $entryUUID ?: CMbSecurity::generateUUID();
    }

    /**
     * Generate un CDocumentEntry for a CFILE
     *
     * @param CDocumentItem $document
     * @param MetadataAnalyzerInterface|null $analyzer
     *
     * @return static
     * @throws CXDSException
     */
    public static function fromDocument(CDocumentItem $document, ?MetadataAnalyzerInterface $analyzer = null): self
    {
        if (!$analyzer) {
            // load all available
            // prioritize
            $analyzer = self::detectAnalyzer($document);
        }

        if (!$analyzer) {
            throw new CXDSException("MetadataAnalyzerInterface.none");
        }

        return $analyzer->generateMetadata($document);
    }

    /**
     * @param CDocumentItem $document
     *
     * @return MetadataAnalyzerInterface|null
     */
    private static function detectAnalyzer(CDocumentItem $document): ?MetadataAnalyzerInterface
    {
        $cda_analyzer = new CDAMetadataAnalyzer();
        if ($cda_analyzer->isSupported($document)) {
            return $cda_analyzer;
        }

        $document_analyser = new DocumentItemMetadataAnalyzer();
        if ($document_analyser->isSupported($document)) {
            return $document_analyser;
        }

        return null;
    }

    /**
     * @param CXDSFormat $format
     */
    public function setFormat(CXDSFormat $format): void
    {
        $this->_format = $format;
    }

    /**
     * @param CXDSDocumentEntryAuthor[] $document_entry_author
     */
    public function setDocumentEntryAuthor(array $document_entry_author): void
    {
        $this->_document_entry_author = $document_entry_author;
    }

    /**
     * @param CXDSDocumentEntryAuthor $document_entry_author
     */
    public function addDocumentEntryAuthor(CXDSDocumentEntryAuthor $document_entry_author): void
    {
        $this->_document_entry_author[] = $document_entry_author;
    }

    /**
     * @param CXDSClass $xds_class
     */
    public function setXdsClass(CXDSClass $xds_class): void
    {
        $this->_xds_class = $xds_class;
    }

    /**
     * @param CXDSConfidentiality[] $_confidentiality
     */
    public function setConfidentiality(array $_confidentiality): void
    {
        $this->_confidentiality = $_confidentiality;
    }

    /**
     * @param CXDSConfidentiality $confidentiality
     */
    public function addConfidentiality(CXDSConfidentiality $confidentiality): void
    {
        $this->_confidentiality[] = $confidentiality;
    }

    /**
     * @param CXDSEventCodeList[] $event_code_list
     */
    public function setEventCodeList(array $event_code_list): void
    {
        $this->_event_code_list = $event_code_list;
    }

    /**
     * @param CXDSEventCodeList $event_code_list
     */
    public function addEventCodeList(CXDSEventCodeList $event_code_list): void
    {
        $this->_event_code_list[] = $event_code_list;
    }

    /**
     * @param CXDSHealthcareFacilityType $healthcare_facility_type
     */
    public function setHealthcareFacilityType(CXDSHealthcareFacilityType $healthcare_facility_type): void
    {
        $this->_healthcare_facility_type = $healthcare_facility_type;
    }

    /**
     * @param CXDSPracticeSetting $practice_setting
     */
    public function setPracticeSetting(CXDSPracticeSetting $practice_setting): void
    {
        $this->_practice_setting = $practice_setting;
    }

    /**
     * @param CXDSType $type
     */
    public function setType(CXDSType $type): void
    {
        $this->_type = $type;
    }

    /**
     * @param string $INS
     * @param string $INS_OID
     */
    public function setPatientId(string $INS, string $INS_OID): void
    {
        if (!$INS || !$INS_OID) {
            return;
        }

        $this->patient_id = [
            'CX.1' => $INS,
            'CX.4' => "&$INS_OID&ISO",
            'CX.5' => 'NH',
        ];
    }

    /**
     * @param string $id
     * @param string $oid
     */
    public function setSourcePatientId(string $id, string $oid): void
    {
        $type = 'PI'; // personal identifier
        if (in_array(
            $oid,
            [CPatientINSNIR::OID_INS_NIR_TEST, CPatientINSNIR::OID_INS_NIA, CPatientINSNIR::OID_INS_NIR]
        )) {
            $type = 'NH'; // national identifier
        }

        $this->source_patient_id = [
            'CX.1' => $id,
            'CX.4' => "&$oid&ISO",
            'CX.5' => $type,
        ];
    }

    /**
     * @param array $array <PID-5, PID-7, PID-8, PID-11>
     */
    public function setSourcePatientInfo(array $infos)
    {
        $this->source_patient_info = $infos;
    }

    /**
     * @param array $legal_authenticator
     */
    public function setLegalAuthenticator(array $legal_authenticator): void
    {
        $this->legal_authenticator = $legal_authenticator;
    }

    /**
     * @return string
     */
    public function getLegalAuthenticator(): string
    {
        return CXDSTools::serializeHL7v2Components($this->legal_authenticator ?: []);
    }

    /**
     * @return string
     */
    public function getSourcePatientId(): string
    {
        return CXDSTools::serializeHL7v2Components($this->source_patient_id ?: []);
    }

    /**
     * @return string
     */
    public function getPatientId(): string
    {
        return CXDSTools::serializeHL7v2Components($this->patient_id ?: []);
    }

    /**
     * @return array
     */
    public function getSourcePatientInfo(): array
    {
        if (!$this->source_patient_info) {
            return [];
        }

        $source_patient_infos = array_filter($this->source_patient_info);

        $result = [];
        foreach ($source_patient_infos as $PID_X => $components) {
            $first_key = array_key_first($components);
            if (is_string($first_key)) {
                $result[$PID_X] = "$PID_X|" . CXDSTools::serializeHL7v2Components($components);
            } else {
                foreach ($components as $component) {
                    $result[$PID_X][] = "$PID_X|" . CXDSTools::serializeHL7v2Components($component);
                }
            }
        }

        return $result;
    }
}
