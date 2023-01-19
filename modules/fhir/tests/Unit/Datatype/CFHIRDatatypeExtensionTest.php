<?php
/**
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Description
 */

namespace Ox\Interop\Fhir\Tests\Unit;

use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CFHIRDatatypeExtensionTest extends OxUnitTestCase
{
    /** @var string */
    private static $dir_resources;

    /**
     * @param string|null $dir
     *
     * @return string
     */
    private function getDirResources(?string $dir = null): string
    {
        if (self::$dir_resources) {
            return $dir ? self::$dir_resources . "$dir/" : self::$dir_resources;
        }

        self::$dir_resources = $root = dirname(__DIR__, 2) . '/' . 'Resources/';

        return $dir ? $root . "$dir/" : $root;
    }

    public function providerToJson(): array
    {
        return [
            'Extension - value[primitive]'                  => $this->getDataTestJson('t1'),
            'Extension - value[complex]'                    => $this->getDataTestJson('t2'),
            'Extension - extension[0] - value[primitive]'   => $this->getDataTestJson('t3'),
            'Extension - extension[1] - value[complex]'     => $this->getDataTestJson('t4'),
            'Extension - value[primitive] - id & extension' => $this->getDataTestJson('t5'),
        ];
    }

    /**
     * @param CFHIRDataType $datatype
     * @param string        $content
     *
     * @dataProvider providerToJson
     */
    public function testToJson(CFHIRDataType $datatype, string $content): void
    {
        $actual = json_encode($datatype->toJSON('extension')['extension']);

        $this->assertJsonStringEqualsJsonString($content, $actual);
    }

    /**
     * @param string $test_name
     *
     * @return array
     */
    private function getDataTestJson(string $test_name): array
    {
        $dir       = self::getDirResources('Datatype');
        $file_name = $dir . "datatype_extension_$test_name.json";
        $content   = file_get_contents($file_name);

        $datatype      = new CFHIRDatatypeExtension();

        $url = new CFHIRDataTypeUri('extension url');
        $value = new CFHIRDataTypeString('value');
        $coding = CFHIRDataTypeCoding::build(
            [
                'system'  => 'system',
                'code'    => 'code',
                'display' => 'display',
            ]
        );

        $datatype->url = $url;
        switch ($test_name) {
            case 't1':
                $datatype->value = new CFHIRDataTypeString('0');
                break;

            case 't2':
                $datatype->value = $coding;
                break;

            case 't3':
                $datatype->extension[] = CFHIRDataTypeExtension::build(
                    [
                        'url' => 'extension url',
                        'value' => 'value'
                    ]
                );
                break;

            case 't4':
                $datatype->extension[] = CFHIRDataTypeExtension::build(
                    [
                        'url' => 'extension url',
                        'value' => $coding
                    ]
                );
                break;

            case 't5':
                $datatype->value            = $value;
                $datatype->value->id        = new CFHIRDataTypeString('id');
                $datatype->value->extension[] = CFHIRDataTypeExtension::build(
                    ['url' => 'extension url', 'value' => 'value']
                );
                break;

            default:
                $datatype = null;
        }

        return ['datatype' => $datatype, 'content' => $content];
    }
}
