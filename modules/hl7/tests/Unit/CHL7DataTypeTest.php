<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Tests\Unit;

use Exception;
use Ox\Interop\Hl7\CHL7v2;
use Ox\Interop\Hl7\CHL7v2DataType;
use Ox\Interop\Hl7\CHL7v2DOMDocument;
use Ox\Interop\Hl7\CHL7v2Field;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Tests\OxUnitTestCase;

class CHL7DataTypeTest extends OxUnitTestCase
{
    private function testDataType(
        array $tab,
        string $datatype_type,
        string $version = '2.5',
        string $extension = 'none'
    ): void {
        $dummy_doc = new CHL7v2DOMDocument();
        $dummy_doc->registerNodeClass("DOMElement", "CHL7v2DOMElement");
        $dummy_doc->loadXML('<?xml version="1.0" ?><root/>');
        $dummy_element = $dummy_doc->documentElement;

        $dummy_message = new CHL7v2Message();
        $dummy_segment = new CHL7v2Segment($dummy_message);
        $dummy_field   = new CHL7v2Field($dummy_segment, $dummy_element);

        $dt = CHL7v2DataType::load($dummy_message, $datatype_type, $version, $extension);

        foreach ($tab as $system => $tests) {
            foreach ($tests as $from => $to) {
                $method = ($system == "MB" ? "toMB" : "toHL7");
                $result = null;

                try {
                    $result = $dt->$method($from, $dummy_field);
                } catch (Exception $e) {
                    $result = $e;
                }

                $this->assertEquals($result, $to);
            }
        }
    }

    private function testAllVersions(array $tab, string $datatype_type): void
    {
        // Test international
        foreach (CHL7v2::getInternationalVersions() as $_version) {
            $this->testDataType($tab, $datatype_type, $_version);
        }

        // Test FRA
        foreach (CHL7v2::getFRAVersions() as $_version) {
            $this->testDataType($tab, $datatype_type, '2.5', $_version);
        }
    }

    public function testDate(): void
    {
        $tab = [
            "MB"  => [
                "20110829" => "2011-08-29",
                "201108"   => "2011-08-00",
                "2011"     => "2011-00-00",
                "2011082"  => null,
            ],
            "HL7" => [
                "2011-08-29" => "20110829",
                "2011-08-00" => "201108",
                "2011-00-00" => "2011",
            ],
        ];

        $this->testAllVersions($tab, 'Date');
    }

    public function testDateTime(): void
    {
        $tab = [
            "MB"  => [
                "20110829140306.0052" => "2011-08-29 14:03:06",
                "20110829140306"      => "2011-08-29 14:03:06",
                "201108291403"        => "2011-08-29 14:03:00",
                "2011082914"          => "2011-08-29 14:00:00",
                "20110829"            => "2011-08-29",
                "201108"              => "2011-08-00",
                "2011"                => "2011-00-00",
                "20110829140360.0052" => null,
            ],
            "HL7" => [
                "2011-08-29 14:03:06" => "20110829140306",
                "2011-08-29T14:03:06" => "20110829140306",
                "2011-08-29 14:03:00" => "20110829140300",
            ],
        ];

        $this->testAllVersions($tab, 'DateTime');
    }

    public function testTime(): void
    {
        $tab = [
            "MB"  => [
                "140306.0052" => "14:03:06",
                "140306"      => "14:03:06",
                "1403"        => "14:03:00",
                "14"          => "14:00:00",
            ],
            "HL7" => [
                "14:03:06" => "140306",
                "14:03:00" => "140300",
                "14:00:00" => "140000",
                "24:00:00" => null,
            ],
        ];

        $this->testAllVersions($tab, 'Time');
    }

    public function testInteger(): void
    {
        $tab = [
            "MB"  => [
                "16512"   => 16512,
                "16512.5" => null,
                "009"     => 9,
                "foo"     => null,
            ],
            "HL7" => [
                "16512"   => 16512,
                "16512.5" => 16512,
                "009"     => 9,
                "foo"     => null,
            ],
        ];

        $this->testAllVersions($tab, 'Integer');
    }

    public function testDouble(): void
    {
        $tab = [
            "MB"  => [
                "16512"   => 16512.0,
                "16512.5" => 16512.5,
                "16512,5" => null,
                "009"     => 9.0,
                "foo"     => null,
            ],
            "HL7" => [
                "16512"   => 16512,
                "16512.5" => 16512.5,
                "16512,5" => 16512,
                "009"     => 9.0,
                "foo"     => null,
            ],
        ];

        $this->testAllVersions($tab, 'Double');
    }
}
