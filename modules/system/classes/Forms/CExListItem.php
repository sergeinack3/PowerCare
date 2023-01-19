<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CMbObject;

/**
 * ListItem class
 *
 * Element de liste, peut être liée soit à un champ (obsolète), un concept (obsolète) ou un CExList (recommandé)
 *
 * L'association à un champ ou un concept est fortement déconseillé, et est désativable dans le Configurer du module
 * forms
 */
class CExListItem extends CMbObject implements FormComponentInterface
{
    public $ex_list_item_id;

    public $list_id;
    public $concept_id;
    public $field_id;

    public $code;
    public $name;

    public $_ref_list;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                  = parent::getSpec();
        $spec->table           = "ex_list_item";
        $spec->key             = "ex_list_item_id";
        $spec->uniques["name"] = ["list_id", "name"];
        $spec->xor["owner"]    = ["list_id", "concept_id", "field_id"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props               = parent::getProps();
        $props["list_id"]    = "ref class|CExList cascade back|list_items";
        $props["concept_id"] = "ref class|CExConcept cascade back|list_items";
        $props["field_id"]   = "ref class|CExClassField cascade back|list_items";
        $props["code"]       = "str maxLength|20";
        $props["name"]       = "str notNull";

        return $props;
    }

    /**
     * Load ref list
     *
     * @param bool $cache Use cache
     *
     * @return CExList
     */
    function loadRefList($cache = true)
    {
        return $this->_ref_list = $this->loadFwdRef("list_id", $cache);
    }

    public function getPerm($permType)
    {
        if (CExClass::inHermeticMode(true)) {
            $list = $this->loadRefList(true);

            if ($list && $list->_id) {
                return $list->getPerm($permType);
            }
        }

        return parent::getPerm($permType);
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $list        = $this->loadRefList();
        $this->_view = "{$list->_view} / $this->name";

        if ($this->code != null) {
            $this->_view .= " [$this->code]";
        }
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        $is_new = !$this->_id;

        // Free input
        if ($this->name || $this->fieldModified('name')) {
            $this->name = strip_tags($this->name);
        }

        if ($msg = parent::store()) {
            return $msg;
        }

        if ($is_new || $this->fieldModified("name")) {
            CExObject::clearLocales();
        }

        return null;
    }
}
