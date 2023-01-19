<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Handle;

use DOMNode;
use DOMNodeList;
use Exception;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;

class CCDAMetaParticipant extends CStoredObject
{
    /** @var string */
    public const TYPE_AUTHOR = 'author';
    /** @var string */
    public const TYPE_PARTICIPANT = 'participant';
    /** @var string */
    public const TYPE_RESPONSIBLE = 'responsible';
    /** @var string */
    public const TYPE_AUTHENTICATOR = 'authenticator';
    /** @var string */
    public const TYPE_LEGAL_AUTHENTICATOR = 'legalAuthenticator';
    /** @var string */
    public const TYPE_CUSTODIAN = 'custodian';

    /** @var string[] */
    public const TYPES = [
        self::TYPE_AUTHOR,
        self::TYPE_PARTICIPANT,
        self::TYPE_RESPONSIBLE,
        self::TYPE_AUTHENTICATOR,
        self::TYPE_LEGAL_AUTHENTICATOR,
        self::TYPE_CUSTODIAN,
    ];

    /** @var string[] */
    public const AVAILABLE_TARGETS = [
        'CMedecin',
        'CExercicePlace',
        'CPatient',
        'CObservationResult',
        'CObservationResultExamen',
        'CObservationResultBattery',
        'CObservationResultIsolat',
    ];

    // DB Table key
    /** @var int */
    public $cda_meta_participant_id;

    /** @var string */
    public $created_datetime;
    /** @var int */
    public $cda_meta_id;
    /** @var string */
    public $type;
    /** @var int */
    public $target_id;
    /** @var string */
    public $target_class;
    /** @var string */
    public $functionCode;
    /** @var string */
    public $functionCodeSystem;
    /** @var string */
    public $assignedAuthorId;
    /** @var string */
    public $assignedAuthorIdSystem;
    /** @var string */
    public $data;
    /** @var string */
    public $data_hash;

    /** @var CCDAMeta */
    public $_ref_cda_meta;
    /** @var CMedecin|CExercicePlace|CPatient */
    public $_ref_target_participant;

    /**
     * @inheritDoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'cda_meta_participant';
        $spec->key   = 'cda_meta_participant_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $list_type = implode('|', self::TYPES);
        $list_target = implode('|', self::AVAILABLE_TARGETS);

        $props['created_datetime']       = "dateTime notNull";
        $props["cda_meta_id"]            = "ref notNull class|CCDAMeta back|cda_meta_participant";
        $props["type"]                   = "enum list|$list_type";
        $props["target_id"]              = "ref class|CMbObject meta|target_class back|cda_target_participant";
        $props["target_class"]           = "enum list|$list_target";
        $props["functionCode"]           = "str";
        $props["functionCodeSystem"]     = "str";
        $props["assignedAuthorId"]       = "str";
        $props["assignedAuthorIdSystem"] = "str";
        $props["data"]                   = "text";
        $props["data_hash"]              = "str";

        return $props;
    }

    /**
     * Load metadata
     *
     * @return CCDAMeta|CStoredObject
     */
    public function loadRefCDAMeta(): CCDAMeta
    {
        return $this->_ref_cda_meta = $this->loadFwdRef("cda_meta_id", true);
    }

    /**
     * Set object
     *
     * @param CMedecin|CExercicePlace|CPatient|CStoredObject $target Target object
     *
     * @return void
     */
    public function setTargetParticipant(CStoredObject $target): void
    {
        $this->target_class = $target->_class;
        $this->target_id    = $target->_id;
    }

    /**
     * Load metadata
     *
     * @return CMedecin|CExercicePlace|CPatient|CStoredObject
     */
    public function loadRefTargetParticipant(): CStoredObject
    {
        $this->_ref_target_participant = new $this->target_class();
        $this->_ref_target_participant->load($this->target_id);

        return $this->_ref_target_participant;
    }

    /**
     * Get metadata participant JSON
     *
     * @return string
     */
    public function getDataJSON(): string
    {
        return $this->data;
    }

    /**
     * Handle CDA meta participant
     *
     * @param CCDAHandle          $cda_handle
     * @param string              $participant_type
     * @param DOMNodeList|DOMNode $nodes
     *
     * @return void
     * @throws Exception
     */
    public function handle(CCDAHandle $cda_handle, string $participant_type, $nodes = null): void
    {
        if (!$nodes || ($nodes instanceof DOMNodeList && $nodes->length === 0)) {
            return;
        }

        $this->cda_meta_id = $cda_handle->getMeta()->_id;
        $this->type        = $participant_type;
        $cda_handle->handleMetaUnStructuredData($this, $nodes);

        $this->handleMetaStructuredData($cda_handle, $participant_type, $nodes);

        $report = $cda_handle->getCDADomDocument()->getReport();
        if ($msg = $this->store()) {
            $report->addItemFailed($this, $msg);
        }

        $report->addItemsStored($this);
    }

    /**
     * Store structured data
     *
     * @param CCDAHandle               $cda_handle
     * @param string                   $participant_type
     * @param DOMNodeList|DOMNode|null $nodes
     *
     * @return void
     */
    private function handleMetaStructuredData(CCDAHandle $cda_handle, string $participant_type, $nodes = null): void
    {
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        if (!$this->_id) {
            $this->created_datetime = "now";
        }

        if ($msg = parent::store()) {
            return $msg;
        }

        return null;
    }
}
