<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * The last X tabs visited per user
 */
class CTabHit extends CMbObject
{
    public const LINES_COUNT_PER_USER = 100;
    public const PROBA_GC_OLD_HITS    = 100;

    /** @var int Primary key */
    public $tab_hit_id;

    /** @var int */
    public $user_id;

    /** @var int */
    public $module_action_id;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "tab_hit";
        $spec->key   = "tab_hit_id";

        $spec->loggable = CMbObjectSpec::LOGGABLE_NEVER;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props['user_id']          = 'ref class|CMediusers notNull cascade back|visited_tabs';
        $props['module_action_id'] = 'ref class|CModuleAction notNull cascade back|hits';

        return $props;
    }

    public function store()
    {
        if ($msg = parent::store()) {
            return $msg;
        }

        CApp::doProbably(self::PROBA_GC_OLD_HITS, [$this, 'removeOldHits']);

        return null;
    }

    public function removeOldHits()
    {
        $hit_ids = $this->getLastHits();

        if (!empty($hit_ids)) {
            $this->deleteHits($hit_ids);
        }
    }

    public static function registerHit(CModule $module, string $tab_name): ?self
    {
        if (!$user = CMediusers::get()) {
            return null;
        }

        if (
            isset($module->_tabs[CModule::TAB_STANDARD][$tab_name])
            || isset($module->_tabs[CModule::TAB_SETTINGS][$tab_name])
            || isset($module->_tabs[CModule::TAB_CONFIGURE][$tab_name])
        ) {
            $tab_hit                   = new self();
            $tab_hit->user_id          = $user->_id;
            $tab_hit->module_action_id = CModuleAction::getID($module->mod_name, $tab_name);
            $tab_hit->store();

            return $tab_hit;
        }

        return null;
    }

    /**
     * @return CTab[]
     *
     * @throws Exception
     */
    public function getMostCalledTabs(CMediusers $mediuser, int $limit): array
    {
        if (!$this->getDS()->hasTable($this->_spec->table)) {
            return [];
        }

        $hit = new self();
        $hit->user_id = $mediuser->_id;

        $tab_hits = $hit->loadMatchingListEsc('COUNT(*) DESC', $limit, 'module_action_id', null, null, false);

        $mod_actions_ids = CMbArray::pluck($tab_hits, 'module_action_id');

        $unsorted_mod_actions = (new CModuleAction())->loadAll($mod_actions_ids);
        return $this->buildTabsFromModuleActions(
            CMbArray::ksortByArray($unsorted_mod_actions, $mod_actions_ids)
        );
    }

    private function getLastHits(?CMediusers $user = null): array
    {
        $ds = $this->getDS();

        return $this->loadIds(
            ['user_id' => $ds->prepare('= ?', $user ? $user->_id : $this->user_id)],
            'tab_hit_id DESC',
            self::LINES_COUNT_PER_USER
        );
    }

    private function deleteHits(array $keep_ids, ?CMediusers $user = null): void
    {
        $ds            = $this->getDS();
        $ids_to_delete = $this->loadIds(
            [
                'user_id'    => $ds->prepare('= ?', $user ? $user->_id : $this->user_id),
                'tab_hit_id' => $ds->prepareNotIn($keep_ids),
            ]
        );

        if ($msg = $this->deleteAll($ids_to_delete)) {
            throw new CMbException($msg);
        }
    }

    /**
     * @return CTab[]
     */
    private function buildTabsFromModuleActions(array $mod_actions): array
    {
        $tabs    = [];
        $modules = [];
        /** @var CModuleAction $mod_action */
        foreach ($mod_actions as $mod_action) {
            if (!isset($modules[$mod_action->module])) {
                $modules[$mod_action->module] = CModule::getVisible($mod_action->module);
            }

            $module = $modules[$mod_action->module];

            if (!$module) {
                continue;
            }

            $module->registerTabs();

            $tabs[] = $module->buildTab($mod_action->action);
        }

        return $tabs;
    }
}
