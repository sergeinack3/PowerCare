<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Contracts\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Abstraction for all Client used with sources
 */
interface HTTPClientInterface extends ClientInterface
{
    /** @var array */
    public const OPTIONS = [
        // content
        'body' => null, // array|string content body (if array given, form_params is set by default)
        'json' => null, // array|string set content-type
        'query' => [], // string[] additional query string values to merge with the request's URL
        'form_params' => null, // array element to send

        'headers' => [], // string[]|string[][] additional headers

        'verify_peer' => true, // bool
        'connect_timeout' => null, // float - the idle timeout - defaults to ini_get('default_socket_timeout')
        'timeout' => null, // // float - the maximum execution time for the request+response as a whole

        // auth
        'ox-token' => null, // string - use for custom header authentication X-OXAPI-KEY
        'auth_basic' => null,   // array|string - an array containing the username as first value, and optionally the
                                //   password as the second one; or string like username:password - enabling HTTP Basic
                                //   authentication (RFC 7617)
        'auth_bearer' => null,  // string - a token enabling HTTP Bearer authorization (RFC 6750)
    ];


    /**
     * Send an HTTP request.
     *
     * @param RequestInterface $request Request to send
     * @param array            $options Request options to apply to the given
     *                                  request and to the transfer.
     *
     * @return ResponseInterface
     */
    public function send(RequestInterface $request, array $options = []): ResponseInterface;

    /**
     * Create and send an HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string              $method  HTTP method.
     * @param string              $uri     URI object or string.
     * @param array               $options Request options to apply.
     *
     * @return ResponseInterface
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface;
}
