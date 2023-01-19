<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Purge;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CStoredObject;
use Ox\Core\Logger\LoggerLevels;
use Throwable;

/**
 * Description
 */
abstract class CObjectPurger implements IShortNameAutoloadable
{
    /** @var string[] */
    public static $allowed_classes = [
        "COperation" => "COperationPurger",
        "CSejour"    => "CSejourPurger",
        "CPatient"   => "CPatientPurger",
    ];

    /** @var string */
    protected $class_name;

    /**
     * Instanciate an object purger
     *
     * @param string $class Name of the class to purge
     *
     * @return CObjectPurger
     */
    public static function getPurger(string $class): ?CObjectPurger
    {
        if (isset(static::$allowed_classes[$class])) {
            return new static::$allowed_classes[$class]();
        }

        return null;
    }

    /**
     * Count the objects to purge
     *
     * @return int
     * @throws Exception
     *
     */
    public function countPurgeable()
    {
        if (!$this->class_name || !class_exists($this->class_name)) {
            return 0;
        }

        /** @var CStoredObject $obj */
        $obj = new $this->class_name();

        return $obj->countList($this->getWhere(), $this->getGroupBy(), $this->getLJoin());
    }

    /**
     * Purge some objects
     *
     * @param int $start Start at
     * @param int $step  Number of objects to purge
     *
     * @return array|null
     * @throws Exception
     *
     */
    public function purgeObjects($start = 0, $step = 10, $max_id = null): ?array
    {
        if (!$this->class_name || !class_exists($this->class_name)) {
            return null;
        }

        $start_time = microtime(true);

        /** @var CStoredObject $obj */
        $obj = new $this->class_name();

        $where = $this->getWhere();

        if ($max_id) {
            $ds                                                         = $obj->getDS();
            $where[$obj->getSpec()->table . '.' . $obj->getSpec()->key] = $ds->prepare('< ?', $max_id);
        }

        $obj_to_purge = $obj->loadList($where, null, "$start,$step", $this->getGroupBy(), $this->getLJoin());

        $messages = [];
        $purge    = [];
        foreach ($obj_to_purge as $_obj) {
            $view = $_obj->_guid . ' : ' . $_obj->_view;
            try {
                if ($msg = $_obj->purge()) {
                    CAppUI::setMsg($msg, UI_MSG_WARNING);
                    $messages[] = $msg;
                } else {
                    CAppUI::setMsg($this->class_name . "-msg-delete", UI_MSG_OK);
                    $purge[] = $view;
                }
            } catch (Throwable $e) {
                $messages[] = $e->getMessage();
            }
        }

        $duration = round(microtime(true) - $start_time, 2);

        if ($messages) {
            CApp::log(CAppUI::tr("system-purge-error|pl") . ' / ' . $duration . 's', $messages, LoggerLevels::LEVEL_DEBUG);
        }

        if ($purge) {
            CApp::log(CAppUI::tr("system-purged-object|pl") . ' / ' . $duration . 's', $purge, LoggerLevels::LEVEL_DEBUG);
        }

        return [
            "ok" => $purge,
            "ko" => $messages,
        ];
    }

    /**
     * Get the where condition for purge
     *
     * @return array
     */
    protected function getWhere()
    {
        return [];
    }

    /**
     * Get the LJoin condition for purge
     *
     * @return array
     */
    protected function getLJoin()
    {
        return [];
    }

    /**
     * Get the group condition for purge
     *
     * @return null|string
     */
    protected function getGroupBy(): ?string
    {
        return null;
    }
}
