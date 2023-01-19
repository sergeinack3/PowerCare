<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Pinned tab in the Appbar for a user and module
 */
class CPinnedTab extends CMbObject
{
    public const RESOURCE_TYPE = 'pinned_tab';

    /** @var int Primary key */
    public $pinned_tab_id;

    /** @var int */
    public $user_id;

    /** @var int */
    public $module_id;

    /** @var int */
    public $module_action_id;

    public $_mod_name;

    public $_tab_name;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec                               = parent::getSpec();
        $spec->table                        = "pinned_tab";
        $spec->key                          = "pinned_tab_id";
        $spec->loggable                     = CMbObjectSpec::LOGGABLE_NEVER;

        $spec->uniques['user_action']       = ['user_id', 'module_action_id'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props['user_id']          = 'ref class|CMediusers notNull fieldset|default back|pinned_tabs cascade';
        $props['module_id']        = 'ref class|CModule notNull fieldset|default back|pinned_tabs';
        $props['module_action_id'] = 'ref class|CModuleAction notNull fieldset|default back|pinned_actions';

        $props['_mod_name'] = 'str fieldset|default';
        $props['_tab_name'] = 'str fieldset|default';

        return $props;
    }

    public function updateFormFields()
    {
        parent::updateFormFields();

        /** @var CModule $module */
        $this->_mod_name = ($module = $this->loadFwdRef('module_id')) ? $module->mod_name : null;
        /** @var CModuleAction $mod_action */
        $this->_tab_name = ($mod_action = $this->loadFwdRef('module_action_id')) ? $mod_action->action : null;
    }

    public function store()
    {
        if (!$this->user_id) {
            $this->user_id = CMediusers::get()->_id;
        }

        return parent::store();
    }

    /**
     * @throws CMbException
     */
    public function createPin(CMediusers $user): void
    {
        $module = CModule::getActive($this->_mod_name);

        if ($module === null) {
            throw new CMbException('system-msg-The %s module is not active', $this->_mod_name);
        }

        $module->registerTabs();

        if (!array_key_exists($this->_tab_name, $module->_tabs[CModule::TAB_STANDARD])) {
            throw new CMbException('CPinnedTab-Error-Tab is not part of module', $this->_tab_name, $module->mod_name);
        }

        $module_action_id = CModuleAction::getID($module->mod_name, $this->_tab_name);

        $this->user_id          = $user->_id;
        $this->module_id        = $module->_id;
        $this->module_action_id = $module_action_id;
    }

    /**
     * @throws CMbException
     */
    public static function removePinnedTabs(string $mod_name, ?CMediusers $user = null): void
    {
        if (!$user) {
            $user = CMediusers::get();
        }

        $module = CModule::getActive($mod_name);

        if ($module === null) {
            throw new CMbException('system-msg-The %s module is not active', $mod_name);
        }

        $pin            = new self();
        $pin->user_id   = $user->_id;
        $pin->module_id = $module->_id;

        $pins = $pin->loadMatchingListEsc();

        if ($msg = $pin->deleteAll(CMbArray::pluck($pins, 'pinned_tab_id'))) {
            throw new CMbException($msg);
        }
    }

    public function getPerm($permType)
    {
        if ($this->user_id === CMediusers::get()->_id) {
            return true;
        }

        return parent::getPerm($permType);
    }
}
