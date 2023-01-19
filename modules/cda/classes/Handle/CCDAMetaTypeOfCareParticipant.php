<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Handle;

use Ox\Core\CStoredObject;
use Ox\Mediboard\Patients\CMedecin;

class CCDAMetaTypeOfCareParticipant extends CStoredObject
{
    // DB Table key
    /** @var int */
    public $cda_meta_type_of_care_participant_id;

    /** @var int */
    public $medecin_id;
    /** @var int */
    public $cda_meta_type_of_care_id;

    /** @var CCDAMetaTypeOfCare */
    public $_ref_cda_meta_type_of_care;
    /** @var CMedecin */
    public $_ref_medecin;

    /**
     * @inheritDoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'cda_meta_type_of_care_participant';
        $spec->key   = 'cda_meta_type_of_care_participant_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["medecin_id"]               = "ref notNull class|CMedecin back|cda_meta_order";
        $props["cda_meta_type_of_care_id"] = "ref notNull class|CCDAMetaTypeOfCare back|cda_meta_tofc_participant";

        return $props;
    }

    /**
     * Load medecin
     *
     * @return CMedecin|CStoredObject
     */
    public function loadRefCDAMeta(): CMedecin
    {
        return $this->_ref_medecin = $this->loadFwdRef("medecin_id", true);
    }

    /**
     * Load metadata type of care
     *
     * @return CCDAMetaTypeOfCare|CStoredObject
     */
    public function loadRefCDAMetaTypeOfCare(): CCDAMetaTypeOfCare
    {
        return $this->_ref_cda_meta_type_of_care = $this->loadFwdRef("cda_meta_type_of_care_id", true);
    }
}
