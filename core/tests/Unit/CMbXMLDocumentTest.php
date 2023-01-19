<?php
/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


namespace Ox\Core\Tests\Unit;

use Ox\Core\CMbString;
use Ox\Core\CMbXMLDocument;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

/**
 * Description
 */
class CMbXMLDocumentTest extends OxUnitTestCase
{
    /**
     * @param string|null $encoding
     *
     * @return string
     */
    private function getXml(?string $encoding = 'UTF-8'): string
    {
        $content = "<accentued_tag_י>content with יאט</accentued_tag_י>";

        return $encoding ? "<?xml version='1.0' encoding='$encoding'?>$content" : $content;
    }

    /**
     * @return array[]
     */
    public function providerCheckConformityEncoding(): array
    {
        $content_defined_iso             = $this->getXml('iso-8859-1');
        $capitalized_content_defined_iso = $this->getXml('ISO-8859-1');

        $content_defined_utf8             = $this->getXml('utf-8');
        $capitalized_content_defined_utf8 = $this->getXml('UTF8');

        return [
            // utf-8
            'Correct encoding utf-8 for xml content'        => [
                mb_convert_encoding(
                    $content_defined_utf8,
                    'UTF-8',
                    'ISO-8859-1'
                ),
                true,
            ],
            'Incorrect encoding utf-8 for xml content'      => [$content_defined_utf8, true],
            'Incorrect encoding UTF-8 for xml content'      => [$capitalized_content_defined_utf8, true],

            // iso
            'Correct encoding iso-8859-1 for xml content'   => [$content_defined_iso, false],
            'Incorrect encoding iso-8859-1 for xml content' => [
                mb_convert_encoding(
                    $content_defined_iso,
                    'UTF-8',
                    'ISO-8859-1'
                ),
                false,
            ],
            'Incorrect encoding ISO-8859-1 for xml content' => [
                mb_convert_encoding(
                    $capitalized_content_defined_iso,
                    'UTF-8',
                    'ISO-8859-1'
                ),
                false,
            ],
        ];
    }

    /**
     * @param string $content
     * @param bool   $expected_utf8_encoding
     *
     * @dataProvider providerCheckConformityEncoding
     *
     * @return void
     * @throws TestsException
     * @throws ReflectionException
     */
    public function testCheckConformityEncoding(string $content, bool $expected_utf8_encoding): void
    {
        $xml_document = new CMbXMLDocument();

        $content = $this->invokePrivateMethod($xml_document, "checkConformityEncoding", $content);

        $this->assertEquals($expected_utf8_encoding, CMbString::isUTF8($content));
    }

    /**
     * @param string $content
     * @param bool   $expected_utf8_encoding
     *
     * @dataProvider providerCheckConformityEncoding
     *
     * @return void
     * @throws TestsException
     * @throws ReflectionException
     */
    public function testLoadXml(string $content, bool $expected_utf8_encoding): void
    {
        $xml_document = new CMbXMLDocument();
        $errors       = $xml_document->loadXML($content, null, true);

        $this->assertEquals('', $errors);
    }

    /**
     * @return array[][]
     */
    public function providerGetDefinedEncoding(): array
    {
        return [
            "With nothing"             => [$this->getXml(null), 'UTF-8'],
            "With invalid content"     => [$this->getXml(), 'UTF-8'],
            "With encoding utf8"       => [$this->getXml('utf8'), 'utf8'],
            "With encoding utf-8"      => [$this->getXml('utf-8'), 'utf-8'],
            "With encoding iso-8859-1" => [$this->getXml('iso-8859-1'), 'iso-8859-1'],
            "With encoding ISO-8859-1" => [$this->getXml('ISO-8859-1'), 'ISO-8859-1'],
        ];
    }

    /**
     * @param string $content
     * @param string $expected
     *
     * @dataProvider providerGetDefinedEncoding
     *
     * @return void
     */
    public function testGetDefinedEncoding(string $content, string $expected): void
    {
        $actual = CMbXMLDocument::getDefinedEncoding($content);
        $this->assertEquals($expected, $actual);
    }
}
