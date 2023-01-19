<?php

/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;

/**
 * Categorie Astreinte - Label and color
 */
class CCategorieAstreinte extends CMbObject
{
    /** @var int Primary key */
    public $oncall_category_id;

    /** @var string */
    public $name;

    /** @var string */
    public $color;

    /** @var int */
    public $group_id;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "oncall_category";
        $spec->key   = "oncall_category_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props["name"]     = "str notNull";
        $props["color"]    = "color";
        $props["group_id"] = "ref notNull class|CGroups back|categories_astreinte";

        return $props;
    }

    /**
     * @return string[] - the key is the id and the value is the name of the category
     * @throws Exception
     */
    public static function getPrefCategories(): array
    {
        $categories = (new CCategorieAstreinte())->loadList();
        $cat_names  = [];
        foreach ($categories as $_category) {
            $cat_names[$_category->_id] = $_category->name;
        }

        return $cat_names;
    }

    /**
     * Load Categories for current group and with no group_id
     *
     * @throws Exception
     */
    public static function loadListCategories(): array
    {
        $category = new CCategorieAstreinte();

        return $category->loadGroupList() + $category->loadList(["group_id is null"]);
    }

    /**
     * Method to get the name of a category using the id (initially made for user prefs)
     *
     * @param int $id
     *
     * @return string
     * @throws Exception
     */
    public static function getName(int $id): ?string
    {
        return (CCategorieAstreinte::findOrFail($id))->name;
    }
}
