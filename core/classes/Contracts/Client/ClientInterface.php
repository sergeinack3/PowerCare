<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Contracts\Client;

use Ox\Mediboard\System\CExchangeSource;

/**
 * Abstraction for all Client used with sources
 */
interface ClientInterface
{
    /** @var string  */
    public const EVENT_AFTER_REQUEST = 'client.after.request';
    /** @var string  */
    public const EVENT_BEFORE_REQUEST = 'client.before.request';
    /** @var string  */
    public const EVENT_RESPONSE = 'client.response';
    /** @var string  */
    public const EVENT_EXCEPTION = 'client.exception';

    /**
     * Initialize client
     *
     * @param CExchangeSource $source
     *
     * @return void
     */
    public function init(CExchangeSource $source): void;

    /**
     * Test if the service is available
     *
     * @return bool
     */
    public function isReachableSource(): bool;

    /**
     * Test if configurations is ok for the service
     *
     * @return bool
     */
    public function isAuthentificate(): bool;

    /**
     * Test the service to retrieve response time
     *
     * @return int
     */
    public function getResponseTime(): int;
}
