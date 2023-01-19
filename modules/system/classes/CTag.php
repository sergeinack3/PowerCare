<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CColorSpec;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\Forms\CExClass;
use Symfony\Component\Routing\RouterInterface;

class CTag extends CMbObject
{
    /** @var string */
    public const RESOURCE_TYPE = 'tag';

    protected const ELLIPSIS_DEPTH = 3;

    /** @var string */
    public $tag_id;
    /** @var string */
    public $parent_id;
    /** @var string */
    public $object_class;
    /** @var string */
    public $name;
    /** @var string */
    public $color;
    /** @var string */
    public $_font_color;
    /** @var self */
    public $_ref_parent;
    /** @var CTagItem[] */
    public $_ref_items;
    /** @var int */
    public $_nb_items;
    /** @var CTag[] */
    public $_ref_children;
    /** @var int */
    public $_nb_children;
    /** @var array */
    public $_tree;
    /** @var int */
    public $_deepness;

    /**
     * @inheritDoc
     */
    public function getApiLink(RouterInterface $router): string
    {
        return $router->generate(
            'system_api_tag',
            [
                'tag_id' => $this->tag_id,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec                  = parent::getSpec();
        $spec->table           = "tag";
        $spec->key             = "tag_id";
        $spec->uniques["name"] = ["parent_id", "object_class", "name"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                 = parent::getProps();
        $props["parent_id"]    = "ref class|CTag autocomplete|name dependsOn|object_class back|children fieldset|default";
        $props["object_class"] = "str class";
        $props["name"]         = "str notNull seekable fieldset|default";
        $props["color"]        = "color fieldset|default";
        $props["_nb_items"]    = "num";
        $props['_font_color']  = 'color';

        return $props;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $parent      = $this->loadRefParent();
        $this->_view = ($parent->_id ? "$parent->_view &raquo; " : "") . $this->name;

        $this->_font_color = "000000";

        if ($this->color && (CColorSpec::get_text_color($this->color) < 130)) {
            $this->_font_color = "ffffff";
        }

        $this->color       = ($this->color) ?: $parent->color;
        $this->_font_color = ($this->_font_color) ?: $parent->_font_color;

        $tree = [["name" => $this->name, "color" => $this->color]];

        $this->_tree = $parent->_id ? array_merge(
            $parent->_tree,
            $tree
        ) : $tree;
    }

    /**
     * @inheritdoc
     */
    public function getPerm($permType)
    {
        $class = $this->object_class;

        if ($class) {
            $context = new $class();

            return $context->getPerm($permType);
        } else {
            return parent::getPerm($permType);
        }
    }

    /**
     * Load tag items
     *
     * @return CTagItem[]
     * @throws Exception
     */
    public function loadRefItems(): ?array
    {
        return $this->_ref_items = $this->loadBackRefs("items");
    }

    /**
     * Count items related to this
     *
     * @return int
     * @throws Exception
     */
    public function countRefItems(): int
    {
        return $this->_nb_items = $this->countBackRefs("items");
    }

    /**
     * Load children
     *
     * @return self[]
     * @throws Exception
     */
    public function loadRefChildren(): ?array
    {
        return $this->_ref_children = $this->loadBackRefs("children");
    }

    /**
     * Count children tags
     *
     * @return int
     * @throws Exception
     */
    public function countChildren(): int
    {
        return $this->_nb_children = $this->countBackRefs("children");
    }

    /**
     * Load parent tag
     *
     * @return self
     * @throws Exception
     */
    public function loadRefParent(): ?self
    {
        return $this->_ref_parent = $this->loadFwdRef("parent_id", true);
    }

    /**
     * @inheritdoc
     */
    public function check(): ?string
    {
        if ($msg = parent::check()) {
            return $msg;
        }

        if (!$this->parent_id) {
            return null;
        }

        $tag = $this;
        while ($tag->parent_id) {
            $parent = $tag->loadRefParent();
            if ($parent->_id == $this->_id) {
                return "Récursivité détectée, un des ancêtres du tag est lui-même";
            }
            $tag = $parent;
        }

        return null;
    }

    /**
     * Get objects matching the keywords having the current tag
     *
     * @param string|null $keywords Keywords
     *
     * @return CMbObject[]
     * @throws Exception
     */
    public function getObjects(?string $keywords = ""): ?array
    {
        if (!$keywords) {
            $items = $this->loadRefItems();
        } else {
            $where = [
                "tag_id"       => "= '$this->_id'",
                "object_class" => "= 'object_class'",
            ];
            $item  = new CTagItem();
            $items = $item->seek($keywords, $where, 10000);
        }

        CMbArray::invoke($items, "loadTargetObject");

        return CMbArray::pluck($items, "_ref_object");
    }

    /**
     * @inheritdoc
     */
    public function getAutocompleteList(
        $keywords,
        $where = null,
        $limit = null,
        $ljoin = null,
        $order = null,
        $group_by = null,
        $strict = true
    ) {
        $list = [];

        if ($keywords === "%" || $keywords == "") {
            $tree = self::getTree($this->object_class);
            self::appendItemsRecursive($list, $tree);

            foreach ($list as $_tag) {
                $_tag->_view = $_tag->name;
            }
        } else {
            $list = parent::getAutocompleteList($keywords, $where, $limit, $ljoin, $order, $group_by, $strict);
        }

        return $list;
    }

    /**
     * @param self[] $list
     * @param array  $tree
     */
    private static function appendItemsRecursive(array &$list, array $tree): void
    {
        if ($tree["parent"]) {
            $list[] = $tree["parent"];
        }

        foreach ($tree["children"] as $_child) {
            self::appendItemsRecursive($list, $_child);
        }
    }

    /**
     * @param int $d
     *
     * @return int
     * @throws Exception
     */
    public function getDeepness(int $d = 0): int
    {
        if ($this->parent_id) {
            $d++;
            $d = $this->loadRefParent()->getDeepness($d);
        }

        return $this->_deepness = $d;
    }

    /**
     * @param string $object_class
     * @param CTag   $parent
     *
     * @return array
     * @throws Exception
     */
    public static function getTree(string $object_class, ?CTag $parent = null): array
    {
        $tag   = new self();
        $where = [
            "object_class" => "= '$object_class'",
            "parent_id"    => (($parent && $parent->_id) ? "= '$parent->_id'" : "IS NULL"),
        ];

        $tree = [
            "parent"   => $parent,
            "children" => [],
        ];

        /** @var self[] $tags */
        $tags = $tag->loadList($where, "name");

        if (CModule::getActive('forms')) {
            if (
                in_array($object_class, ['CExClass', 'CExConcept', 'CExList'])
                && CExClass::inHermeticMode(true)
            ) {
                $empty_tags = [];

                $_dummy_object = new $object_class();
                $_group_id     = CGroups::loadCurrent()->_id;

                foreach ($tags as $_k => $_tag) {
                    $empty_tags[$_tag->_id] = false;

                    $_object_ids = self::searchLinkedObjectsId($_dummy_object, [$_tag->_id]);

                    if (!$object_class::checkByGroupId($_object_ids, $_group_id)) {
                        unset($tags[$_k]);
                    }
                }
            }
        }

        foreach ($tags as $_tag) {
            $_tag->getDeepness();
            $tree["children"][] = self::getTree($object_class, $_tag);
        }

        return $tree;
    }

    /**
     * Returns an array of children ids from a parent tag
     *
     * @param CTag  $tag
     * @param array $ids
     *
     * @return array
     * @throws Exception
     */
    public static function getRecursiveIds(CTag $tag, array &$ids = []): array
    {
        $ids[] = $tag->_id;
        foreach ($tag->loadRefChildren() as $child) {
            array_merge($ids, self::getRecursiveIds($child, $ids));
        }

        return array_unique($ids);
    }

    /**
     * Returns an array of children tag ids from an array of tag ids
     *
     * @param array $tag_ids
     *
     * @return array
     * @throws Exception
     */
    public static function getMultipleRecursiveIds(array $tag_ids): array
    {
        $result = [];
        foreach ($tag_ids as $_tag_id) {
            $result = array_merge($result, CTag::getRecursiveIds(CTag::findOrFail($_tag_id)));
        }

        return $result;
    }

    /**
     * @param string $tags
     *
     * @return array
     */
    public static function splitIdsToArray(string $tags): array
    {
        $result  = [
            'include' => [],
            'exclude' => [],
        ];
        $tag_ids = array_unique(explode("|", $tags));
        foreach ($tag_ids as $_tag_id) {
            if (substr($_tag_id, 0, 1) == "!") {
                $result['exclude'][] = substr($_tag_id, 1);
            } else {
                $result['include'][] = $_tag_id;
            }
        }

        return $result;
    }

    /**
     * @param string $object_class
     * @param array  $tag_ids
     * @param bool   $recursive
     *
     * @return array
     * @throws Exception
     */
    public static function getWhereClause(
        string $object_class,
        array  $tag_ids,
        bool   $recursive = false
    ): array {
        $ds = (new self())->getDS();

        if ($recursive === true) {
            $tag_ids = self::getMultipleRecursiveIds($tag_ids);
        }

        return [
            "tag_item.object_class" => $ds->prepare("= '" . $object_class . "'"),
            "tag_item.tag_id"       => $ds->prepareIn($tag_ids),
        ];
    }

    /**
     * @param CStoredObject $object
     * @param array         $tag_ids
     * @param bool          $recursive
     *
     * @return array
     * @throws Exception
     */
    public static function searchLinkedObjectsId(
        CStoredObject $object,
        array         $tag_ids,
        bool          $recursive = false
    ): array {
        $key   = $object->getPrimaryKey();
        $table = $object->getSpec()->table;

        $where = self::getWhereClause($object->_class, $tag_ids, $recursive);

        $ljoin = [
            "tag_item" => "`tag_item`.`object_id` = `$table`.`$key`",
        ];

        $request = new CRequest();
        $request->addSelect("`$key`");
        $request->addTable("`$table`");
        $request->addLJoin($ljoin);
        $request->addWhere($where);
        $request->addGroup("`$key`");

        $objects = (new $object())->getDS()->loadList($request->makeSelect());

        return CMbArray::pluck($objects, $key);
    }

    /**
     * @return bool
     */
    public function hasEllipsis(): bool
    {
        return count($this->_tree) >= self::ELLIPSIS_DEPTH;
    }
}
