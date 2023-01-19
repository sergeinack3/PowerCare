<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\BloodSalvage;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Qualite\CEiItem;

/**
 * CTypeEi
 */
class CTypeEi extends CMbObject
{
    /** @var int */
    public $type_ei_id;

    //DB Fields
    /** @var string */
    public $name;
    /** @var string */
    public $concerne;
    /** @var string */
    public $desc;
    /** @var string */
    public $type_signalement;
    /** @var string */
    public $evenements;

    /** @var array */
    public $_ref_evenement;

    /** @var CEiItem[] */
    public $_ref_items;

    /**
     * @see parent::getSpec()
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'type_ei';
        $spec->key   = 'type_ei_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props                     = parent::getProps();
        $props["name"]             = "str notNull maxLength|30";
        $props["concerne"]         = "enum notNull list|pat|vis|pers|med|mat";
        $props["desc"]             = "text";
        $props["type_signalement"] = "enum notNull list|inc|ris";
        $props["evenements"]       = "str notNull maxLength|255";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();
        $this->_view = $this->name;

        if ($this->evenements) {
            $this->_ref_evenement = explode("|", $this->evenements);
        }
    }

    /**
     * Chargement des items
     *
     * @return CEiItem[]
     * @throws Exception
     */
    public function loadRefItems(): array
    {
        $this->_ref_items = [];

        foreach ($this->_ref_evenement as $evenement) {
            $ext_item = new CEiItem();
            $ext_item->load($evenement);
            $this->_ref_items[] = $ext_item;
        }

        return $this->_ref_items;
    }
}
