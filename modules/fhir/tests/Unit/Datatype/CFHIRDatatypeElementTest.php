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
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Interop\Fhir\Utilities\CFHIRTools;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CFHIRDatatypeElementTest extends OxUnitTestCase
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
            'Complex - code' => $this->getDataTestJson('t1', false),
            'Complex - str'  => $this->getDataTestJson('t2', false),
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
        $actual = json_encode($datatype->toJSON($datatype::NAME));

        $this->assertJsonStringEqualsJsonString($content, $actual);
    }

    public function providerToJsonArray(): array
    {
        return [
            'str[]'                => array_merge($this->getDataTestJson('t3'), ['field' => 'string']),
            'str[] - extension'    => array_merge($this->getDataTestJson('t4'), ['field' => 'string']),
            'str[] - extension[1]' => array_merge($this->getDataTestJson('t5'), ['field' => 'string']),
            'str[1] - extension[]' => array_merge($this->getDataTestJson('t6'), ['field' => 'string']),
        ];
    }

    /**
     * @param CFHIRDataType $datatype
     * @param string        $content
     *
     * @dataProvider providerToJsonArray
     */
    public function testToJsonArray(array $datatypes, string $content, string $field): void
    {
        $actual = json_encode(CFHIRTools::manageDatatypeJSONArray($datatypes, $field));

        $this->assertJsonStringEqualsJsonString($content, $actual);
    }

    /**
     * @param string $test_name
     *
     * @return array
     */
    private function getDataTestJson(string $test_name, bool $file = true): array
    {
        $dir       = self::getDirResources('Datatype');
        $file_name = $dir . "datatype_element_$test_name.json";
        $content   = $file ? file_get_contents($file_name) : '';

        $url   = new CFHIRDataTypeUri('extension url');
        $value = new CFHIRDataTypeString('value');
        switch ($test_name) {
            case 't1':
                $datatype = new CFHIRDataTypeString('0');
                $content  = '{"String" : "0"}';
                break;

            case 't2':
                $datatype = new CFHIRDataTypeCode('value');
                $content  = '{"Code" : "value"}';
                break;

            case 't3':
                $string   = new CFHIRDataTypeString('value');
                $datatype = [$string, $string];
                break;
            case 't4':
                $string            = new CFHIRDataTypeString('value');
                $string->id        = new CFHIRDataTypeString('id');
                $string->extension[] = CFHIRDataTypeExtension::build(['url' => $url, "value" => $value]);
                $datatype          = [$string, $string];
                break;
            case 't5':
                $string            = new CFHIRDataTypeString('value');
                $string2           = new CFHIRDataTypeString('value');
                $string2->id        = new CFHIRDataTypeString('id');
                $string2->extension[] = CFHIRDataTypeExtension::build(['url' => $url, "value" => $value]);
                $datatype          = [$string, $string2];
                break;
            case 't6':
                $string            = new CFHIRDataTypeString();
                $string->id        = new CFHIRDataTypeString('id');
                $string->extension[] = CFHIRDataTypeExtension::build(['url' => $url, "value" => $value]);
                $string2           = new CFHIRDataTypeString('value');
                $string2->id        = new CFHIRDataTypeString('id');
                $string2->extension[] = CFHIRDataTypeExtension::build(['url' => $url, "value" => $value]);
                $datatype          = [$string, $string2];
                break;

            default:
                $datatype = null;
        }

        return ['datatype' => $datatype, 'content' => $content];
    }
}
