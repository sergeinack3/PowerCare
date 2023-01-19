<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;

/**
 * Créneau dans une plage de consultation
 */
class CSlot extends CStoredObject
{
    /** @var int Primary key */
    public $slot_id;

    /** @var int */
    public $plageconsult_id;
    /** @var int */
    public $consultation_id;
    /** @var string */
    public $start;
    /** @var string */
    public $end;
    /** @var bool */
    public $overbooked;
    /** @var string */
    public $status;

    /** @var CConsultation */
    public $_ref_consultation;
    /** @var CPlageconsult */
    public $_ref_plageconsult;

    /** @var string */
    public $_date;
    /** @var string */
    public $_heure;
    /** @var string */
    public $_nb_week;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table    = 'slot';
        $spec->key      = 'slot_id';
        $spec->loggable = false;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props["plageconsult_id"] = "ref notNull class|CPlageconsult back|slots cascade";
        $props["consultation_id"] = "ref class|CConsultation back|slots";
        $props["start"]           = "dateTime notNull";
        $props["end"]             = "dateTime notNull";
        $props["overbooked"]      = "bool default|0";
        $props["status"]          = "enum list|busy|free|busy-unavailable|busy-tentative|entered-in-error default|free";

        return $props;
    }

    /**
     * @throws Exception
     */
    public function loadRefConsultation(): CConsultation
    {
        return $this->_ref_consultation = $this->loadFwdRef("consultation_id", true);
    }

    /**
     * @throws Exception
     */
    public function loadRefPlageconsult(): CPlageconsult
    {
        return $this->_ref_plageconsult = $this->loadFwdRef("plageconsult_id", true);
    }
}
