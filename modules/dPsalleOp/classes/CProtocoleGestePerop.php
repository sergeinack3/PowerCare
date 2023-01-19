<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Protocoles de gestes perop
 */
class CProtocoleGestePerop extends CMbObject
{
    /** @var int */
    public $protocole_geste_perop_id;
    /** @var string */
    public $libelle;
    /** @var string */
    public $description;
    /** @var boolean */
    public $actif;

    // DB References
    /** @var int */
    public $group_id;
    /** @var int */
    public $function_id;
    /** @var int */
    public $user_id;

    /** @var int */
    public $_count_items;

    /** @var CGroups */
    public $_ref_group;
    /** @var CFunctions */
    public $_ref_function;
    /** @var CMediusers */
    public $_ref_user;
    /** @var CProtocoleGestePeropItem[] */
    public $_ref_protocole_geste_items;
    /** @var CGestePerop[] */
    public $_ref_gestes_perop;
    /** @var array */
    public $_ref_protocole_geste_item_by_categories;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec               = parent::getSpec();
        $spec->table        = 'protocole_geste_perop';
        $spec->key          = 'protocole_geste_perop_id';
        $spec->xor["owner"] = ["group_id", "function_id", "user_id"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props                = parent::getProps();
        $props["group_id"]    = "ref class|CGroups back|protocoles_geste_perop";
        $props["function_id"] = "ref class|CFunctions back|protocoles_geste_perop";
        $props["user_id"]     = "ref class|CMediusers back|protocoles_geste_perop";
        $props["libelle"]     = "str notNull";
        $props["description"] = "text helped";
        $props["actif"]       = "bool default|1";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = $this->libelle;
    }

    /**
     * Load the group
     *
     * @return CGroups
     * @throws Exception
     */
    public function loadRefGroup(): CGroups
    {
        return $this->_ref_group = $this->loadFwdRef("group_id", true);
    }

    /**
     * Load the function
     *
     * @return CFunctions
     * @throws Exception
     */
    public function loadRefFunction(): CFunctions
    {
        return $this->_ref_function = $this->loadFwdRef("function_id", true);
    }

    /**
     * Load the user
     *
     * @return CMediusers
     * @throws Exception
     */
    public function loadRefUser(): CMediusers
    {
        return $this->_ref_user = $this->loadFwdRef("user_id", true);
    }

    /**
     * Load the perop gesture protocol items
     *
     * @param array $where
     *
     * @return CProtocoleGestePeropItem[]
     * @throws Exception
     */
    public function loadRefsProtocoleGestePeropItems(array $where = []): array
    {
        $order = "rank ASC";

        return $this->_ref_protocole_geste_items = $this->loadBackRefs(
            "protocole_geste_items",
            $order,
            null,
            null,
            null,
            null,
            "",
            $where
        );
    }

    /**
     * Load the perop gesture protocol items by category
     *
     * @return int
     * @throws Exception
     */
    public function loadRefProtocoleGestePeropItemCategories(): int
    {
        $protocole_items = $this->loadRefsProtocoleGestePeropItems();

        $protocole_items_by_cat = [];
        $total                  = 0;
        $precisions             = [];

        foreach ($protocole_items as $_item) {
            $context = $_item->loadRefContext();

            $precisions = $context->loadRefPrecisions();
            $category   = $context->loadRefCategory();

            foreach ($precisions as $_precision) {
                $_precision->loadRefValeurs();
            }

            $context->_datetime = CMbDT::dateTime();
            $context_view       = $category->_id ? $category->_view : CAppUI::tr(
                "CAnesthPeropCategorie.none-court"
            );

            $protocole_items_by_cat[$context_view][$context->_id]["gestes"][$context->_id] = $context;
            $protocole_items_by_cat[$context_view][$context->_id]["rank"]                  = $_item->rank;
            $protocole_items_by_cat[$context_view][$context->_id]["checked"]               = $_item->checked;
            $protocole_items_by_cat[$context_view][$context->_id]["isCategory"]            = 0;
            $protocole_items_by_cat[$context_view][$context->_id]["item"]                  = $_item;
            $protocole_items_by_cat[$context_view][$context->_id]["actif"]                 = $context->actif;
            $total++;
        }

        $this->_ref_protocole_geste_item_by_categories = $protocole_items_by_cat;

        return $total;
    }

    /**
     * Load the perop gestures
     *
     * @return CGestePerop[]
     * @throws Exception
     */
    public function loadRefsGestePerop(): array
    {
        $protocole_gestes_item = $this->loadBackRefs(
            "protocole_geste_items",
            null,
            null,
            null,
            null,
            null,
            "",
            ["object_class" => " = 'CGestePerop'"]
        );
        $gestes_perop          = null;

        foreach ($protocole_gestes_item as $_item) {
            /** @var CProtocoleGestePeropItem $_item */
            $geste                     = CGestePerop::find($_item->object_id);
            $gestes_perop[$geste->_id] = $geste;
        }

        return $this->_ref_gestes_perop = $gestes_perop;
    }
}
