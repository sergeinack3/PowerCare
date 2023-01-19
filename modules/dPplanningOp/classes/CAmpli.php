<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Ampli
 */
class CAmpli extends CMbObject
{
    /** @var int Primary key */
    public $ampli_id;

    /** @var string */
    public $libelle;

    /** @var int */
    public $group_id;

    /** @var int */
    public $actif;

    /** @var string */
    public $unite_rayons_x;

    /** @var string */
    public $unite_pds;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'ampli';
        $spec->key   = 'ampli_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                   = parent::getProps();
        $props['libelle']        = 'str';
        $props['group_id']       = 'ref class|CGroups back|amplis';
        $props['actif']          = 'bool default|1';
        $props['unite_rayons_x'] = 'enum list|mA|mGy|cGy.cm_carre|mGy.m_carre|mGy.cm_carre|Gy.cm_carre default|mA';
        $props["unite_pds"]      = "enum list|uGycm_carre|mGycm_carre|Gycm_carre|cGycm_carre default|mGycm_carre";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields() {
        parent::updateFormFields();
        $this->_view = $this->libelle;
    }

    /**
     * @inheritDoc
     */
    public function store()
    {
        if (!$this->_id) {
            $this->group_id = CGroups::loadCurrent()->_id;
        }

        return parent::store();
    }
}
