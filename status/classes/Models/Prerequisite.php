<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status\Models;

/**
 * Prerequisite abstract class
 */
abstract class Prerequisite
{
    public $name        = "";
    public $description = "";
    public $mandatory   = false;
    public $reasons     = [];

    /**
     * Check prerequisite
     *
     * @param bool $strict Check also warnings
     *
     * @return bool
     */
    abstract function check($strict = true);

    /**
     * Return all instances of self
     *
     * @return self[]
     */
    abstract function getAll();

    /**
     * Check all items
     *
     * @param bool $strict Make strict checking
     *
     * @return bool
     */
    function checkAll($strict = true)
    {
        foreach ($this->getAll() as $item) {
            if (!$item->check($strict)) {
                return false;
            }
        }

        return true;
    }
}
