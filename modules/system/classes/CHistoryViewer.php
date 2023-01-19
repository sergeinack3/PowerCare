<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CModelObject;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CRefSpec;

/**
 * Description
 */
class CHistoryViewer implements IShortNameAutoloadable
{
    static $objects = [];


    /**
     * @param array           $tree
     * @param int             $deepness
     * @param CStoredObject[] $instances
     * @param CStoredObject   $object
     *
     * @return array
     */
    static function makeTree(&$tree, $deepness, $object)
    {
        if ($deepness <= 0) {
            return [];
        }

        foreach ($object->getBackProps() as $_propName => $_prop) {
            [$_backclass, $_props] = explode(" ", $_prop);

            /** @var CStoredObject $_backObject */
            $_backObject = self::getInstance($_backclass);

            if ($_backObject && $_backObject->isLoggable()) {
                $subtree = [
                    "back" => [],
                    "fwd"  => [],
                ];
                self::makeTree($subtree, $deepness - 1, $_backObject);

                $tree["back"][$_propName] = [
                    "class"           => $object->_class,
                    "declaring_class" => self::getDeclaringClass($object, $_propName),
                    "subtree"         => $subtree,
                ];
            }
        }

        uksort(
            $tree["back"],
            function ($a, $b) use ($object) {
                $_class_a = CHistoryViewer::getDeclaringClass($object, $a);
                $_class_b = CHistoryViewer::getDeclaringClass($object, $b);

                return strcmp(CAppUI::tr("$_class_a-back-$a"), CAppUI::tr("$_class_b-back-$b"));
            }
        );

        foreach ($object->getSpecs() as $_propName => $_spec) {
            if (!$_spec instanceof CRefSpec || $_propName == $object->_spec->key || $_propName[0] == "_") {
                continue;
            }

            /** @var CStoredObject $_fwdObject */
            $_fwdObject = self::getInstance($_spec->class);

            if ($_fwdObject && $_fwdObject->isLoggable()) {
                $subtree = [
                    "back" => [],
                    "fwd"  => [],
                ];
                self::makeTree($subtree, $deepness - 1, $_fwdObject);

                $tree["fwd"][$_propName] = [
                    "class"   => $object->_class,
                    "subtree" => $subtree,
                ];
            }
        }

        uksort(
            $tree["fwd"],
            function ($a, $b) use ($object) {
                return strcmp(CAppUI::tr("$object->_class-$a"), CAppUI::tr("$object->_class-$b"));
            }
        );
    }

    static function getInstance($class)
    {
        static $instances = [];

        if (!class_exists($class)) {
            return $instances[$class] = false;
        }

        if (!isset($instances[$class])) {
            $instances[$class] = new $class();
        }

        return $instances[$class];
    }

    static function getDeclaringClass($object, $backName)
    {
        static $declaring = [];

        if (isset($declaring["$object->_class-$backName"])) {
            return $declaring["$object->_class-$backName"];
        }

        /** @var CModelObject $parent */
        $parent = self::getInstance(get_parent_class($object));

        $backProps = $parent->getBackProps();

        if (!isset($backProps[$backName])) {
            return $declaring["$object->_class-$backName"] = $object->_class;
        }

        // Avoid going to CModelObject which cannot have backprops and is abstract
        if (get_class($parent) === CStoredObject::class) {
            return $declaring["CStoredObject-$backName"] = "CStoredObject";
        }

        return $declaring["$object->_class-$backName"] = self::getDeclaringClass($parent, $backName);
    }

    static function getObject($class, $id)
    {
        if (!isset(self::$objects[$class][$id])) {
            /** @var CStoredObject $_object */
            $_object = new $class();
            $_object->load($id);

            self::$objects[$class][$id] = $_object->_view;
        }
    }
}
