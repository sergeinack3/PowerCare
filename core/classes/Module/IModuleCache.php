<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Module;

interface IModuleCache
{
    /**
     * Clears cache keys from patterns
     *
     * @param int $layer
     *
     * @return void
     */
    public function clear(int $layer): void;

    /**
     * Specific actions to run
     *
     * @return void
     */
    public function clearSpecialActions(): void;
}
