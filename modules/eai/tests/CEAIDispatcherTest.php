<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tests\Unit;

use Ox\Core\CMbString;
use Ox\Core\CMbXMLDocument;
use Ox\Interop\Eai\CEAIDispatcher;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

class CEAIDispatcherTest extends OxUnitTestCase
{
    /**
     * @param string|null $encoding
     *
     * @return string
     */
    private function getXml(?string $encoding = 'UTF-8'): string
    {
        $content = "<accentued_tag_È>content with È‡Ë</accentued_tag_È>";

        return $encoding ? "<?xml version='1.0' encoding='$encoding'?>$content" : $content;
    }

    /**
     * Convert in utf8 encoding
     *
     * @param string $content
     *
     * @return string
     */
    private function utf8EncodeContent(string $content): string
    {
        return mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
    }

    /**
     * @param string|null $encoding
     *
     * @return string
     */
    private function getHL7v2(?string $encoding = 'UNICODE UTF-8'): string
    {
        return "MSH|^~\&|foobarÈ^000001^EUI-64|foo|||" .
            "20220310100448.0000+0100||ORU^R01^ORU_R01|483|P|2.6|||AL|NE||$encoding";
    }

    public function providerDecodeUTF8XML(): array
    {
        return [
            "Content XML with UTF-8 encoding defined" => [
                $this->utf8EncodeContent($this->getXml('UTF-8')),
                'iso-8859-1',
            ],
            "Content XML with utf-8 encoding defined" => [
                $this->utf8EncodeContent($this->getXml('utf-8')),
                'iso-8859-1',
            ],
            "Content XML with utf8 encoding defined" => [
                $this->utf8EncodeContent($this->getXml('utf8')),
                'iso-8859-1',
            ],
            "Content XML with iso-8859-1 encoding defined" => [
                $this->utf8EncodeContent($this->getXml('iso-8859-1')),
                'iso-8859-1',
            ],
            "Content XML with ISO-8859-1 encoding defined" => [
                $this->utf8EncodeContent($this->getXml('ISO-8859-1')),
                'iso-8859-1',
            ],
        ];
    }

    /**
     * @param string $content
     * @param string $expected_encode
     * @param bool   $is_utf8
     *
     * @dataProvider providerDecodeUTF8XML
     *
     * @return void
     * @throws TestsException
     * @throws ReflectionException
     */
    public function testDecodeUTF8XML(string $content, string $expected_encode): void
    {
        $eai_dispatcher = new CEAIDispatcher();


        $content = $this->invokePrivateMethod($eai_dispatcher, 'decodeUTF8', $content);

        $this->assertFalse(CMbString::isUTF8($content));
        $this->assertEquals($expected_encode, strtolower(CMbXMLDocument::getDefinedEncoding($content)));
    }

    /**
     * @return array[]
     */
    public function providerDecodeUTF8HL7v2(): array
    {
        return [
            "Content HL7v2 with UNICODE UTF-8 encoding defined" => [
                $this->utf8EncodeContent($this->getHL7v2()),
                '8859/1',
            ],
            "Content HL7v2 with 8859/1 encoding defined"        => [
                $this->utf8EncodeContent($this->getHL7v2('8859/1')),
                '8859/1',
            ],
        ];
    }

    /**
     * @param string $content
     * @param string $expected_encode
     * @param bool   $is_utf8
     *
     * @dataProvider providerDecodeUTF8HL7v2
     *
     * @return void
     * @throws TestsException
     * @throws ReflectionException
     */
    public function testDecodeUTF8HL7v2(string $content, string $expected_encode): void
    {
        $eai_dispatcher = new CEAIDispatcher();
        $content        = $this->invokePrivateMethod($eai_dispatcher, 'decodeUTF8', $content);

        $this->assertFalse(CMbString::isUTF8($content));
        $this->assertTrue(str_ends_with($content, $expected_encode));
    }
}
