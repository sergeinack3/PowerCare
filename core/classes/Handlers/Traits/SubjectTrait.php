<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Handlers\Traits;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Core\Handlers\ObjectHandler;
use Ox\Mediboard\System\CConfigurationModelManager;

/**
 * Description
 */
trait SubjectTrait
{
    /**
     * @param ObjectHandlerEvent $event
     * @param array              ...$args
     *
     * @throws Exception
     */
    public function notify(ObjectHandlerEvent $event, ...$args): void
    {
        // Todo: Enable handlers when disconnecting
        if (!CConfigurationModelManager::isReady()) {
            return;
        }

        // Todo: No handler when public
        if (CApp::getInstance()->isPublic()) {
            return;
        }

        // Event Handlers
        HandlerManager::makeObjectHandlers();

        // Subject instance is put at the beginning of the call parameters
        array_unshift($args, $this);

        /** @var ObjectHandler $_observer */
        foreach (HandlerManager::getObjectHandlers() as $_observer) {
            $_trace = HandlerManager::mustLogHandler($_observer);

            $_callable = [$_observer, $event->getValue()];

            if ($_trace) {
                HandlerManager::trace('will be called.', $_observer, $event->getValue(), $args);
            }

            if (!is_callable($_callable)) {
                continue;
            }

            try {
                if ($_trace) {
                    HandlerManager::trace('is called.', $_observer, $event->getValue(), $args);
                }

                call_user_func_array($_callable, $args);

                if ($_trace) {
                    HandlerManager::trace('has been called.', $_observer, $event->getValue(), $args);
                }
            } catch (Exception $e) {
                // Todo: Throw handler call exception
                CAppUI::setMsg($e, UI_MSG_ERROR);
            }
        }
    }
}
