<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

//CCanDo::checkRead();

use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CTag;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExConcept;
use Ox\Mediboard\System\Forms\CExList;

$tag_id       = CValue::get("tag_id");
$columns      = CValue::get("col");
$keywords     = CValue::get("keywords");
$object_class = CValue::get("object_class");
$insertion    = CValue::get("insertion");
$group_id     = CValue::get("group_id");

$tag = new CTag();

$where = [];
if ($group_id) {
    $where[1000] = "(group_id = '$group_id' OR group_id IS NULL)";
}

/** @var CMbObject $object */

if (strpos($tag_id, "all") === 0) {
    $parts        = explode("-", $tag_id);
    $object_class = $parts[1];

    $object = new $object_class();

    if (CModule::getActive('forms')) {
        if (
            CExClass::inHermeticMode(true)
            && ($object instanceof CExClass || $object instanceof CExConcept || $object instanceof CExList)
        ) {
            $_group_id   = CGroups::loadCurrent()->_id;
            $where[1000] = "group_id = '$_group_id'";
        }
    }

    if (!$keywords) {
        $keywords = "%";
    }

    /** @var CMbObject[] $objects */
    $objects = $object->seek($keywords, $where, 10000, true);
    foreach ($objects as $_object) {
        $_object->loadRefsTagItems();
    }

    $count_children = $object->_totalSeek;
} elseif (strpos($tag_id, "none") === 0) {
    $parts        = explode("-", $tag_id);
    $object_class = $parts[1];

    $tag->object_class = $object_class;
    $object            = new $object_class();

    if (CModule::getActive('forms')) {
        if (
            CExClass::inHermeticMode(true)
            && ($object instanceof CExClass || $object instanceof CExConcept || $object instanceof CExList)
        ) {
            $_group_id   = CGroups::loadCurrent()->_id;
            $where[1000] = "group_id = '$_group_id'";
        }
    }

    $where["tag_item_id"] = "IS NULL";

    $ljoin = [
        "tag_item" => "tag_item.object_id = {$object->_spec->table}.{$object->_spec->key} AND tag_item.object_class = '$object_class'",
    ];

    if (!$keywords) {
        $keywords = "%";
    }

    $objects        = $object->seek($keywords, $where, 10000, true, $ljoin);
    $count_children = $object->_totalSeek;
} else {
    $tag->load($tag_id);
    $count_children = $tag->countChildren();
    $objects        = $tag->getObjects($keywords);
    $object         = new $tag->object_class();

    // filter by group_id
    if (
        $group_id
        || (
            CModule::getActive('forms')
            && (
                CExClass::inHermeticMode(true)
                && (($object instanceof CExClass || $object instanceof CExConcept || $object instanceof CExList))
            )
        )
    ) {
        $group_id = $group_id ?: CGroups::loadCurrent()->_id;
    }

    if ($group_id) {
        foreach ($objects as $_id => $_object) {
            if ($_object->group_id && $_object->group_id != $group_id) {
                unset($objects[$_id]);
            }
        }
    }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("objects", $objects);
$smarty->assign("columns", $columns);
$smarty->assign("insertion", $insertion);
$smarty->assign("count_children", $count_children);
$smarty->assign("tag", $tag);
$smarty->display("inc_list_objects_by_tag.tpl");
