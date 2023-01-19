<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\CAppUI;
use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Symfony\Component\HttpFoundation\Request;

class RequestBulk
{
    use RequestHelperTrait;

    /** @var string */
    public const KEY_ID = 'id';

    /** @var string */
    public const KEY_PATH = 'path';

    /** @var string */
    public const KEY_METHOD = 'method';

    /** @var string */
    public const KEY_PARAMS = 'parameters';

    /** @var string */
    public const KEY_BODY = 'body';

    /** @var string */
    public const HEADER_SUB_REQUEST = 'X-BULK-OPERATION';

    /** @var array */
    public const ALLOWED_KEYS = [
        self::KEY_ID     => 'string',
        self::KEY_PATH   => 'string',
        self::KEY_METHOD => 'string',
        self::KEY_PARAMS => 'array',
        self::KEY_BODY   => 'array',
    ];

    /** @var array */
    public const REQUIRED_KEYS = [
        self::KEY_ID,
        self::KEY_PATH,
        self::KEY_METHOD,
    ];

    /** @var int */
    public const MAX_OPERATIONS = 10;

    /** @var RequestApi */
    private $request_api;

    /** @var Request[] */
    private $requests = [];

    /**
     * CBulkOperations constructor.
     *
     * @param RequestApi $request_api
     */
    public function __construct(RequestApi $request_api)
    {
        $this->request_api = $request_api;
    }

    /**
     * @return Request[]
     * @throws ApiRequestException
     */
    public function createRequests(): array
    {
        $master_request = $this->request_api->getRequest();

        $content_parsed = $this->parseContent($master_request->getContent());

        foreach ($content_parsed['data'] as $operation) {
            // Init
            $external_url = CAppUI::conf('external_url');
            $path         = $operation[self::KEY_PATH];
            $path         = strpos($path, 'http') === 0 ? str_replace($external_url, '', $path) : $path;
            $method       = $operation[self::KEY_METHOD];
            $id           = $operation[self::KEY_ID];
            $parameters   = $operation[self::KEY_PARAMS] ?? [];
            $body         = $operation[self::KEY_BODY] ?? null;
            if ($body) {
                $body = json_encode($body);
            }

            $cookies = $master_request->cookies->all();
            $server  = $master_request->server->all();
            $files   = [];

            // Create request
            $request = Request::create($path, $method, $parameters, $cookies, $files, $server, $body);

            // Custom header is used to control not allowed bulk operations
            $request->headers->add([self::HEADER_SUB_REQUEST => true]);

            $this->requests[$id] = $request;
        }

        return $this->requests;
    }

    /**
     * @param string $content_json
     *
     * @return array
     * @throws ApiRequestException
     */
    private function parseContent($content_json): array
    {
        // Check content
        if ($content_json === '') {
            throw new ApiRequestException('Missing json content');
        }

        // Check json
        $content_decoded = json_decode($content_json, true);
        if ($content_decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiRequestException('Invalid json content');
        }

        // Check json decoded
        if (!is_array($content_decoded)) {
            throw new ApiRequestException('Invalid json content (must be an array)');
        }

        if (!array_key_exists('data', $content_decoded)) {
            throw new ApiRequestException('Invalid json content (missing data key)');
        }

        if (!is_array($content_decoded['data'])) {
            throw new ApiRequestException('Invalid json content (data must be an array)');
        }

        // Check limit operations
        if (count($content_decoded['data']) > self::MAX_OPERATIONS) {
            throw new ApiRequestException('Max bulk operations limit exceeded');
        }

        // Chek each requests
        $request_ids = [];
        foreach ($content_decoded['data'] as $request) {
            foreach (self::REQUIRED_KEYS as $key_required) {
                if (!array_key_exists($key_required, $request)) {
                    throw new ApiRequestException('Invalid request (missing required keys)');
                }
            }

            foreach ($request as $key_request => $value_request) {
                if (!array_key_exists($key_request, self::ALLOWED_KEYS)) {
                    throw new ApiRequestException('Invalid request (unknown schema key ' . $key_request . ')');
                }

                if (gettype($value_request) !== self::ALLOWED_KEYS[$key_request]) {
                    throw new ApiRequestException('Invalid request (wrong type key ' . $key_request . ')');
                }
            }

            if (in_array($request[self::KEY_ID], $request_ids, true)) {
                throw new ApiRequestException('Invalid request (duplicate id ' . $request[self::KEY_ID] . ')');
            }

            $request_ids[] = $request[self::KEY_ID];
        }

        return $content_decoded;
    }

    /**
     * @return Request[]
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

}
