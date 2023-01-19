<?php
/**
 * @package Mediboard\mMdiusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CModelObject;
use Ox\Core\ComparatorException;
use Ox\Core\IComparator;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Mediusers comparator
 */
class CMediusersComparator implements IComparator, IShortNameAutoloadable {
  /**
   * @inheritDoc
   */
  public function equals($a, $b) {
    if (!$a instanceof CMediusers || !$b instanceof CMediusers) {
      throw new ComparatorException('Comparator-error-Invalid argument type');
    }

    if ($a->_id && $b->_id) {
      return $a->_id === $b->_id;
    }
    if ($a->_user_username && $b->_user_username) {
      return $a->_user_username === $b->_user_username;
    }
    if ($a->rpps && $b->rpps) {
      return $a->rpps === $b->rpps;
    }

    return false;
  }
}



