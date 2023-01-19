<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Request;

use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Request\RequestBulk;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestBulkTest extends OxUnitTestCase
{
    /**
     * @param Request $request
     * @param string  $exception_message_expected
     *
     * @throws ApiRequestException
     *
     * @dataProvider createRequestsFailedProvider
     */
    public function testCreateRequestsFailed(Request $request, string $exception_message_expected)
    {
        $request_api = RequestApi::createFromRequest($request);

        $bulk = new RequestBulk($request_api);
        $this->expectExceptionMessage($exception_message_expected);
        $bulk->createRequests();
    }

    /**
     * @param Request $request
     * @param         $expected_requests
     *
     * @throws ApiRequestException
     *
     * @dataProvider createRequestBulkProvider
     */
    public function testCreateRequestBulk(Request $request, $expected_requests)
    {
        $request_api = RequestApi::createFromRequest($request);
        $bulk        = new RequestBulk($request_api);
        $this->assertEquals($expected_requests, $bulk->createRequests());
    }

    /**
     * @return array
     */
    public function createRequestsFailedProvider(): array
    {
        return [
            'queryNoContent'          => [
                new Request(),
                'Missing json content',
            ],
            'invalideJson'            => [
                new Request([], [], [], [], [], [], 'notéjson'),
                'Invalid json content',
            ],
            'jsonNotAnArray'          => [
                new Request([], [], [], [], [], [], json_encode("notjsonarray")),
                'Invalid json content (must be an array)',
            ],
            'jsonNoData'              => [
                new Request([], [], [], [], [], [], json_encode(['foo' => 'bar'])),
                'Invalid json content (missing data key)',
            ],
            'jsonDataNotAnArray'      => [
                new Request([], [], [], [], [], [], json_encode(['data' => 'bar'])),
                'Invalid json content (data must be an array)',
            ],
            'moreThanLimitOperations' => [
                new Request([], [], [], [], [], [], json_encode($this->getDataMoreThanLimit())),
                'Max bulk operations limit exceeded',
            ],
            'missingRequiredKey'      => [
                new Request([], [], [], [], [], [], json_encode($this->getRequestMissingRequiredKey())),
                'Invalid request (missing required keys)',
            ],
            'notAllowedKey'           => [
                new Request([], [], [], [], [], [], json_encode($this->getRequestNotAllowedKey())),
                "Invalid request (unknown schema key foo)",
            ],
            'notAllowedType'          => [
                new Request([], [], [], [], [], [], json_encode($this->getRequestNotAllowedType())),
                "Invalid request (wrong type key id)",
            ],
            'duplicateRequestId'      => [
                new Request([], [], [], [], [], [], json_encode($this->getRequestDuplicateId())),
                "Invalid request (duplicate id 1)",
            ],
        ];
    }

    /**
     * @return array
     */
    public function createRequestBulkProvider(): array
    {
        return [
            'singleRequestBulk' => [
                new Request(
                    [], [], [], [], [], ['REQUEST_TIME' => 10, 'REQUEST_TIME_FLOAT' => 10.0], json_encode(
                          [
                              'data' => [
                                  [
                                      RequestBulk::KEY_ID     => '1',
                                      RequestBulk::KEY_PATH   => 'path',
                                      RequestBulk::KEY_METHOD => 'get',
                                  ],
                              ],
                          ]
                      )
                ),
                [1 => $this->getSingleRequestBulk('path', 'get')],
            ],
            'multiRequestBulk'  => [
                new Request(
                    [], [], [], [], [], ['REQUEST_TIME' => 10, 'REQUEST_TIME_FLOAT' => 10.0], json_encode(
                          [
                              'data' => [
                                  [
                                      RequestBulk::KEY_ID     => '1',
                                      RequestBulk::KEY_PATH   => 'path',
                                      RequestBulk::KEY_METHOD => 'get',
                                  ],
                                  [
                                      RequestBulk::KEY_ID     => 'toto',
                                      RequestBulk::KEY_PATH   => 'new_path',
                                      RequestBulk::KEY_METHOD => 'post',
                                  ],
                              ],
                          ]
                      )
                ),
                [
                    1      => $this->getSingleRequestBulk('path', 'get'),
                    'toto' => $this->getSingleRequestBulk('new_path', 'post'),
                ],
            ],
        ];
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return Request
     */
    private function getSingleRequestBulk(string $path, string $method): Request
    {
        // Disable requestTime
        $singleRequestBulk = Request::create(
            $path,
            $method,
            [],
            [],
            [],
            ['REQUEST_TIME' => 10, 'REQUEST_TIME_FLOAT' => 10.0]
        );
        $singleRequestBulk->headers->add([RequestBulk::HEADER_SUB_REQUEST => true]);

        return $singleRequestBulk;
    }

    /**
     * @return array
     */
    private function getDataMoreThanLimit(): array
    {
        $data = ['data' => []];
        for ($i = 0; $i < RequestBulk::MAX_OPERATIONS * 2; $i++) {
            $data['data'][] = 'foo';
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getRequestMissingRequiredKey(): array
    {
        return [
            'data' => [
                [RequestBulk::KEY_ID => '1'],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getRequestNotAllowedKey(): array
    {
        return [
            'data' => [
                [
                    RequestBulk::KEY_ID     => '1',
                    RequestBulk::KEY_PATH   => 'path',
                    RequestBulk::KEY_METHOD => 'get',
                    'foo'                   => 'bar',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getRequestNotAllowedType(): array
    {
        return [
            'data' => [
                [
                    RequestBulk::KEY_ID     => 1,
                    RequestBulk::KEY_PATH   => 'path',
                    RequestBulk::KEY_METHOD => 'get',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getRequestDuplicateId(): array
    {
        return [
            'data' => [
                [
                    RequestBulk::KEY_ID     => '1',
                    RequestBulk::KEY_PATH   => 'path',
                    RequestBulk::KEY_METHOD => 'get',
                ],
                [
                    RequestBulk::KEY_ID     => '1',
                    RequestBulk::KEY_PATH   => 'path2',
                    RequestBulk::KEY_METHOD => 'post',
                ],
            ],
        ];
    }
}
