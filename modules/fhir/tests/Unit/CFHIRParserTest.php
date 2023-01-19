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

use Exception;
use Ox\Core\CMbDT;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\R4\Bundle\CFHIRResourceBundle;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\InteropSante\CFHIRResourcePractitionerFR;
use Ox\Interop\Fhir\Serializers\CFHIRParser;
use Ox\Interop\Fhir\Serializers\CFHIRSerializer;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CFHIRParserTest extends OxUnitTestCase
{
    private const TEST_DATATYPE_CHOICE_DATETIME = '2020-01-01 10:00:00';

    /** @var string */
    private static $dir_resources;

    private function getDirResources(?string $dir = null): string
    {
        if (self::$dir_resources) {
            return $dir ? self::$dir_resources . "$dir/" : self::$dir_resources;
        }

        self::$dir_resources = $root = dirname(__DIR__, 1) . '/' . 'Resources/';

        return $dir ? $root . "$dir/" : $root;
    }

    private function getResourceTestDatatypeChoice(bool $decease_date = false): CFHIRResourcePatient
    {
        $resource = new CFHIRResourcePatient();

        $deceased = $decease_date
            ? new CFHIRDataTypeDateTime(CMbDT::dateTime(self::TEST_DATATYPE_CHOICE_DATETIME))
            : new CFHIRDataTypeBoolean(false);
        $resource->setDeceased($deceased);

        $serializer = CFHIRSerializer::serialize($resource, 'xml');
        $xml        = $serializer->getResourceSerialized();
        $parser     = CFHIRParser::parse($xml);

        return $parser->getResource();
    }

    public function testParsingDatatypeChoiceNotNull(): void
    {
        $resource = $this->getResourceTestDatatypeChoice();

        $this->assertNotNull($resource->getDeceased(), 'The field "deceased" should be not null after parsing data');
    }

    public function providerParsingDatatypeChoiceType(): array
    {
        return [
            'Deceased[bool]'     => [
                'expected' => CFHIRDataTypeBoolean::class,
                'resource' => $this->getResourceTestDatatypeChoice(),
            ],
            'Deceased[datetime]' => [
                'expected' => CFHIRDataTypeDateTime::class,
                'resource' => $this->getResourceTestDatatypeChoice(true),
            ],
        ];
    }

    /**
     * @dataProvider providerParsingDatatypeChoiceType
     */
    public function testParsingDatatypeChoiceType(string $expected, CFHIRResourcePatient $resource): void
    {
        $this->assertInstanceOf(
            $expected,
            $resource->getDeceased(),
            'The field "deceased" should be an object instance of ' . $expected
        );
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function providerParsingDatatypeChoiceValue(): array
    {
        return [
            'Deceased[bool]'     => [
                'expected' => false,
                'resource' => $this->getResourceTestDatatypeChoice(),
            ],
            'Deceased[datetime]' => [
                'expected' => CFHIR::getTimeUtc(CMbDT::dateTime(self::TEST_DATATYPE_CHOICE_DATETIME), false),
                'resource' => $this->getResourceTestDatatypeChoice(true),
            ],
        ];
    }

    /**
     * @param mixed                $expected
     * @param CFHIRResourcePatient $resource
     *
     * @dataProvider providerParsingDatatypeChoiceValue
     */
    public function testParsingDatatypeChoiceValue($expected, CFHIRResourcePatient $resource): void
    {
        $this->assertEquals(
            $expected,
            $resource->getDeceased()->getValue(),
            'The value set on deceased should be of type "' . gettype(
                $expected
            ) . '" and should be equals to : ' . $expected
        );
    }

    public function testParsingDatatypeResource(): void
    {
        $patient         = new CFHIRResourcePatient();
        $patient->setActive(new CFHIRDataTypeBoolean(true));
        $bundle          = new CFHIRResourceBundle();
        $bundle->addResource($patient);

        $serializer = CFHIRSerializer::serialize($bundle, 'xml');
        $xml        = $serializer->getResourceSerialized();

        $parser = CFHIRParser::parse($xml);
        /** @var CFHIRResourceBundle $bundle */
        $bundle = $parser->getResource();

        $this->assertCount(1, $bundle->getEntry(), 'The bundle entry should have only 1 entry');
        $this->assertNotNull($bundle->getEntry()[0]->resource, 'The bundle entry should have a resource');
        $this->assertEquals(false, $bundle->getEntry()[0]->resource->isResourceNotSupported());
        $this->assertEquals(
            CFHIRResourcePatient::RESOURCE_TYPE,
            $bundle->getEntry()[0]->resource->getValue()::RESOURCE_TYPE
        );
    }

    public function providerParsingDatatypeResourceNotManaged(): array
    {
        $xml = '<Bundle xmlns="http://hl7.org/fhir"><entry><resource>
                <ResourceNotManaged xmlns="http://hl7.org/fhir"></ResourceNotManaged></resource></entry></Bundle>';

        $json = '{"resourceType": "Bundle", "entry": [{"resource": {"resourceType": "ResourceNotManaged"}}]}';

        return ['json' => ['content' => $json], 'xml' => ['content' => $xml]];
    }

    /**
     * @param string $content
     *
     * @dataProvider providerParsingDatatypeResourceNotManaged
     */
    public function testParsingDatatypeResourceNotManaged(string $content): void
    {
        $parser = CFHIRParser::parse($content);
        /** @var CFHIRResourceBundle $bundle */
        $bundle = $parser->getResource();

        $this->assertCount(1, $bundle->getEntry(), 'The bundle entry should have only 1 entry');
        $this->assertNotNull($bundle->getEntry()[0]->resource, 'The bundle entry should have a resource');
        $this->assertTrue(
            $bundle->getEntry()[0]->resource->isResourceNotSupported(),
            'The bundle entry should have a resource which is not managed'
        );
    }

    public function providerParsingDatatypeComplex(): array
    {
        $filename = self::getDirResources('Parser') . 'patient_complex_type';

        $data = [];
        foreach (['json', 'xml'] as $format) {
            $content = file_get_contents("$filename.$format");
            $parser  = CFHIRParser::parse($content);

            /** @var CFHIRResourcePatient $patient */
            $patient  = $parser->getResource();
            $codeable = $patient->getMaritalStatus();

            $data["[$format] Patient.maritalStatus.coding.display"] = [
                'expected' => "input display",
                'value'    => $codeable->coding[0]->display->getValue(),
            ];

            $data["[$format] Patient.maritalStatus.coding.system"] = [
                'expected' => "input system",
                'value'    => $codeable->coding[0]->system->getValue(),
            ];

            $data["[$format] Patient.maritalStatus.coding.code"] = [
                'expected' => "input code",
                'value'    => $codeable->coding[0]->code->getValue(),
            ];

            $data["[$format] Patient.maritalStatus.text"] = [
                'expected' => "text coding",
                'value'    => $codeable->text->getValue(),
            ];
        }

        return $data;
    }

    /**
     * @param string $expected
     * @param string $value
     *
     * @dataProvider providerParsingDatatypeComplex
     */
    public function testParsingDatatypeComplex(string $expected, string $value): void
    {
        $this->assertEquals($expected, $value);
    }

    /**
     * @return array[]
     */
    public function providerParsingDatatypeElement(): array
    {
        $filename = self::getDirResources('Parser') . 'patient_datatype_element';

        $data = [];
        foreach (['json', 'xml'] as $format) {
            $content       = file_get_contents("$filename.$format");
            $data[$format] = ['content' => $content];
        }

        return $data;
    }

    /**
     * @param string $content
     *
     * @dataProvider providerParsingDatatypeElement
     */
    public function testParsingDatatypeElement(string $content): void
    {
        $parser = CFHIRParser::parse($content);
        /** @var CFHIRResourcePatient $patient */
        $patient = $parser->getResource();

        $this->assertEquals(
            true,
            $patient->getActive()->getValue(),
            'The resource should be have a value boolean with true'
        );
        $this->assertEquals(1, $patient->getActive()->id->getValue(), 'The resource should be have a field active with an');
        $this->assertCount(
            1,
            $patient->getActive()->extension,
            'The resource should be have a field active with an extension'
        );
        $this->assertEquals(
            'extension',
            $patient->getActive()->extension[0]->url->getValue(),
            'The resource should be have a field active with an extension with an url'
        );
    }

    /**
     * @return string[][]
     */
    public function providerParsingResource(): array
    {
        $resources = [
            'xml'  => [
                'patient' => '<Patient xmlns="http://hl7.org/fhir"></Patient>',
                'bundle'  => '<Bundle xmlns="http://hl7.org/fhir"></Bundle>',
            ],
            'json' => [
                'patient' => '{"resourceType": "Patient"}',
                'bundle'  => '{"resourceType": "Bundle"}',
            ],
        ];

        $data = [];
        foreach (['json', 'xml'] as $format) {
            $data["[$format] " . CFHIRResourceBundle::class] = [
                'resource' => $resources[$format]['bundle'],
                'expected' => CFHIRResourceBundle::class,
            ];

            $data["[$format] " . CFHIRResourcePatient::class] = [
                'resource' => $resources[$format]['patient'],
                'expected' => CFHIRResourcePatient::class,
            ];
        }

        return $data;
    }

    /**
     * @param string $content
     * @param string $expected
     *
     * @dataProvider providerParsingResource
     */
    public function testParsingResource(string $content, string $expected): void
    {
        $parser = CFHIRParser::parse($content);

        $this->assertEquals($expected, get_class($parser->getResource()));
    }

    public function providerParsingResourceProfiled(): array
    {
        $fr_practitioner = new CFHIRResourcePractitionerFR();
        $profile         = $fr_practitioner->getProfile();
        $xml             = "<Practitioner xmlns='http://hl7.org/fhir'>
                  <meta><profile value='$profile'></profile></meta></Practitioner>";
        $json            = "{\"resourceType\":\"Practitioner\",\"meta\":{\"profile\":[\"$profile\"]}}";

        return ['json' => ['data' => $json], 'xml' => ['data' => $xml]];
    }

    /**
     * @dataProvider providerParsingResourceProfiled
     */
    public function testParsingResourceProfiled(string $data): void
    {
        $parser   = CFHIRParser::parse($data);
        $resource = $parser->getResource();

        $this->assertInstanceOf(CFHIRResourcePractitionerFR::class, $resource);
    }

    public function providerParsingBackboneElement(): array
    {
        $file_name = self::getDirResources('Parser') . 'patient_backbone_element';

        $data = [];
        foreach (['json', 'xml'] as $format) {
            $content = file_get_contents("$file_name.$format");
            $parser  = CFHIRParser::parse($content);
            /** @var CFHIRResourcePatient $patient */
            $patient  = $parser->getResource();
            $language = $patient->getCommunication()[0]->language;

            $data["[$format]Patient.communication.language.display"] = [
                'expected' => "Fr",
                'value'    => $language->coding[0]->display->getValue(),
            ];

            $data["[$format]Patient.communication.language.code"] = [
                'expected' => "fr-Fr",
                'value'    => $language->coding[0]->code->getValue(),
            ];

            $data["[$format]Patient.communication.language.system"] = [
                'expected' => "input system",
                'value'    => $language->coding[0]->system->getValue(),
            ];

            $data["[$format]Patient.communication.preferred"] = [
                'expected' => true,
                'value'    => $patient->getCommunication()[0]->preferred->getValue(),
            ];
        }

        return $data;
    }

    /**
     * @param mixed $expected
     * @param mixed $value
     *
     * @dataProvider providerParsingBackboneElement
     */
    public function testParsingBackboneElement($expected, $value): void
    {
        $this->assertEquals($expected, $value);
    }

    public function providerParsingNotFhir(): array
    {
        $xml  = "<data xmlns='http://hl7.org/fhir'></data>";
        $json = '{"resourceType": "data"}';

        return [
            'XML'  => ['data' => $xml],
            'JSON' => ['data' => $json],
        ];
    }

    /**
     * @param string $data
     *
     * @dataProvider providerParsingNotFhir
     */
    public function testParsingNotFhir(string $data): void
    {
        $parser = CFHIRParser::parse($data);

        $this->assertNull($parser->getResource());
    }

    /**
     * @return array
     */
    public function providerParsingDatatypeExtension(): array
    {
        $file_name = self::getDirResources('Parser') . 'patient_extension_field';

        $data = [];
        foreach (['xml', 'json'] as $format) {
            $content = file_get_contents("$file_name.$format");
            /** @var CFHIRResourcePatient $patient */
            $parser  = CFHIRParser::parse($content);
            $patient = $parser->getResource();

            $data["[$format] Patient.active.id"] = [
                'expected' => 1,
                'actual'   => $patient->getActive()->id->getValue(),
            ];

            $data["[$format] Patient.active.value"] = [
                'expected' => 1,
                'actual'   => $patient->getActive()->getValue(),
            ];

            $data["[$format] Patient.active.extension.url"] = [
                'expected' => 'extension',
                'actual'   => $patient->getActive()->extension[0]->url->getValue(),
            ];

            $data["[$format] Patient.active.extension.extension.url"] = [
                'expected' => 'foo',
                'actual'   => $patient->getActive()->extension[0]->extension[0]->url->getValue(),
            ];

            $data["[$format] Patient.active.extension.extension.valueString"] = [
                'expected' => 'value',
                'actual'   => $patient->getActive()->extension[0]->extension[0]->value->getValue(),
            ];
        }

        return $data;
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     *
     * @dataProvider providerParsingDatatypeExtension
     */
    public function testParsingDatatypeExtension($expected, $actual): void
    {
        $this->assertEquals($expected, $actual);
    }
}
