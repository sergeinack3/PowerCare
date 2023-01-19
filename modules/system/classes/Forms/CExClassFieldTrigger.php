<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CMbObject;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;

/**
 * Field Trigger class
 *
 * Définit le formulaire à déclencher lors du choix d'un élément d'une liste à choix unique ou multiple
 */
class CExClassFieldTrigger extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    public $ex_class_field_trigger_id;

    public $ex_class_field_id;
    public $ex_class_triggered_id;
    public $trigger_value;

    /** @var CExClassField */
    public $_ref_ex_class_field;

    /** @var CExClassField */
    public $_ref_ex_class_triggered;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                                = parent::getSpec();
        $spec->table                         = "ex_class_field_trigger";
        $spec->key                           = "ex_class_field_trigger_id";
        $spec->uniques["ex_class_triggered"] = ["ex_class_field_id", "trigger_value"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                          = parent::getProps();
        $props["ex_class_field_id"]     = "ref notNull class|CExClassField cascade back|ex_triggers";
        $props["ex_class_triggered_id"] = "ref notNull class|CExClass cascade back|ex_triggers";
        $props["trigger_value"]         = "str notNull";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function loadView()
    {
        parent::loadView();
        $this->loadRefExClassField();
        $this->loadRefExClassTriggered();
        $this->_view = $this->_ref_ex_class_field->_view . " > " . $this->_ref_ex_class_triggered->_view;
    }

    /**
     * @param bool $cache
     *
     * @return CExClassField
     */
    function loadRefExClassField($cache = true)
    {
        return $this->_ref_ex_class_field = $this->loadFwdRef("ex_class_field_id", $cache);
    }

    /**
     * @param bool $cache
     *
     * @return CExClass
     */
    function loadRefExClassTriggered($cache = true)
    {
        return $this->_ref_ex_class_triggered = $this->loadFwdRef("ex_class_triggered_id", $cache);
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadRefExClassField($cache);
    }
}
