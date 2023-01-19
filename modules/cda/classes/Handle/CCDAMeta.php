<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Handle;

use Exception;
use Ox\Core\CStoredObject;
use Ox\Interop\Cda\CCDAPOCD_HD000040;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;

class CCDAMeta extends CStoredObject
{
    // DB Table key
    /** @var int */
    public $cda_meta_id;

    /** @var string */
    public $created_datetime;
    /** @var string CFile|CObservationResultSet */
    public $target_class;
    /** @var int */
    public $target_id;
    /** @var int CPatient */
    public $patient_id;
    /** @var string */
    public $id;
    /** @var string */
    public $relatedDocumentId;
    /** @var string */
    public $relatedDocumentIdCodeCodeSytem;
    /** @var string */
    public $code;
    /** @var string */
    public $codeCodeSystem;
    /** @var string */
    public $title;
    /** @var string */
    public $effectiveTime;
    /** @var string */
    public $confidentialityCode;
    /** @var string */
    public $confidentialityCodeCodeSytem;
    /** @var string */
    public $languageCode;
    /** @var string */
    public $setId;
    /** @var int */
    public $versionNumber;

    // Forward ref
    /** @var CFile|CObservationResultSet */
    public $_ref_target_object;
    /** @var CPatient */
    public $_ref_patient;

    /** @var CCDAMetaTypeOfCare|null */
    public $_current_type_of_care = null;

    /**
     * @inheritDoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'cda_meta';
        $spec->key   = 'cda_meta_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props['created_datetime']               = "dateTime notNull";
        $props["patient_id"]                     = "ref notNull class|CPatient back|record_target";
        $props["target_id"]                      = "ref class|CMbObject meta|target_class back|cda_metadata";
        $props["target_class"]                   = "enum list|CFile|CObservationResultSet";
        $props['id']                             = "str notNull";
        $props['relatedDocumentId']              = "str";
        $props['relatedDocumentIdCodeCodeSytem'] = "str";
        $props['code']                           = "str";
        $props['codeCodeSystem']                 = "str";
        $props['title']                          = "str";
        $props['effectiveTime']                  = "dateTime";
        $props['confidentialityCode']            = "str";
        $props['confidentialityCodeCodeSytem']   = "str";
        $props['languageCode']                   = "str";
        $props['setId']                          = "str";
        $props['versionNumber']                  = "num";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        $this->_view = $this->title;

        parent::updateFormFields();
    }

    /**
     * Set object
     *
     * @param CFile|CObservationResultSet $target Target object
     *
     * @return void
     */
    public function setTarget(CStoredObject $target): void
    {
        $this->target_class = $target->_class;
        $this->target_id    = $target->_id;
    }

    /**
     * Load target object
     *
     * @return CFile|CObservationResultSet|CStoredObject
     */
    public function loadRefObject(): CStoredObject
    {
        $this->_ref_target_object = new $this->target_class();
        $this->_ref_target_object->load($this->target_id);

        return $this->_ref_target_object;
    }

    /**
     * Load patient
     *
     * @return CPatient|CStoredObject
     */
    public function loadRefPatient(): CPatient
    {
        return $this->_ref_patient = $this->loadFwdRef("patient_id", true);
    }

    /**
     * @return CCDAMetaTypeOfCare|null
     * @throws Exception
     */
    public function loadLastBackRefTypeOfCare(): ?CCDAMetaTypeOfCare
    {
        return $this->_current_type_of_care = $this->loadLastBackRef('cda_meta_type_of_care');
    }

    /**
     * Handle metadata
     *
     * @param CCDAHandle $cda_handle
     *
     * @return void
     */
    public function handle(CCDAHandle $cda_handle): void
    {
        $cda_document = $cda_handle->getCDADomDocument();
        $report       = $cda_document->getReport();

        $this->id = $cda_document->getId();
        // On recherche si on a déjà récupéré les métadonnées
        $this->loadMatchingObject();
        $this->code                         = $cda_handle->getCodeAttributNode('code');
        $this->codeCodeSystem               = $cda_handle->getCodeSystemAttributNode('code');
        $this->title                        = $cda_document->queryTextNode('title');
        $this->effectiveTime                = $cda_document->getEffectiveTime();
        $this->confidentialityCode          = $cda_handle->getCodeAttributNode('confidentialityCode');
        $this->confidentialityCodeCodeSytem = $cda_handle->getCodeSystemAttributNode('confidentialityCode');
        $this->languageCode                 = $cda_handle->getCodeAttributNode('languageCode');
        $this->setId                        = $cda_document->getSetId();
        if (!$this->setId) {
            $this->setId = $cda_document->getId();
        }
        $this->relatedDocumentId = $cda_document->getRelatedDocumentId();
        $this->versionNumber     = $cda_handle->getValueAttributNode('versionNumber');

        if (!$patient = $cda_handle->getPatient()) {
            $report->addItemFailed($this, "CCDAMeta-msg-Patient missing");

            return;
        }
        $this->patient_id = $patient->_id;

        if ($msg = $this->store()) {
            $report->addItemFailed($this, $msg);
        }

        $report->addItemsStored($this);
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if (!$this->_id) {
            $this->created_datetime = "now";
        }

        if ($msg = parent::store()) {
            return $msg;
        }
    }

    /**
     * @return string
     */
    public function getSetId(): string
    {
        return $this->setId;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Load file link to cda meta
     *
     * @return CFile|null
     * @throws Exception
     */
    public function loadFile(string $tag = null): ?CFile
    {
        $idex = CIdSante400::getMatch('CFile', $tag, CCDAPOCD_HD000040::CDA_PREFIX . $this->id);
        if ($idex->_id) {
            return $idex->loadTargetObject();
        }

        return null;
    }

    /**
     * Get related CCDAMeta for old document
     *
     * @return $this|null
     * @throws Exception
     */
    public function getRelatedMeta(): ?self
    {
        if (!$this->relatedDocumentId) {
            return null;
        }

        $old_document_meta = new self();
        $old_document_meta->id = $this->relatedDocumentId;
        $old_document_meta->setId = $this->setId;
        $old_document_meta->loadMatchingObject();

        return $old_document_meta->_id ? $old_document_meta : null;
    }

    /**
     * Get related old document
     *
     * @return CFile|null
     * @throws Exception
     */
    public function getRelatedDocument(): ?CFile
    {
        if (!$related_meta = $this->getRelatedMeta()) {
            return null;
        }

        return $related_meta->loadFile();
    }
}
