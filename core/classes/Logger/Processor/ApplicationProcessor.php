<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Logger\Processor;

use Monolog\Processor\ProcessorInterface;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbString;
use Ox\Core\Logger\ContextEncoder;

/**
 * Add extra data to log (user, ssid ...) and encode given context
 */
class ApplicationProcessor implements ProcessorInterface
{
    public function __invoke(array $record): array
    {
        if (isset($record['context'])) {
            $record['context'] = (new ContextEncoder($record['context']))->encode();
        }
        $record['extra']['user_id']      = (CAppUI::$user) ? CAppUI::$user->user_id : null;
        $record['extra']['server_ip']    = $_SERVER["SERVER_ADDR"] ?? null;
        $record['extra']['session_id']   = CMbString::truncate(session_id(), 15);
        $record['extra']['request_uid'] = CApp::getRequestUID();

        return $record;
    }
}
