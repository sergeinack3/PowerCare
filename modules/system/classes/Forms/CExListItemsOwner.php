<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CValue;
use Ox\Core\FieldSpecs\CEnumSpec;

/**
 * List Items Owner class
 *
 * Classe mère des champs, concepts et listes
 */
class CExListItemsOwner extends CMbObject implements FormComponentInterface
{
    /** @var CExListItem[] */
    public $_ref_items;

    /** @var bool */
    static $_order_list_items = true;

    /**
     * @return CExListItem[]
     */
    function loadRefItems()
    {
        $items = $this->loadBackRefs("list_items");
        self::naturalSort($items, ["code"]);

        return $this->_ref_items = $items;
    }

    /**
     * @return string
     */
    function getBackRefField()
    {
        $map = [
            "CExList"       => "list_id",
            "CExConcept"    => "concept_id",
            "CExClassField" => "field_id",
        ];

        return CValue::read($map, $this->_class);
    }

    /**
     * @return integer[]
     */
    function getItemsKeys()
    {
        static $cache = [];

        if (isset($cache[$this->_guid])) {
            return $cache[$this->_guid];
        }

        $item  = new CExListItem;
        $where = [
            $this->getBackRefField() => "= '$this->_id'",
        ];
        // TODO ne pas ordonner par nom dans certains cas

        // Natural sort, sort of ...
        $orderby = (self::$_order_list_items ? "LPAD(code, 20, '0'), name" : null);

        return $cache[$this->_guid] = $item->loadIds($where, $orderby, null, null, null, false);
    }

    /**
     * @return string[]
     */
    function getItemNames()
    {
        $item  = new CExListItem;
        $where = [
            $this->getBackRefField() => "= '$this->_id'",
        ];

        $request = new CRequest(false);
        $request->addWhere($where);
        $request->addTable($item->_spec->table);
        $request->addOrder("LPAD(code, 20, '0'), name");
        $request->addSelect([
                                $item->_spec->key,
                                "name",
                            ]);

        $ds = $item->_spec->ds;

        return $ds->loadHashList($request->makeSelect());
    }

    /**
     * @return CExListItemsOwner
     */
    function getRealListOwner()
    {
        return $this;
    }

    /**
     * @param CEnumSpec $spec
     *
     * @return CEnumSpec
     */
    function updateEnumSpec(CEnumSpec $spec)
    {
        $items = $this->getItemNames();
        $empty = empty($spec->_locales);

        foreach ($items as $_id => $_item) {
            if (!$empty && !isset($spec->_locales[$_id])) {
                continue;
            }

            $spec->_locales[$_id] = $_item;
        }

        $spec->_list = array_keys($spec->_locales);

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function loadView()
    {
        parent::loadView();
        $this->loadRefItems();
    }
}
