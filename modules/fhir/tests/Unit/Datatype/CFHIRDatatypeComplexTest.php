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
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Interop\Fhir\Utilities\CFHIRTools;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CFHIRDatatypeComplexTest extends OxUnitTestCase
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
            'Complex - coding'             => $this->getDataTestJson('t1'),
            'Complex - coding - extension' => $this->getDataTestJson('t2'),
            'Complex - codeable'           => $this->getDataTestJson('t5'),
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
        $actual = json_encode($datatype->toJSON($datatype::NAME)[$datatype::NAME]);

        $this->assertJsonStringEqualsJsonString($content, $actual);
    }

    public function providerToJsonArray(): array
    {
        return [
            'Complex - coding[]'               => array_merge($this->getDataTestJson('t3'), ['field' => 'coding']),
            'Complex - coding[] - extension[]' => array_merge($this->getDataTestJson('t4'), ['field' => 'coding']),
            'Complex - codeable[] - coding[0]' => array_merge(
                $this->getDataTestJson('t6'),
                ['field' => 'codeableConcept']
            ),
            'Complex - codeable[] - coding[1]' => array_merge(
                $this->getDataTestJson('t7'),
                ['field' => 'codeableConcept']
            ),
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
    private function getDataTestJson(string $test_name): array
    {
        $dir       = self::getDirResources('Datatype');
        $file_name = $dir . "datatype_complex_$test_name.json";
        $content   = file_get_contents($file_name);

        $url         = new CFHIRDataTypeUri('extension url');
        $value       = new CFHIRDataTypeString('value');
        $coding_data = [
            'system'  => 'system',
            'code'    => 'code',
            'display' => 'display',
        ];
        $coding      = CFHIRDataTypeCoding::build($coding_data);
        $codeable    = CFHIRDataTypeCodeableConcept::build(['coding' => [$coding]]);
        switch ($test_name) {
            case 't1':
                $datatype = $coding;
                break;

            case 't2':
                $datatype                      = $coding;
                $datatype->system->id          = new CFHIRDataTypeString('id');
                $datatype->system->extension[] = CFHIRDataTypeExtension::build(['url' => $url, 'value' => $value]);
                break;

            case 't3':
                $datatype = [$coding, $coding];
                break;

            case 't4':
                $coding->id         = new CFHIRDataTypeString('id');
                $coding2            = CFHIRDataTypeCoding::build($coding_data);
                $coding2->id        = new CFHIRDataTypeString('id');
                $coding2->extension = CFHIRDataTypeExtension::build(['url' => $url, 'value' => $value,]);
                $datatype           = [$coding, $coding2];
                break;
            case 't5':
                $datatype       = $codeable;
                $datatype->text = $value;
                break;
            case 't6':
                $datatype = [$codeable, $codeable];
                break;
            case 't7':
                $codeable2 = CFHIRDataTypeCodeableConcept::build(
                    ['coding' => [CFHIRDataTypeCoding::build($coding_data)]]
                );

                $codeable2->coding[0]->id        = new CFHIRDataTypeString('id');
                $codeable2->coding[0]->extension = CFHIRDataTypeExtension::build(['url' => $url, 'value' => $value]);
                $datatype                        = [$codeable, $codeable2];
                break;

            default:
                $datatype = null;
        }

        return ['datatype' => $datatype, 'content' => $content];
    }
}
