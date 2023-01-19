<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CLogAccessMedicalData
 */
class CLogAccessMedicalData extends CMbObject
{
    public $access_id;

    public $user_id;
    public $datetime;
    public $group_id;
    public $context;

    // Meta
    public $object_id;
    public $object_class;
    public $_ref_object;

    public $_date_min;
    public $_date_max;

    public $_ref_user;
    public $_ref_group;

    static $context_types = ['timeline', 'trace'];

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = 'log_access_medical_data';
        $spec->key      = 'access_id';
        $spec->loggable = false;

        $spec->iodkus[] = ['user_id', 'datetime', 'object_id', 'object_class'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["user_id"]      = "ref class|CMediusers notNull back|log_access_user";
        $props["datetime"]     = "dateTime notNull";
        $props["group_id"]     = "ref class|CGroups notNull back|log_access_medical_data";
        $props["object_id"]    = "ref notNull class|CStoredObject meta|object_class back|log_access_medical_data unlink";
        $props["object_class"] = "enum notNull list|CSejour|CPatient|COperation|CConsultation";
        $props["context"]      = "enum list|" . implode('|', self::$context_types);

        $props['_date_min'] = "date";
        $props['_date_max'] = "date";

        return $props;
    }

    /**
     * @return CMediusers
     */
    function loadRefUser()
    {
        return $this->_ref_user = $this->loadFwdRef("user_id", true);
    }

    /**
     * @return CGroups
     */
    function loadRefGroup()
    {
        return $this->_ref_group = $this->loadFwdRef("group_id", true);
    }

    /**
     * Log into database an access
     *
     * @param int    $user_id      CMediusers_id
     * @param string $object_class Object_class
     * @param int    $object_id    Object_id
     * @param string $datetime     datetime
     * @param int    $group_id     group where access has been done
     * @param string $context      Context
     *
     * @return bool the status of executed request.
     */
    static function logintoDb($user_id, $object_class, $object_id, $datetime, $group_id, $context)
    {
        $object = new self();
        $ds     = $object->getDS();
        $sql    = "INSERT IGNORE INTO {$object->_spec->table} 
              (`access_id`, `user_id`, `datetime`, `object_class`, `object_id`, `group_id`, `context`)
              VALUES (null, '$user_id', '$datetime', '$object_class', '$object_id', '$group_id', '$context');";

        return $ds->exec($sql);
    }

    /**
     * Clean-up duplicate objects
     *
     * @param CStoredObject[] $objects Objects to clean duplicate info of
     *
     * @return int
     * @throws Exception
     */
    static function cleanupDuplicates(array $objects)
    {
        if (count($objects) === 0) {
            return 0;
        }

        /** @var CStoredObject $first */
        $first = reset($objects);
        $ids   = CMbArray::pluck($objects, $first->_spec->key);
        $ds    = $first->getDS();

        $where = [
            "object_class" => "= '$first->_class'",
            "object_id"    => $ds->prepareIn($ids),
        ];

        $fields = [
            "GROUP_CONCAT(access_id) AS ids",
            "user_id",
            "datetime",
        ];

        $log  = new self();
        $list = $log->countMultipleList($where, null, ["user_id", "datetime"], null, $fields);

        $count = 0;

        foreach ($list as $_log) {
            if ($_log["total"] <= 1) {
                continue;
            }

            $ids = explode(",", $_log["ids"]);
            array_shift($ids);

            $where = [
                $log->_spec->key => $ds->prepareIn($ids),
            ];

            $req = new CRequest();
            $req->addWhere($where);
            $ds->exec($req->makeDelete($log));

            $count += count($ids);
        }

        return $count;
    }

    /**
     * LogSejourAccess
     *
     * @param CMbObject $object  Object
     * @param string    $context Context
     *
     * @return bool has the access been logged
     */
    static function logForObject(CMbObject $object, $context = "")
    {
        $group = CGroups::loadCurrent();
        if ($object instanceof IGroupRelated && ($related_group = $object->loadRelGroup())) {
            $group = $related_group->_id ? $related_group : $group;
        }

        if (!$object->_id || !CAppUI::conf("admin CLogAccessMedicalData enable_log_access", $group)) {
            return false;
        }

        $user     = CMediusers::get();
        $conf     = CAppUI::conf("admin CLogAccessMedicalData round_datetime", $group);
        $datetime = CMbDT::dateTime();

        switch ($conf) {
            // minute
            case '1m':
                $datetime = CMbDT::format($datetime, "%y-%m-%d %H:%M:00");
                break;
            // 10 minutes
            case '10m':
                $minute   = CMbDT::format($datetime, "%M");
                $minute   = str_pad(floor($minute / 10) * 10, 2, 0, STR_PAD_RIGHT);
                $datetime = CMbDT::format($datetime, "%y-%m-%d %H:$minute:00");
                break;
            // 1 day
            case '1d':
                $datetime = CMbDT::format($datetime, "%y-%m-%d 00:00:00");
                break;
            // 1 hour
            default:
                $datetime = CMbDT::format($datetime, "%y-%m-%d %H:00:00");
                break;
        }

        return self::logintoDb($user->_id, $object->_class, $object->_id, $datetime, $group->_id, $context);
    }

    /**
     * Count the list of access for a sejour
     *
     * @param CSejour $sejour Sejour
     *
     * @return int
     */
    static function countListForSejour($sejour)
    {
        $log                   = new self();
        $where                 = [];
        $where["object_class"] = " = '$sejour->_class'";
        $where["object_id"]    = " = '$sejour->_id' ";

        return $log->countList($where);
    }

    /**
     * Load the list of access for a sejour
     *
     * @param CSejour $sejour Sejour
     * @param int     $page   Page number
     * @param int     $step   Step
     *
     * @return CLogAccessMedicalData[]
     */
    static function loadListForSejour($sejour, $page = 0, $step = 50)
    {
        $log = new self();

        $where                 = [];
        $where["object_class"] = " = '$sejour->_class'";
        $where["object_id"]    = " = '$sejour->_id' ";

        return $log->loadList($where, "datetime DESC", "$page, $step");
    }


    /**
     * @param CStoredObject $object
     *
     * @return void
     * @todo redefine meta raf
     * @deprecated
     */
    public function setObject(CStoredObject $object)
    {
        CMbMetaObjectPolyfill::setObject($this, $object);
    }

    /**
     * @param bool $cache
     *
     * @return mixed
     * @throws Exception
     * @deprecated
     * @todo redefine meta raf
     */
    public function loadTargetObject($cache = true)
    {
        return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
    }

    /**
     * @inheritDoc
     * @todo remove
     */
    function loadRefsFwd()
    {
        parent::loadRefsFwd();
        $this->loadTargetObject();
    }
}
