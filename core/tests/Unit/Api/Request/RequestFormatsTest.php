<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Request;

use Ox\Core\Api\Request\RequestFormats;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestFormatsTest extends OxUnitTestCase
{
    /**
     * @param string $query_content
     * @param array  $expected
     *
     * @dataProvider formatsProvider
     */
    public function testFormats($query_content, $expected)
    {
        $req        = new Request([], [], [], [], [], ['HTTP_' . RequestFormats::HEADER_KEY_WORD => $query_content]);
        $req_format = new RequestFormats($req);
        $this->assertEquals($expected, $req_format->getFormats());
    }

    //  /**
    //   * Test format not supported
    //   */
    //  public function testFormatsNotSupportedException() {
    //    $this->markTestSkipped('Throw exception in RequestFormats constructor');
    //
    //    $req = new Request([], [], [], [], [], ['HTTP_' . RequestFormats::HEADER_KEY_WORD => 'toto']);
    //    $this->expectException(ApiRequestException::class);
    //    new RequestFormats($req);
    //  }

    /**
     * @param string $query_content
     * @param array  $expected
     *
     * @dataProvider expectedFormatProvider
     */
    public function testGetExpectedFormat($query_content, $expected)
    {
        $req        = new Request([], [], [], [], [], ['HTTP_' . RequestFormats::HEADER_KEY_WORD => $query_content]);
        $req_format = new RequestFormats($req);
        $this->assertEquals($expected, $req_format->getExpected());
    }

    /**
     * @return array
     */
    public function formatsProvider()
    {
        return [
            'noFormat'         => [
                '',
                [''],
            ],
            'singleFormatJson' => [
                RequestFormats::FORMAT_JSON,
                [RequestFormats::FORMAT_JSON],
            ],
            'singleFormatXml'  => [
                RequestFormats::FORMAT_XML,
                [RequestFormats::FORMAT_XML],
            ],
            'multiFormats'     => [
                RequestFormats::FORMAT_JSON . ',' . RequestFormats::FORMAT_XML . ',' . RequestFormats::FORMAT_HTML . ','
                . RequestFormats::FORMAT_JSON_API,
                [
                    RequestFormats::FORMAT_JSON,
                    RequestFormats::FORMAT_XML,
                    RequestFormats::FORMAT_HTML,
                    RequestFormats::FORMAT_JSON_API,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function expectedFormatProvider()
    {
        return [
            'expectJson'     => [
                'foo,' . RequestFormats::FORMAT_JSON,
                RequestFormats::FORMAT_JSON,
            ],
            'expectXml'      => [
                'bar,' . RequestFormats::FORMAT_XML,
                RequestFormats::FORMAT_XML,
            ],
            'expectXmlFirst' => [
                RequestFormats::FORMAT_JSON . ',' . RequestFormats::FORMAT_XML,
                RequestFormats::FORMAT_XML,
            ],
        ];
    }
}
