<?php
/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


namespace Ox\Mediboard\System\Tests\Classes;

use Ox\Core\Contracts\Client\HTTPClientInterface;
use Ox\Mediboard\System\CSourceHTTP;
use Psr\Http\Message\ResponseInterface;

trait SourceTestTrait
{
    /**
     * Source will return response after getClient and make request or send
     *
     * @param ResponseInterface $response
     * @param string            $function : request | send
     * @param CSourceHTTP|null  $source_mocked
     *
     * @return CSourceHTTP
     */
    public function mockSourceForResponse(
        ResponseInterface $response,
        ?CSourceHTTP $source_mocked = null,
        string $function = 'request'
    ): CSourceHTTP {
        $client = $this->getMockBuilder(HTTPClientInterface::class)
            ->getMock();
        $client->method($function)->willReturn($response);

        if (!$source_mocked) {
            $source_mocked = $this->getMockBuilder(CSourceHTTP::class)
                ->getMock();
        }
        $source_mocked->method('getClient')->willReturn($client);

        return $source_mocked;
    }
}
