<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Mediboard\System\CConfigurationModelManager;

/**
 * Class CEAIHandler
 *
 * @abstract Event handler class for Mediboard
 */
abstract class CEAIHandler implements IShortNameAutoloadable
{
    /**
     * Subject notification mechanism
     *
     * @param string $message on[Before|After][Build]
     *
     * @return void
     */
    static function notify($message/*, ... */)
    {
        // Todo: Enable handlers when disconnecting
        if (!CConfigurationModelManager::isReady()) {
            return;
        }

        // Todo: No handler when public
        if (CApp::getInstance()->isPublic()) {
            return;
        }

        $args = func_get_args();
        array_shift($args); // $message

        // Event Handlers
        HandlerManager::makeEAIHandlers();

        foreach (HandlerManager::getEAIHandlers() as $_handler) {
            $_trace = HandlerManager::mustLogHandler($_handler);

            try {
                if ($_trace) {
                    HandlerManager::trace('is called.', $_handler, "on$message", $args);
                }

                call_user_func_array([$_handler, "on$message"], $args);

                if ($_trace) {
                    HandlerManager::trace('has been called.', $_handler, "on$message", $args);
                }
            } catch (Exception $e) {
                CAppUI::setMsg($e, UI_MSG_ERROR);
            }
        }
    }

    /**
     * Trigger before build message
     *
     * @return bool
     */
    function onBeforeBuild()
    {
    }

    /**
     * Trigger after build message
     *
     * @return bool
     */
    function onAfterBuild()
    {
    }
}
