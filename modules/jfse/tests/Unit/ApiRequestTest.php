<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit;

use Ox\Core\Cache;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Utils;
use Ox\Tests\OxUnitTestCase;

/**
 * Class ApiRequestTest
 *
 * @package Ox\Mediboard\Jfse\Tests\Unit\API
 */
class ApiRequestTest extends OxUnitTestCase
{
    /**
     * Test if the returned request is the expected one (especially for the body part)
     *
     * @config [CConfiguration] jfse API editorName Openxtrem
     * @config [CConfiguration] jfse API editorKey 124843218
     */
    public function testMakeRequestFromMethodContent(): void
    {
        $method_parameters = [
            'param1' => 'value1',
            'param2' => 'value2',
            'param3' => 'value3',
        ];
        $resident_uid      = '64-00-6a-8b-bb-f402-00-00-00-01-00';

        $cache = $this->getMockBuilder(Cache::class)
            ->setConstructorArgs([Utils::class, 'ResidentUid', Cache::INNER])->setMethods(['exists', 'get'])->getMock();
        $cache->method('exists')->willReturn(true);
        $cache->method('get')->willReturn($resident_uid);
        Utils::getInstance()->setResidentUidCache($cache);

        $cache = $this->getMockBuilder(Cache::class)
            ->setConstructorArgs([Utils::class, 'JfseUserId', Cache::INNER])->setMethods(['exists', 'get'])->getMock();
        $cache->method('exists')->willReturn(true);
        $cache->method('get')->willReturn(1);
        Utils::getInstance()->setJfseUserIdCache($cache);

        $expected = [
            'integrator' => [
                'etablissement' => 'TEST',
                'os'            => 'WINDOWS',
            ],
            'method'     => [
                'name'       => 'testMethod',
                'service'    => true,
                'parameters' => $method_parameters,
            ],
            'cardReader' => [
                'id'        => $resident_uid,
                'protocol'  => 'PCSC',
                'channel'   => '0',
                'reader'    => '0'
            ],
            'returnMode' => [
                'mode' => 1,
                'URL' => ''
            ],
            'idJfse'     => 1,
        ];

        $actual = json_decode(Request::forge('testMethod', $method_parameters)->getContent(), true);
        /* We unset the integrator name, because it depends on a config,
           and the key because it is a hash of the CMediusers's guid */
        unset($actual['integrator']['name']);
        unset($actual['integrator']['key']);

        $this->assertEquals($expected, $actual);
    }
}
