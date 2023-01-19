<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Handlers;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CStoredObject;

/**
 * Class ObjectHandler
 */
abstract class ObjectHandler implements IShortNameAutoloadable
{
    /**
     * ObjectHandler constructor.
     */
    final public function __construct()
    {
    }

    /**
     * Is object handled ?
     *
     * @param CStoredObject $object Object handled
     *
     * @return bool
     */
    public static function isHandled(CStoredObject $object)
    {
        return (!isset($object->_skip_handler));
    }
}
