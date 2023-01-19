<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ftp;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Interop\Eai\Resilience\ClientContext;

class CircuitBreakerException extends CMbException
{
    public const WAITING     = 1;
    public const SOURCE_LOCK = 2;
    public const CALL_ERROR  = 3;

    /** @var int */
    protected $id;

    protected ?ClientContext $client_context = null;

    public function __construct(int $id, string ...$args)
    {
        $this->id = $id;
        $message = CAppUI::tr("CircuitBreakerException-$id", $args);

        parent::__construct($message, $id);
    }

    /**
     * @param ClientContext|null $client_context
     *
     * @return CircuitBreakerException
     */
    public function setClientContext(?ClientContext $client_context): CircuitBreakerException
    {
        $this->client_context = $client_context;

        return $this;
    }
}
