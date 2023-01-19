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
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;
use Ox\Core\Mutex\CMbMutex;

/**
 * Module-Action link class
 */
class CModuleAction extends CStoredObject
{
    /** @var integer Primary key */
    public $module_action_id;

    /** @var string Module name */
    public $module;

    /** @var string Action name */
    public $action;

    /** @var CAccessLog[] Logs */
    public $_ref_access_logs;


    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->loggable = false;
        $spec->table    = 'module_action';
        $spec->key      = 'module_action_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props           = parent::getProps();
        $props["module"] = "str notNull seekable";
        $props["action"] = "str notNull seekable";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = "{$this->module} - {$this->action}";
    }

    /**
     * Get all CAccessLog[] backrefs
     *
     * @return CStoredObject[]|null
     */
    function loadRefsAccessLogs()
    {
        return $this->_ref_access_logs = $this->loadBackRefs("access_logs");
    }


    /**
     * Get the ID using ON DUPLICATE KEY UPDATE MySQL feature
     *
     * @param string $module Specified module
     * @param string $action Specified action
     *
     * @return int
     * @throws Exception
     */
    static function getID($module, $action)
    {
        $cache = new Cache('CModuleAction.getID', [$module, $action], Cache::INNER_OUTER);
        if ($module_action_id = $cache->get()) {
            return $module_action_id;
        }

        // Take a mutex for 10 seconds
        $mutex = new CMbMutex("module-action-$module-$action");
        $mutex->acquire(5);

        $self = new self();

        $ds = $self->getDS();

        $where = [
            "module" => $ds->prepare("= ?", $module),
            "action" => $ds->prepare("= ?", $action),
        ];
        $ids   = $self->loadIds($where);
        $count = is_countable($ids) ? count($ids) : 0;

        // We have matching module/action
        if ($count > 0) {
            if ($count > 1) {
                trigger_error("CModuleAction '$module/$action' non unique : " . implode(', ', $ids), E_USER_WARNING);
            }

            $result = $cache->put(reset($ids));

            $mutex->release();

            return $result;
        }

        // Not in DB, save it
        $self->module = $module;
        $self->action = $action;
        $self->rawStore();

        $result = $cache->put($self->_id);

        $mutex->release();

        return $result;
    }

    /**
     * Get actions for given module
     *
     * @param string $module Module name
     *
     * @return string|array Array of actions with actions as keys and ids as values
     */
    static function getActions($module)
    {
        static $modules_actions;
        if (!$modules_actions) {
            $request = new CRequest();
            $request->addColumn("module");
            $request->addColumn("action");
            $request->addColumn("module_action_id");
            $self            = new self;
            $ds              = $self->_spec->ds;
            $modules_actions = $ds->loadTree($request->makeSelect($self));
        }

        return $modules_actions[$module];
    }

    /**
     * Get module and action from a given CModuleAction ID
     *
     * @param integer $id CModuleAction ID
     *
     * @return array
     */
    static function getModuleAction($id)
    {
        if (!$id) {
            return [];
        }

        $module_action                   = new CModuleAction();
        $module_action->module_action_id = $id;

        if ($module_action->loadMatchingObject()) {
            return [$module_action->module, $module_action->action];
        }

        return [];
    }

    /**
     * Returns the path to the class-specific template
     *
     * @param string $type view|autocomplete|edit
     *
     * @return string|null
     */
    function getTypedTemplate($type)
    {
        if (!in_array($type, ["view", "autocomplete", "edit"])) {
            return null;
        }

        $mod_name = $this->_ref_module->mod_name;
        $template = "$mod_name/templates/{$this->_class}_$type.tpl";

        if (!is_file("modules/$template")) {
            $template = "system/templates/CMbObject_$type.tpl";
        }

        return "../../$template";
    }

    /**
     * Specific function clearing IDs in SHM
     *
     * @return int
     */
    static function clearCacheIDs()
    {
        return Cache::deleteKeys(Cache::OUTER, 'CModuleAction.getID-');
    }
}
