<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CStoredObject;
use Ox\Core\Mutex\CMbMutex;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Object-Class link class
 */
class CObjectClass extends CStoredObject
{
    /** @var integer Primary key */
    public $object_class_id;

    /** @var string Classe name */
    public $object_class;


    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->loggable = false;
        $spec->table    = 'object_class';
        $spec->key      = 'object_class_id';

        $spec->uniques['object_class'] = ['object_class'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["object_class"] = "str notNull";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = "{$this->object_class_id} - {$this->object_class}";
    }


    /**
     * Get the ID using ON DUPLICATE KEY UPDATE MySQL feature
     *
     * @param string $object_class Specified Class
     *
     * @return int
     * @throws Exception
     */
    static function getID($object_class, $force_store = false)
    {
        $args = func_get_args();

        if (!$force_store && !class_exists($object_class) && !CExObject::getValidObject($object_class)) {
            throw new Exception("CObjectClass '$object_class' is not a class");
        }

        $cache = new Cache('CObjectClass.getID', $args, Cache::INNER_OUTER);
        if ($object_class_id = $cache->get()) {
            return $object_class_id;
        }

        // Take a mutex for 10 seconds
        $mutex = new CMbMutex("object-class-$object_class");
        $mutex->acquire(5);

        $self = new self();

        $ds = $self->getDS();

        $where = [
            "object_class" => $ds->prepare("= ?", $object_class),
        ];
        $ids   = $self->loadIds($where);
        $count = count($ids);

        // We have matching class
        if ($count > 0) {
            if ($count > 1) {
                $mutex->release();
                trigger_error("CObjectClass '$object_class' non unique : " . implode(', ', $ids), E_USER_WARNING);
            }

            $result = $cache->put(reset($ids));

            $mutex->release();

            return $result;
        }

        // Not in DB, save it
        $self->object_class = $object_class;
        $self->rawStore();

        $result = $cache->put($self->_id);

        $mutex->release();

        return $result;
    }

    static function getClass($object_class_id)
    {
        $cache = new Cache('CObjectClass.getClass', $object_class_id, Cache::INNER_OUTER);
        if ($object_class = $cache->get()) {
            return $object_class;
        }

        $self = new self();
        $self->load($object_class_id);

        if ($self->_id) {
            $result = $cache->put($self->object_class);

            return $result;
        } else {
            throw new Exception("CObjectClass with id '$object_class_id' is not a valid id");
        }
    }
}
