<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Redis;

use Ox\Components\Yampee\Redis\Connection;
use Ox\Components\Yampee\Redis\Exception\Connection as ConnectionException;

/**
 * Redis client
 */
class CRedisConnection extends Connection
{
    /**
     * CRedisConnection constructor.
     *
     * @param string    $host
     * @param int       $port
     * @param float|int $timeout
     *
     * @throws ConnectionException
     */
    public function __construct($host = 'localhost', $port = 6379, float $timeout = 5)
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if (!$socket) {
            throw new ConnectionException($host, $port);
        }

        $this->socket = $socket;
    }
}
