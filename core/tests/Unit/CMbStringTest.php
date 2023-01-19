<?php

/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\CMbSecurity;
use Ox\Core\CMbString;
use Ox\Mediboard\Patients\CPatient;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CMbStringTest extends OxUnitTestCase
{
    public function testToWords(): void
    {
        $text1 = CMbString::toWords(1983.36);
        $text2 = "mille neuf cent quatre-vingt-trois virgule trente-six";
        $this->assertEquals($text1, $text2);
    }

    /**
     * @param string|object $text
     * @param int           $size
     * @param string|object $expected
     * @param string        $replacement
     *
     * @dataProvider truncateTextProvider
     */
    public function testTruncate($text, $size, $expected, $replacement = '...'): void
    {
        $this->assertEquals($expected, CMbString::truncate($text, $size, $replacement));
    }

    /**
     * Provide text and expected results for the truncate function
     *
     * @return array
     */
    public function truncateTextProvider(): array
    {
        return [
            ['toto', 10, 'toto'],
            [new CPatient(), 10, new CPatient()],
            ['Test truncate text too long for it', 10, 'Test tr...'],
            ['Test truncate text too long for it', 12, 'Test trun?!?', '?!?'],
        ];
    }

    /**
     * Test for the function uriToArray which parses an URI and returns an array [protocol, host, params ...]
     */
    public function testUriToArray(): void
    {
        $expected = [
            "scheme" => "https",
            "host"   => "mediboard.com",
            "path"   => null,
            "params" => [
                "sejour_id" => "1111",
                "patient"   => "1234",
            ],
        ];

        $this->assertEquals($expected, CMbString::uriToArray("https://mediboard.com?sejour_id=1111&patient=1234"));

        $expected["path"] = "//";
        $this->assertEquals($expected, CMbString::uriToArray("https://mediboard.com//?sejour_id=1111&patient=1234"));

        $expected["path"] = "/index.php";
        $this->assertEquals(
            $expected,
            CMbString::uriToArray("https://mediboard.com/index.php?sejour_id=1111&patient=1234")
        );

        $expected["path"]   = null;
        $expected["params"] = null;
        $this->assertEquals($expected, CMbString::uriToArray("https://mediboard.com"));

        $expected["path"] = "/";
        $this->assertEquals($expected, CMbString::uriToArray("https://mediboard.com/"));
    }

    /**
     * Test is base64 string
     *
     * @param string $string The string to be tested
     */
    public function testIsBase64(): void
    {
        $string = "I am not base 64 encoded";
        $this->assertFalse(CMbString::isBase64($string));

        $string = base64_encode($string);
        $this->assertTrue(CMbString::isBase64($string));
    }

    /**
     * @param string $code
     * @param bool   $expected
     *
     * @dataProvider isLuhnProvider
     */
    public function testIsLuhn(?string $code, bool $expected): void
    {
        $this->assertEquals($expected, CMbString::luhn($code));
    }

    public function isLuhnProvider(): array
    {
        return [
            'number_is_luhn1'       => ['15362', true],
            'number_is_luhn2'       => ['999985566622', true],
            'number_is_luhn3'       => ['0', true],
            'number_is_luhn_letter' => ['15A362', true],
            'number_is_luhn_space'  => ['15 36 2', true],
            'number_is_luhn_empty'  => ['', true],
            'number_is_luhn_null'   => [null, true],
            'number_is_not_luhn1'   => ['12255566', false],
            //      'number_is_not_luhn2'   => [598776, false],
        ];
    }

    /**
     * @param string|null $code
     * @param bool        $expected
     *
     * @dataProvider isLuhnForAdeliProvider
     */
    public function testIsAdeliLuhn(?string $code, bool $expected): void
    {
        $this->assertEquals($expected, CMbString::luhnForAdeli($code));
    }

    public function isLuhnForAdeliProvider(): array
    {
        return [
            'number_is_luhn1'       => ['15362', true],
            'number_is_luhn2'       => ['999985566622', true],
            'number_is_luhn3'       => ['0', true],
            'number_is_luhn_letter' => ['15A362', false],
            'number_is_luhn_space'  => ['15 36 2', true],
            'number_is_luhn_empty'  => ['', true],
            'number_is_luhn_null'   => [null, true],
            'number_is_luhn_adeli'  => ['9DA005191', true],
            //      'number_is_not_luhn1'   => [598776, false],
            'number_is_not_luhn2'   => ['aaaaaaaaa', false],
        ];
    }

    public function testGetPathFromUrl(): void
    {
        $url = 'http://username:password@hostname:9090';
        $this->assertEquals(
            "/path",
            CMbString::getPathFromUrl("$url/path?arg=value#anchor")
        );
        $this->assertNull(CMbString::getPathFromUrl($url));
    }

    public function testRemoveHtml(): void
    {
        $this->assertEquals("samplesample", CMbString::removeHtml("sample<br/>sample"));
        $this->assertEquals("", CMbString::removeHtml(""));
    }

    public function testStartsWith(): void
    {
        $txt = "Lorem ipsum";
        $this->assertTrue(CMbString::startsWith($txt, "Lorem"));
        $this->assertFalse(CMbString::startsWith($txt, "ipsum"));
    }

    public function testCompareAdresses(): void
    {
        $str1 = "50 Rue de Mediboard";
        $str2 = "59 Lot de Mediboard";
        $str3 = "48 Boulevard de Lorem ipsum";

        $this->assertTrue(CMbString::compareAdresses($str1, $str2));
        $this->assertTrue(CMbString::compareAdresses($str1, $str1));
        $this->assertFalse(CMbString::compareAdresses($str1, $str3));
    }

    public function testCurrency(): void
    {
        $this->assertEquals("<span class='negative'>-500,18 &euro;</span>", CMbString::currency(-500.18));
        $this->assertEquals("<span class='empty'>0,00 &euro;</span>", CMbString::currency(0.0));
    }

    public function testCanonicalize(): void
    {
        $str = "Lorem Ipsum Dolor";
        $this->assertEquals("lorem ipsum dolor", CMbString::canonicalize($str));
    }

    public function testIsEmailValid(): void
    {
        $str1 = "foo_bar@machine.test";
        $str2 = "foo_bar@machine";

        $this->assertEquals(1, CMbString::isEmailValid($str1));
        $this->assertEquals(0, CMbString::isEmailValid($str2));
    }

    public function testIsUUID(): void
    {
        $uuid = CMbSecurity::generateUUID();

        $this->assertTrue(CMbString::isUUID($uuid), "$uuid is not a valid UUID");
    }

    /**
     * @return string[][]
     */
    public function providerIsUUIDNotOk(): array
    {
        return [
            "12345678 (8)"                                       => ['123456zF'],
            "123456e8-14F8 (8-4)"                                => ['123456e8-14F8'],
            "123a56F8-1478-1236 (8-4-4)"                         => ['123a56F8-1478-1236'],
            "123a567F-1478-1zZ6-4R69 (8-4-4-4)"                  => ['123a567F-1478-1zZ6-4R69'],
            "123a567F-1478-1236-4569-01234567891 (8-4-4-4-11)"   => ['123a567F-1478-1236-4569-01234567891'],
            "123a567F-1478-1236-4569-0123456789101 (8-4-4-4-13)" => ['123a567F-1478-1236-4569-0123456789101'],
        ];
    }

    /**
     * @param string $uuid
     *
     * @dataProvider providerIsUUIDNotOk
     * @return void
     */
    public function testIsUUIDNotOk(string $uuid): void
    {
        $this->assertFalse(CMbString::isUUID($uuid));
    }

    public function testToBytes(): void
    {
        $this->assertEquals("1.02ko", CMbString::toDecaSI("1024"));
        $this->assertEquals("1.00Kio", CMbString::toDecaBinary("1024"));
    }

    public function testHtmlEncode(): void
    {
        $this->assertEquals("&lt;&gt;&amp;&quot;", CMbString::htmlEncode("<>&\""));
    }

    public function testHtmlToText(): void
    {
        $html = "<p>Test paragraph.</p><!-- Comment --> <a href='#fragment'>Other text</a>";

        $this->assertTrue(CMbString::isHtml($html));
        $this->assertEquals(
            "Test paragraph. Other text",
            CMbString::htmlToText("<p>Test paragraph.</p><!-- Comment --> <a href='#fragment'>Other text</a>")
        );
    }

    public function testBr2nl(): void
    {
        $this->assertEquals(
            "<p>Test paragraph.Other text</p>",
            CMbString::br2nl("<p>Test paragraph.<br />Other text</p>")
        );
    }

    public function testMakeUrlHyperlinks(): void
    {
        $url = "https://www.lorem.ipsum";

        $this->assertEquals(
            '<a href="https://www.lorem.ipsum" target="_blank">https://www.lorem.ipsum</a>',
            CMbString::makeUrlHyperlinks($url)
        );
    }

    public function testToQuery(): void
    {
        $this->assertEquals("foo=bar&lorem=ipsum", CMbString::toQuery(['foo' => 'bar', 'lorem' => 'ipsum']));
    }

    public function testGetCommonPrefix(): void
    {
        $this->assertEquals("Hello ", CMbString::getCommonPrefix("Hello world", "Hello there"));
    }

    /**
     * @return array[]
     */
    public function providerTestIsUTF8(): array
    {
        return [
            'UFT-8 string without accent'      => [
                'value'    => mb_convert_encoding("foo bar", 'UTF-8', 'ISO-8859-1'),
                "expected" => true,
            ],
            'UFT-8 string with accent'         => [
                'value'    => mb_convert_encoding("é", 'UTF-8', 'ISO-8859-1'),
                "expected" => true,
            ],
            'ISO-8859-1 string without accent' => ['value' => "a", "expected" => true],
            // considered like unicode: UTF-8
            'ISO-8859-1 string with accent'    => ['value' => "é", "expected" => false],

            'windows-1252 string with accent'    => [
                'value'    => mb_convert_encoding("é", 'Windows-1252', 'ISO-8859-1'),
                "expected" => false,
            ],
            // considered like unicode: UTF-8
            'windows-1252 string without accent' => [
                'value'    => mb_convert_encoding("a", 'Windows-1252', 'ISO-8859-1'),
                "expected" => true,
            ],

            'UTF-16 (without BOM) string with accent'    => [
                'value'    => mb_convert_encoding("é", 'UTF-16', 'ISO-8859-1'),
                "expected" => false,
            ],
            // considered like unicode: UTF-8
            'UTF-16 (without BOM) string without accent' => [
                'value'    => mb_convert_encoding("a", 'UTF-16', 'ISO-8859-1'),
                "expected" => true,
            ],

            'UTF-16 (BE) string with accent'    => [
                'value'    => mb_convert_encoding("é", 'UTF-16BE', 'ISO-8859-1'),
                "expected" => false,
            ],
            // considered like unicode: UTF-8
            'UTF-16 (BE) string without accent' => [
                'value'    => mb_convert_encoding("a", 'UTF-16BE', 'ISO-8859-1'),
                "expected" => true,
            ],

            'UTF-16 (LE) string with accent'    => [
                'value'    => mb_convert_encoding("é", 'UTF-16LE', 'ISO-8859-1'),
                "expected" => false,
            ],
            // considered like unicode: UTF-8
            'UTF-16 (LE) string without accent' => [
                'value'    => mb_convert_encoding("a", 'UTF-16LE', 'ISO-8859-1'),
                "expected" => true,
            ],
        ];
    }

    /**
     * @param string $value
     * @param bool   $expected
     *
     * @dataProvider providerTestIsUTF8
     *
     * @return void
     */
    public function testIsUTF8(string $value, bool $expected): void
    {
        $this->assertEquals($expected, CMbString::isUTF8($value));
    }

    public function providerTestIsIntranetIP(): array
    {
        return [
            '10.0.0.0'        => [
                'value'    => '10.0.0.0',
                "expected" => true,
            ],
            '127.0.0.1'       => [
                'value'    => '127.0.0.1',
                "expected" => true,
            ],
            '172.16.0.0'      => [
                'value'    => '172.16.0.0',
                "expected" => true,
            ],
            '192.168.0.0 '    => [
                'value'    => '192.168.0.0',
                "expected" => true,
            ],
            '80.125.126.127'  => [
                'value'    => '80.125.126.127',
                "expected" => false,
            ],
            '186.187.188.189' => [
                'value'    => '186.187.188.189',
                "expected" => false,
            ],
        ];
    }

    /**
     * @param string $address
     * @param bool   $expected
     *
     * @dataProvider providerTestIsIntranetIP
     *
     * @return void
     */
    public function testIsIntranetIP(string $address, bool $expected): void
    {
        $this->assertEquals($expected, CMbString::isIntranetIP($address));
    }

    /**
     * @param array  $components
     * @param string $expected
     *
     * @return void
     */
    public function testMakeUrlFromComponents(): void
    {
        $components = [
            "scheme"   => "http",
            "host"     => "lorem",
            "user"     => "toto",
            "pass"     => "tata",
            "port"     => "1234",
            "path"     => "/ipsum",
            "query"    => "search=foo",
            "fragment" => "bar",
        ];

        $this->assertEquals(
            "http://toto:tata@lorem:1234/ipsum?search=foo#bar",
            CMbString::makeUrlFromComponents($components)
        );
    }
}
