<?php

/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ftp;

use Ox\Core\CMbDT;
use Ox\Interop\Eai\Client\Legacy\ClientInterface;
use Ox\Mediboard\System\CExchangeSource;

class ResponseAnalyser implements RequestAnalyserInterface

{
    /**
     * @param array $context
     *
     * @return bool
     */
    public function serviceAvailable(ClientContext $context): bool
    {
        $error = $context->getClient()->getError();

        return ($error === null) ;
    }
}
