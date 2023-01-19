<?php
/**
 * @package Core\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tests\Unit\Transformations;

use Ox\Core\CMbArray;
use Ox\Core\CMbXPath;
use Ox\Interop\Eai\Transformations\CTransformationRule;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CAppTest
 * @package Ox\Core\Tests\Unit
 */
class CTransformationRuleTest extends OxUnitTestCase
{
    /** @var string */
    private const EXAMPLE_MESSAGE = 'MSH|^~\&|Mediboard|mediboard|Tiers|TIERS|20210219150153||ADT^A28^ADT_A05|9448|D|2.5^FRA^2.5|||||17|8859/1|FR||
EVN||20210219150153| test_trim ||||
PID|1||322921^^^MB&1.2.250.1.2.3.5&OX^RI||Test^1444^^^M.^^D^A~Test^1444^^^M.^^L^A||19300913|M||||||||S||||||||||||||N|||20210219145929||||||
PV1|1|N';

    private const EXAMPLE_MESSAGE_ORU = 'MSH|^~\&|Mediboard|mediboard|Tiers|TIERS|20210909000633||ORU^R01|2616863|P|2.5.1||||||8859
PID|1||322921^^^MB&1.2.250.1.2.3.5&OX^RI||Test^1444^^^M.^^D^A~Test^1444^^^M.^^L^A||19300913|M||||||||S||||||||||||||N|||20210219145929||||||
PV1|1|I|^4120||||||||||||||||
ORC|NW|BA21360582-1^TEST|BA21360582-1^TEST|BA21360582^TEST|IP|20210909000609|^^^^^R||20210909000633|||5511^^^^^^^^^^^TEST||||||||||||
OBR|1|BA21360582-1^TEST|BA21360582-1^TEST|MECH^RESULTAT DE CULTURE APRES 5 JOURS:^CULT_TEST|||||||||Hémoc N°1  PAC Chambre Implantable ||_H^HEMOCULTURE AERO - ANAEROBIE^TEST|5511^^^^^^^^^^^TEST||||||||MB||11475-1&MICROORGANISM IDENTIFIED&LN&MECH&RESULTAT DE CULTURE APRES 5 JOURS:&CULT_TEST^1|^^^^^R||||||||||||||||||||
OBX|0|RP|11502-2^Compte rendu du laboratoire [Recherche] Consultation ; Patient ; Document^LN||toto.pdf||||||F|||202110131426
SPM|1|^212521000301&TEST||H^^TEST|||||||||||||20210908230000|20210909000609||Y|
OBX|1|CE|11475-1^MICROORGANISM IDENTIFIED^LN^MECH^RESULTAT DE CULTURE APRES 5 JOURS:^CULT_TEST|1|^^^^||||||I|||||^^|||
OBX|1|ED|CRSGL1^Compte-rendu pdf^TEST||^Application^PDF^Base64^JVBE*******************||||||F|||||
OBX|0|RP|11502-2^Compte rendu du laboratoire [Recherche] Consultation ; Patient ; Document^LN||toto.pdf||||||F|||202110131426
ORC|PE|2^Labo|BA21360222|10^Labo|||||202111201426|^NURSE^JANET||||||||||||||||||||||||||||10^Labo
OBR|2|2^Labo||2164-2^Créatinine clairance [Volume/Temps] 24H ; Urine+Sérum/Plasma ; Numérique^LN||||||^UNDEUX^JO||||||^NEPH^^^^DR
OBX|0|NM|2164-2^Créatinine clairance [Volume/Temps] 24H ; Urine+Sérum/Plasma ; Numérique^LN||179|mL/min|||||F|||202111201426
OBX|1|NM|2164-2^Créatinine clairance [Volume/Temps] 24H ; Urine+Sérum/Plasma ; Numérique^LN||132|mL/min|||||F|||202111201426
OBX|2|NM|2164-2^Créatinine clairance [Volume/Temps] 24H ; Urine+Sérum/Plasma ; Numérique^LN||129|mL/min|||||F|||202111201426
ORC|NW|4^Labo|BA21360111^TEST|10^Labo|||||202110131426|^NURSE^JANET||||||||||||||||||||||||||||10^Labo
OBR|4|4^Labo||11502-2^Compte rendu du laboratoire [Recherche] Consultation ; Patient ; Document^LN||||||^UNDEUX^JO||||||^NEPH^^^^DR
OBX|0|RP|11502-2^Compte rendu du laboratoire [Recherche] Consultation ; Patient ; Document^LN||toto.pdf||||||F|||202110131426';

    public function providerLowerTransformation(): array
    {
        return [
            "LOWER FIELD" => [
                'xpath_request' => 'MSH/MSH.3',
                'expected'      => 'mediboard'
            ],
            "LOWER FIELD 2" => [
                'xpath_request' => 'MSH/MSH.4',
                'expected'      => 'mediboard'
            ],
        ];
    }

    public function providerUpperTransformation(): array
    {
        return [
            "UPPER FIELD" => [
                'xpath_request' => 'MSH/MSH.5',
                'expected'      => 'TIERS'
            ],
            "UPPER FIELD 2" => [
                'xpath_request' => 'MSH/MSH.6',
                'expected'      => 'TIERS'
            ],
        ];
    }

    public function providerInsertTransformation(): array
    {
        return [
            "INSERT FIELD" => [
                'xpath_request' => 'EVN/EVN.1',
                'insert_value ' => 'insertion_value',
                'expected'      => 'insertion_value'
            ],
            "INSERT FIELD 2" =>[
                'xpath_request' => 'EVN/EVN.2',
                'insert_value ' => 'insertion_value',
                'expected'      => 'insertion_value'
            ],
        ];
    }

    public function providerDeleteTransformation(): array
    {
        return [
            "DELETE FIELD" => [
                'xpath_request' => 'EVN/EVN.2',
            ],
            "DELETE SEGMENT" => [
                'xpath_request' => 'PV1'
            ]
        ];
    }

    public function providerDeleteComplexeTransformation(): array
    {
        return [
            "DELETE COMPLEXE SEGMENT" => [
                'xpath_request' => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/OBX',
            ],
            "DELETE COMPLEXE FIELD" => [
                'xpath_request' => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORU_R01.OBSERVATION/OBX/OBX.2',
            ],
        ];
    }

    public function providerMapTransformation(): array
    {
        return [
            "MAP" => [
                'xpath_request' => 'EVN/EVN.2',
                'map_value'    => '"20210219150154|20210219150111,20210219150153|20210219150222,20210219150000"',
                'expected'      => '20210219150222'
            ],
        ];
    }

    public function providerTrimTransformation(): array
    {
        return [
            "TRIM" => [
                'xpath_request' => 'EVN/EVN.3',
                'method'        => 'trim',
                'expected'      => 'test_trim'
            ],
            "RTRIM" => [
                'xpath_request' => 'EVN/EVN.3',
                'method'        => 'rtrim',
                'expected'      => ' test_trim'
            ],
            "LTRIM" => [
                'xpath_request' => 'EVN/EVN.3',
                'method'        => 'ltrim',
                'expected'      => 'test_trim '
            ],
            "Unknown TRIM" => [
                'xpath_request' => 'EVN/EVN.3',
                'method'        => 'unkown_trim',
                'expected'      => ' test_trim '
            ],
        ];
    }

    public function providerSubTransformation(): array
    {
        return [
            "SUB OK" => [
                'xpath_request' => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORU_R01.SPECIMEN/SPM/SPM.2/EIP.2/EI.2',
                'params'        => '0,-2',
                'expected'      => 'TE'
            ],
            "SUB NOTHING" => [
                'xpath_request' => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORU_R01.SPECIMEN/SPM/SPM.2/EIP.2/EI.2',
                'params'        => ',-2',
                'expected'      => 'TEST'
            ]
        ];
    }

    public function providerPadTransformation(): array
    {
        return [
            "PAD BOTH" => [
                'xpath_request' => 'MSH/MSH.3',
                'params'        => '11,"*",STR_PAD_BOTH',
                'expected'      => '*Mediboard*'
            ],
            "PAD LEFT" => [
                'xpath_request' => 'MSH/MSH.3',
                'params'        => '11,"*",STR_PAD_LEFT',
                'expected'      => '**Mediboard'
            ],
            "PAD RIGHT" => [
                'xpath_request' => 'MSH/MSH.3',
                'params'        => '11,"*",STR_PAD_RIGHT',
                'expected'      => 'Mediboard**'
            ],
            "PAD NOTHING" => [
                'xpath_request' => 'MSH/MSH.3',
                'params'        => '11,"",STR_PAD_RIGHT',
                'expected'      => 'Mediboard'
            ]
        ];
    }

    public function providerCopyTransformation(): array
    {
        return [
            "Copy All Message" => [
                'xpath_source' => 'MSH/MSH.3',
                'xpath_target' => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORC/ORC.2',
                'copy_type'    => 'all',
                'expected'     => 'Mediboard|Mediboard|Mediboard'
            ],
            "Copy Group Message" => [
                'xpath_source' => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORC/ORC.3/EI.1',
                'xpath_target' => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/OBR/OBR.1',
                'copy_type'    => 'group',
                'expected'     => 'BA21360582-1|BA21360222|BA21360111'
            ],
            "Copy Segment Message" => [
                'xpath_source' => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORC/ORC.3/EI.1',
                'xpath_target' => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORC/ORC.1',
                'copy_type'    => 'segment',
                'expected'     => 'BA21360582-1|BA21360222|BA21360111'
            ],
            "Copy Type Unknown" => [
                'xpath_source' => 'MSH/MSH.3',
                'xpath_target' => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORC/ORC.1',
                'copy_type'    => 'unknown',
                'expected'     => 'NW|PE|NW'
            ],
        ];
    }

    public function providerConcatTransformation(): array
    {
        return [
           "Concat All Message" => [
                'xpath_source'  => 'MSH/MSH.3',
                'xpath_target'  => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORC/ORC.1',
                'concat_type'   => 'all',
                'expected'      => 'NWMediboard|PEMediboard|NWMediboard'
            ],
            "Concat Group Message" => [
                'xpath_source'     => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORC/ORC.3/EI.1',
                'xpath_target'     => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/OBR/OBR.1',
                'concat_type'      => 'group',
                'expected'         => '1BA21360582-1|2BA21360222|4BA21360111'
            ],
            "Concat Segment Message" => [
                'xpath_source'       => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORC/ORC.3/EI.1',
                'xpath_target'       => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORC/ORC.1',
                'concat_type'        => 'segment',
                'expected'           => 'NWBA21360582-1|PEBA21360222|NWBA21360111'
            ],
            "Concat Type Unknown" => [
                'xpath_source'    => 'MSH/MSH.3',
                'xpath_target'    => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORC/ORC.1',
                'concat_type'     => 'unknown',
                'expected'        => 'NW|PE|NW'
            ],
        ];
    }

    public function providerGetSegmentPath(): array
    {
        return [
            "SEGMENT PATH RELATIVE" => [
                'path' => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORC/ORC.1',
                'relative' => 'true',
                'expected' => '/ORC.1',
            ],
            "SEGMENT PATH NO RELATIVE" => [
                'path' => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORC/ORC.1',
                'relative' => 'false',
                'expected' => 'ORC.1',
            ],
        ];
    }

    public function providerGetGroupPath(): array
    {
        return [
            "GROUP PATH RELATIVE" => [
                'path' => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORC/ORC.1',
                'relative' => 'true',
                'expected' => '/ORC/ORC.1',
            ],
            "GROUP PATH NO RELATIVE" => [
                'path' => 'ORU_R01.PATIENT_RESULT/ORU_R01.ORDER_OBSERVATION/ORC/ORC.1',
                'relative' => 'false',
                'expected' => 'ORC/ORC.1',
            ],
        ];
    }

    /**
     * @dataProvider providerConcatTransformation
     *
     * @param string $xpath_source
     * @param string $xpath_target
     * @param string $concat_type
     * @param string $expected
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testConcatTransformation(string $xpath_source, string $xpath_target, string $concat_type, string $expected): void
    {
        $hl7_message = new CHL7v2Message();
        $hl7_message->parse(self::EXAMPLE_MESSAGE_ORU);
        $dom = $hl7_message->toXML(null, true);

        $transformation         = new CTransformationRule();
        $transformation->params = $concat_type;
        $dom = $transformation->concatTransformation($dom, $xpath_source, $xpath_target);

        $expecteds = explode('|', $expected);

        $xpath = new CMbXPath($dom);
        $i = 0;
        foreach ($xpath->query($xpath_target) as $_node) {
            $this->assertEquals(CMbArray::get($expecteds, $i), $_node->nodeValue);
            $i++;
        }
    }

    /**
     * @dataProvider providerCopyTransformation
     *
     * @param string $xpath_source
     * @param string $xpath_target
     * @param string $copy_type
     * @param string $expected
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testCopyTransformation(string $xpath_source, string $xpath_target, string $copy_type, string $expected): void
    {
        $hl7_message = new CHL7v2Message();
        $hl7_message->parse(self::EXAMPLE_MESSAGE_ORU);
        $dom = $hl7_message->toXML(null, true);

        $transformation         = new CTransformationRule();
        $transformation->params = $copy_type;
        $dom = $transformation->copyTransformation($dom, $xpath_source, $xpath_target);

        $expecteds = explode('|', $expected);

        $xpath = new CMbXPath($dom);
        $i = 0;
        foreach ($xpath->query($xpath_target) as $_node) {
            $this->assertEquals(CMbArray::get($expecteds, $i), $_node->nodeValue);
            $i++;
        }
    }

    /**
     * @dataProvider providerTrimTransformation
     *
     * @param string $xpath_request
     * @param string $method
     * @param string $expected
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testTrimTransformation(string $xpath_request, string $method, string $expected): void
    {
        $hl7_message = new CHL7v2Message();
        $hl7_message->parse(self::EXAMPLE_MESSAGE);
        $dom = $hl7_message->toXML(null, true);

        $transformation         = new CTransformationRule();
        $transformation->params = $method;
        $dom = $transformation->trimTransformation($dom, $xpath_request);

        $this->assertXpathMatch($dom, $expected, "string(//$xpath_request)");
    }

    /**
     * @dataProvider providerPadTransformation
     *
     * @param string $xpath_request
     * @param string $params
     * @param string $expected
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testPadTransformation(string $xpath_request, string $params, string $expected): void
    {
        $hl7_message = new CHL7v2Message();
        $hl7_message->parse(self::EXAMPLE_MESSAGE);
        $dom = $hl7_message->toXML(null, true);

        $transformation         = new CTransformationRule();
        $transformation->params = $params;
        $dom = $transformation->padTransformation($dom, $xpath_request);

        $this->assertXpathMatch($dom, $expected, "string(//$xpath_request)");
    }

    /**
     * @dataProvider providerSubTransformation
     *
     * @param string $xpath_request
     * @param string $params
     * @param string $expected
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testSubTransformation(string $xpath_request, string $params, string $expected): void
    {
        $hl7_message = new CHL7v2Message();
        $hl7_message->parse(self::EXAMPLE_MESSAGE_ORU);
        $dom = $hl7_message->toXML(null, true);

        $transformation         = new CTransformationRule();
        $transformation->params = $params;
        $dom = $transformation->subTransformation($dom, $xpath_request);

        $this->assertXpathMatch($dom, $expected, "string(//$xpath_request)");
    }

    /**
     * @dataProvider providerMapTransformation
     *
     * @param string $xpath_request
     * @param string $map_value
     * @param string $expected
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testMapTransformation(string $xpath_request, string $map_value, string $expected): void
    {
        $hl7_message = new CHL7v2Message();
        $hl7_message->parse(self::EXAMPLE_MESSAGE);
        $dom = $hl7_message->toXML(null, true);

        $transformation         = new CTransformationRule();
        $transformation->params = $map_value;
        $dom = $transformation->mapTransformation($dom, $xpath_request);

        $this->assertXpathMatch($dom, $expected, "string(//$xpath_request)");
    }

    /**
     * @dataProvider providerLowerTransformation
     *
     * @param string $xpath_request
     * @param string $xpath_request
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testLowerTransformationOk(string $xpath_request, string $expected): void
    {
        $hl7_message = new CHL7v2Message();
        $hl7_message->parse(self::EXAMPLE_MESSAGE);
        $dom = $hl7_message->toXML(null, true);

        $transformation      = new CTransformationRule();
        $dom = $transformation->lowerTransformation($dom, $xpath_request);

        $this->assertXpathMatch($dom, $expected, "string(//$xpath_request)");
    }

    /**
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testLowerTransformationSourceNodeNotExist(): void
    {
        $hl7_message = new CHL7v2Message();
        $hl7_message->parse(self::EXAMPLE_MESSAGE);
        $dom                   = $hl7_message->toXML();
        $hash_message_begining = sha1($dom->saveXML());

        $transformation      = new CTransformationRule();
        $dom = $transformation->lowerTransformation($dom, 'EVN/EVN.4');

        $hash_message_ending = sha1($dom->saveXML());
        $this->assertEquals($hash_message_begining, $hash_message_ending);
    }

    /**
     * @dataProvider providerUpperTransformation
     *
     * @param string $xpath_request
     * @param string $xpath_request
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testUpperTransformationOk(string $xpath_request, string $expected): void
    {
        $hl7_message = new CHL7v2Message();
        $hl7_message->parse(self::EXAMPLE_MESSAGE);
        $dom                   = $hl7_message->toXML();

        $transformation      = new CTransformationRule();
        $dom = $transformation->upperTransformation($dom, $xpath_request);

        $this->assertXpathMatch($dom, $expected, "string(//$xpath_request)");
    }

    /**
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testUpperTransformationSourceNodeNotExist(): void
    {
        $hl7_message = new CHL7v2Message();
        $hl7_message->parse(self::EXAMPLE_MESSAGE);
        $dom                   = $hl7_message->toXML();
        $hash_message_begining = sha1($dom->saveXML());

        $transformation      = new CTransformationRule();
        $dom = $transformation->upperTransformation($dom, 'EVN/EVN.4');

        $hash_message_ending = sha1($dom->saveXML());
        $this->assertEquals($hash_message_begining, $hash_message_ending);
    }

    /**
     * @dataProvider providerInsertTransformation
     *
     * @param string $xpath_request
     * @param string $insert_value
     * @param string $expected
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testInsertTransformationOk(string $xpath_request, string $insert_value, string $expected): void
    {
        $this->markTestSkipped('Error on empty nodes');
        $hl7_message = new CHL7v2Message();
        $hl7_message->parse(self::EXAMPLE_MESSAGE);
        $dom                   = $hl7_message->toXML();

        $transformation      = new CTransformationRule();
        $dom = $transformation->insertTransformation($dom, $xpath_request, null, $insert_value);

        $this->assertXpathMatch($dom, $expected, "string(//$xpath_request)");
    }

    /**
     * @dataProvider providerDeleteTransformation
     *
     * @param string $xpath_request
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testDeleteTransformationOk(string $xpath_request): void
    {
        $hl7_message = new CHL7v2Message();
        $hl7_message->parse(self::EXAMPLE_MESSAGE);
        $dom                   = $hl7_message->toXML();

        $transformation      = new CTransformationRule();
        $dom = $transformation->deleteTransformation($dom, $xpath_request);

        $this->assertXpathMatch($dom, '', "string(//$xpath_request)");
    }

    /**
     * @dataProvider providerDeleteComplexeTransformation
     *
     * @param string $xpath_request
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testDeleteComplexeTransformationOk(string $xpath_request): void
    {
        $hl7_message = new CHL7v2Message();
        $hl7_message->parse(self::EXAMPLE_MESSAGE_ORU);
        $dom                   = $hl7_message->toXML();

        $transformation      = new CTransformationRule();
        $dom = $transformation->deleteTransformation($dom, $xpath_request);

        $this->assertXpathMatch($dom, '', "string(//$xpath_request)");
    }

    /**
     * @dataProvider providerGetSegmentPath
     *
     * @param string $path
     * @param string $relative
     * @param string $expected
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testGetSegmentPath(string $path, string $relative, string $expected) {
        $transformation = new CTransformationRule();

        $this->assertEquals($expected, $transformation->getSegmentPath($path, $relative === 'true' ? true : false));
    }

    /**
     * @dataProvider providerGetGroupPath
     *
     * @param string $path
     * @param string $relative
     * @param string $expected
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function testGetGroupPath(string $path, string $relative, string $expected) {
        $transformation = new CTransformationRule();

        $this->assertEquals($expected, $transformation->getGroupPath($path, $relative === 'true' ? true : false));
    }
}
