<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Traits;

use Ox\Core\CMbObject;

/**
 * Description
 */
trait StandardPermTrait
{
    abstract public function loadRefParentForPerm(bool $cache = true): ?CMbObject;

    /**
     * @param int $permType
     *
     * @return bool
     */
    public function getPerm($permType)
    {
        $parent = $this->loadRefParentForPerm(true);

        if ($parent && $parent->_id) {
            return $parent->getPerm($permType);
        }

        return parent::getPerm($permType);
    }
}
