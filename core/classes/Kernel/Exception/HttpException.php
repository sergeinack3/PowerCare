<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Exception;

use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class HttpException extends RuntimeException implements HttpExceptionInterface
{
    /** @var int */
    protected $status_code;

    /** @var array */
    protected $headers;

    /** @var bool */
    protected $is_loggable = true;

    /**
     * HttpException constructor.
     *
     * @param int         $status_code
     * @param null|string $message
     * @param array       $headers
     * @param int         $code
     */
    public function __construct($status_code, $message = null, array $headers = [], $code = 0)
    {
        $this->status_code = $status_code;
        $this->headers     = $headers;

        parent::__construct($message ?? "", $code);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status_code;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set response headers.
     *
     * @param array $headers Response headers
     *
     * @return HttpException
     */
    public function setHeaders(array $headers): HttpException
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param int $status_code
     *
     * @return $this
     */
    public function setStatusCode(int $status_code): HttpException
    {
        $this->status_code = $status_code;

        return $this;
    }

    /**
     * @return void
     * @throws HttpException
     */
    public function throw(): void
    {
        throw $this;
    }

    /**
     * @return bool
     */
    public function isLoggable(): bool
    {
        return $this->is_loggable;
    }
}
