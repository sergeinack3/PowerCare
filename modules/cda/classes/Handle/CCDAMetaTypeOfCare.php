<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Handle;

use DOMNode;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Eai\Tools\CConsultationTrait;
use Ox\Interop\Eai\Tools\CSejourTrait;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Mediboard\PlanningOp\CSejour;

class CCDAMetaTypeOfCare extends CStoredObject
{
    use CSejourTrait;
    use CConsultationTrait;

    // DB Table key
    /** @var int */
    public $cda_meta_type_of_care_id;

    /** @var string */
    public $created_datetime;
    /** @var int */
    public $cda_meta_id;
    /** @var int */
    public $target_id;
    /** @var string */
    public $target_class;
    /** @var string */
    public $encounterId;
    /** @var string */
    public $encounterIdCodeSystem;
    /** @var string */
    public $encounterCode;
    /** @var string */
    public $encounterCodeCodeSystem;
    /** @var string */
    public $encounterStart;
    /** @var string */
    public $encounterEnd;
    /** @var int */
    public $encounterHealthCareFacility_id;
    /** @var string */
    public $data;
    /** @var string */
    public $data_hash;

    /** @var CCDAMeta */
    public $_ref_cda_meta;

    /** @var CExercicePlace */
    public $_ref_healthcare_facility;

    /** @var CCDAMetaTypeOfCareParticipant[] */
    public $_ref_participants;

    /** @var CSejour|CConsultation */
    public $_ref_target_object;

    /**
     * @inheritDoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'cda_meta_type_of_care';
        $spec->key   = 'cda_meta_type_of_care_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props['created_datetime']               = "dateTime notNull";
        $props["cda_meta_id"]                    = "ref notNull class|CCDAMeta back|cda_meta_type_of_care";
        $props["target_id"]                      = "ref class|CMbObject meta|target_class back|cda_target_object";
        $props["target_class"]                   = "enum list|CSejour|CConsultation";
        $props["encounterId"]                    = "str";
        $props["encounterIdCodeSystem"]          = "str";
        $props["encounterCode"]                  = "str";
        $props["encounterCodeCodeSystem"]        = "str";
        $props["encounterStart"]                 = "dateTime";
        $props["encounterEnd"]                   = "dateTime";
        $props["encounterHealthCareFacility_id"] = "ref class|CExercicePlace back|cda_meta_healthCare_facility";
        $props["data"]                           = "text";
        $props["data_hash"]                      = "str";

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
     * Load metadata
     *
     * @return CExercicePlace|CStoredObject
     */
    public function loadRefHealthCareFacility(): CExercicePlace
    {
        return $this->_ref_healthcare_facility = $this->loadFwdRef("encounterHealthCareFacility_id", true);
    }

    /**
     * Load participants
     *
     * @return CCDAMetaTypeOfCareParticipant
     */
    public function loadRefsParticipants(): array
    {
        return $this->_ref_participants = $this->loadBackRefs("cda_meta_tofc_participant");
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
     * Handle type of care
     *
     * @param CCDAHandle $cda_handle
     *
     * @return void
     */
    public function handle(CCDAHandle $cda_handle): void
    {
        if (!$node = $cda_handle->getCDADomDocument()->getComponentOf()) {
            return;
        }

        $this->cda_meta_id = $cda_handle->getMeta()->_id;
        $cda_handle->handleMetaUnStructuredData($this, $node);

        $this->handleMetaStructuredData($cda_handle, $node);

        $report = $cda_handle->getCDADomDocument()->getReport();
        if ($msg = $this->store()) {
            $report->addItemFailed($this, $msg);
        }

        $report->addItemsStored($this);

        $cda_handle->getMeta()->_current_type_of_care = $this;
    }

    /**
     * Store structured data
     *
     * @param CCDAHandle $cda_handle
     * @param DOMNode    $node
     *
     * @return void
     */
    private function handleMetaStructuredData(CCDAHandle $cda_handle, DOMNode $node): void
    {
        $encompassingEncounter = $cda_handle->getNode('encompassingEncounter', $node);

        $this->encounterId           = $cda_handle->getExtensionAttributNode('id', $encompassingEncounter);
        $this->encounterIdCodeSystem = $cda_handle->getRootAttributNode('id', $encompassingEncounter);

        $effectiveTime = $cda_handle->getNode('effectiveTime', $encompassingEncounter);
        if ($encounterStart = $cda_handle->getLowAttributNode($effectiveTime)) {
            $this->encounterStart = CMbDT::dateTime($encounterStart);
        }
        if ($encounterEnd = $cda_handle->getHighAttributNode($effectiveTime)) {
            $this->encounterEnd = CMbDT::dateTime($encounterEnd);
        }

        $codable = null;

        // Si on est sur le NDA
        $oid_master_sejour = CDomain::getMasterDomainSejour()->OID;
        if ($oid_master_sejour && $oid_master_sejour === $this->encounterIdCodeSystem) {
            $codable = $this->loadSejourFromNDA(
                $this->encounterId,
                $cda_handle->getCDADomDocument()->getSender()->_tag_sejour
            );
        }

        if (!$codable && ($this->encounterStart || $this->encounterEnd)) {
            $codable = $this->searchSejour(
                $this->encounterStart ?: $this->encounterEnd,
                $cda_handle->getPatient(),
                $cda_handle->getCDADomDocument()->getSender()->group_id
            );

            if (!$codable) {
                $codable = $this->searchConsultation(
                    $this->encounterStart ?: $this->encounterEnd,
                    $cda_handle->getPatient()
                );
            }
        }

        // Dans le cas où l'on n'a pas de contexte
        if (!$codable) {
            return;
        }

        $this->target_class = $codable->_class;
        $this->target_id    = $codable->_id;

        $cda_handle->setTargetObject($codable);
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
     * Load target
     *
     * @return CSejour|CConsultation|CStoredObject
     */
    public
    function loadRefTargetObject(): CStoredObject
    {
        $this->_ref_target_object = new $this->target_class();
        $this->_ref_target_object->load($this->target_id);

        return $this->_ref_target_object;
    }
}
