<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain;

use Ox\Mediboard\Jfse\ApiClients\ConventionClient;
use Ox\Mediboard\Jfse\Domain\Convention\Convention;
use Ox\Mediboard\Jfse\Domain\Convention\ConventionService;
use Ox\Mediboard\Jfse\Domain\Convention\ConventionType;
use Ox\Mediboard\Jfse\Domain\Convention\Correspondence;
use Ox\Mediboard\Jfse\Domain\Convention\Grouping;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use PHPUnit\Framework\MockObject\MockObject;

class ConventionServiceTest extends UnitTestJfse
{
    /** @var MockObject */
    private $client;

    public function setUp(): void
    {
        parent::setUp();

        $empty_data   = '{"method": {"output": {}}}';
        $this->client = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $empty_data)]);
    }

    /**
     * @dataProvider updateConventionProvider
     *
     * @param Convention $convention
     */
    public function testUpdateConvention(Convention $convention): void
    {
        $this->assertTrue((new ConventionService(new ConventionClient($this->client)))->updateConvention($convention));
    }

    public function updateConventionProvider(): array
    {
        $convention_a = Convention::hydrate(
            [
                "jfse_id" => 1,
            ]
        );
        $convention_b = Convention::hydrate(
            [
                "signer_organization_number"  => "775671993",
                "convention_type"             => "RO",
                "secondary_criteria"          => "030255400",
                "agreement_type"              => "T",
                "signer_organization_label"   => "MUTUELLE BLEUE",
                "amc_number"                  => "775671993",
                "amc_label"                   => "MUTUELLE BLEUE",
                "statutory_operator"          => "",
                "routing_code"                => "RO",
                "host_id"                     => "",
                "domain_name"                 => "TEST DOMAINE",
                "sts_referral_code"           => "",
                "group_convention_flag"       => "0",
                "certificate_use_flag"        => "0",
                "sts_disabled_flag"           => "1",
                "cancel_management"           => "0",
                "rectification_management"    => "0",
                "convention_application"      => "1",
                "systematic_application"      => "1",
                "convention_application_date" => "",
                "group_id"                    => "0",
                "jfse_id"                     => "1",
            ]
        );

        return [[$convention_a], [$convention_b]];
    }

    public function testListRegroupements(): void
    {
        $json_response = <<< JSON
            {
                "method": {
                    "output": {
                        "lstRegroupements": [
                            {
                                "idRegroupement": "1501513695254272553",
                                "numeroAMC": "302976568",
                                "libelleOrganismeAMC": "Mutuelle Centrale des Finances",
                                "typeConvention": "RO",
                                "libelleTypeConvention": "Gestion unique",
                                "critereSecondaire": "94???????",
                                "numeroSignataire": "94",
                                "idEtablissement": 0,
                                "idJfse": 0
                            },
                            {
                                "idRegroupement": "1501513695254302554",
                                "numeroAMC": "341230380",
                                "libelleOrganismeAMC": "Mutuelle des Agents des Impots",
                                "typeConvention": "RO",
                                "libelleTypeConvention": "Gestion unique",
                                "critereSecondaire": "94???????",
                                "numeroSignataire": "94",
                                "idEtablissement": 0,
                                "idJfse": 0
                            }
                        ]
                    }
                }
            }
JSON;
        $this->client  = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, utf8_encode($json_response))]
        );
        $expected      = [
            Grouping::hydrate(
                [
                    "grouping_id"                => "1501513695254272553",
                    "amc_number"                 => "302976568",
                    "amc_label"                  => "Mutuelle Centrale des Finances",
                    "convention_type"            => "RO",
                    "convention_type_label"      => "Gestion unique",
                    "secondary_criteria"         => "94???????",
                    "signer_organization_number" => "94",
                    "group_id"                   => 0,
                    "jfse_id"                    => 0,
                ]
            ),
            Grouping::hydrate(
                [
                    "grouping_id"                => "1501513695254302554",
                    "amc_number"                 => "341230380",
                    "amc_label"                  => "Mutuelle des Agents des Impots",
                    "convention_type"            => "RO",
                    "convention_type_label"      => "Gestion unique",
                    "secondary_criteria"         => "94???????",
                    "signer_organization_number" => "94",
                    "group_id"                   => 0,
                    "jfse_id"                    => 0,
                ]
            ),
        ];
        $this->assertEquals(
            $expected,
            (new ConventionService(new ConventionClient($this->client)))->listRegroupements()
        );
    }

    public function testListCorrespondences(): void
    {
        $json_response = <<< JSON
            {
                "method": {
                    "output": {
                        "lstCorrespondances": [
                            {
                                "idCorrespondance": "1501513695257482702",
                                "numMutuelle": "????????",
                                "codeRegime": "999410699",
                                "numeroAMC": "77565712",
                                "libelleAMC": "SMCC",
                                "idEtablissement": 0
                            },
                            {
                                "idCorrespondance": "1501513695257592703",
                                "numMutuelle": "????????",
                                "codeRegime": "092000000",
                                "numeroAMC": "77565712",
                                "libelleAMC": "SMCC",
                                "idEtablissement": 0
                            }
                        ]
                    }
                }
            }
JSON;
        $this->client  = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, utf8_encode($json_response))]
        );

        $expected = [
            Correspondence::hydrate(
                [
                    "correspondence_id"       => 1501513695257482702,
                    "health_insurance_number" => "????????",
                    "regime_code"             => "999410699",
                    "amc_number"              => "77565712",
                    "amc_label"               => "SMCC",
                    "group_id"                => 0,
                ]
            ),
            Correspondence::hydrate(
                [
                    "correspondence_id"       => 1501513695257592703,
                    "health_insurance_number" => "????????",
                    "regime_code"             => "092000000",
                    "amc_number"              => "77565712",
                    "amc_label"               => "SMCC",
                    "group_id"                => 0,
                ]
            ),
        ];
        $this->assertEquals(
            $expected,
            (new ConventionService(new ConventionClient($this->client)))->listCorrespondences()
        );
    }

    public function testListTypesConvention(): void
    {
        $json_response = <<< JSON
            {
                "method": {
                    "output": {
                        "lstTypeConvention": [
                            {
                                "code": "AC",
                                "libelle": "Ancienne Convention SP Pharma"
                            },
                            {
                                "code": "CM",
                                "libelle": "Convention FNMF"
                            }
                        ]
                    }
                }
            }
JSON;
        $this->client  = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, utf8_encode($json_response))]
        );

        $expected = [
            ConventionType::hydrate(
                [
                    "code"  => "AC",
                    "label" => "Ancienne Convention SP Pharma",
                ]
            ),
            ConventionType::hydrate(
                [
                    "code"  => "CM",
                    "label" => "Convention FNMF",
                ]
            ),
        ];
        $this->assertEquals(
            $expected,
            (new ConventionService(new ConventionClient($this->client)))->listTypesConvention()
        );
    }

    /**
     * @dataProvider updateRegroupementProvider
     */
    public function testUpdateRegroupement(Grouping $grouping): void
    {
        $service = new ConventionService(new ConventionClient($this->client));
        $this->assertTrue($service->updateRegroupement($grouping));
    }

    public function updateRegroupementProvider(): array
    {
        $entity_A = Grouping::hydrate(
            [
                "grouping_id"                => 0,
                "amc_number"                 => "123456789",
                "amc_label"                  => "Label Test 1",
                "convention_type"            => "RO",
                "convention_type_label"      => "Gestion unique",
                "secondary_criteria"         => "94???????",
                "signer_organization_number" => "94",
                "group_id"                   => 0,
                "jfse_id"                    => 0,
            ]
        );
        $entity_B = Grouping::hydrate(
            [
                "grouping_id"                => 0,
                "amc_number"                 => "987654321",
                "amc_label"                  => "Label Test 2",
                "convention_type"            => "RO",
                "convention_type_label"      => "Gestion unique",
                "secondary_criteria"         => "94???????",
                "signer_organization_number" => "94",
                "group_id"                   => 0,
                "jfse_id"                    => 0,
            ]
        );

        return [[$entity_A], [$entity_B]];
    }

    /**
     * @dataProvider updateCorrespondenceProvider
     */
    public function testUpdateCorrespondance(Correspondence $correspondence): void
    {
        $service = new ConventionService(new ConventionClient($this->client));
        $this->assertTrue($service->updateCorrespondance($correspondence));
    }

    public function updateCorrespondenceProvider(): array
    {
        $entity_A = Correspondence::hydrate(
            [
                "correspondence_id"       => 0,
                "health_insurance_number" => "????????",
                "regime_code"             => "999410699",
                "amc_number"              => "123456789",
                "amc_label"               => "SMCC",
                "group_id"                => 0,
            ]
        );
        $entity_B = Correspondence::hydrate(
            [
                "correspondence_id"       => 0,
                "health_insurance_number" => "????????",
                "regime_code"             => "092000000",
                "amc_number"              => "987654321",
                "amc_label"               => "SMCC",
                "group_id"                => 0,
            ]
        );

        return [[$entity_A], [$entity_B]];
    }

    /**
     * @dataProvider convertCharToBinaryProvider
     */
    public function testConvertCharToBinary(string $char, string $expected): void
    {
        $binary = $this->invokePrivateMethod(
            new ConventionService(new ConventionClient($this->client)),
            'convertCharToBinary',
            $char
        );
        $this->assertEquals($expected, $binary);
    }

    /**
     * @dataProvider convertFileContentToBinaryProvider
     */
    public function testConvertFileContentToBinary(string $content, string $expected): void
    {
        $binary = $this->invokePrivateMethod(
            new ConventionService(new ConventionClient($this->client)),
            'convertFileContentToBinary',
            $content
        );
        $this->assertEquals($expected, $binary);
    }

    public function convertCharToBinaryProvider(): array
    {
        $binary_a = "1100001";
        $binary_b = "1100010";
        $binary_c = "1100011";
        $binary_d = "1100100";
        $binary_1 = "110001";
        $binary_2 = "110010";
        $binary_3 = "110011";
        $binary_4 = "110100";

        return [
            ["a", $binary_a],
            ["b", $binary_b],
            ["c", $binary_c],
            ["d", $binary_d],
            ["1", $binary_1],
            ["2", $binary_2],
            ["3", $binary_3],
            ["4", $binary_4],
        ];
    }

    public function convertFileContentToBinaryProvider(): array
    {
        $content_a = "a;b;c;d;1;2;3;4";
        $binary_a  =
            "1100001111011110001011101111000111110111100100111011110001111011110010111011110011111011110100";

        $content_b = "e;f;g;h;5;6;7;8";
        $binary_b  =
            "1100101111011110011011101111001111110111101000111011110101111011110110111011110111111011111000";

        return [
            [$content_a, $binary_a],
            [$content_b, $binary_b],
        ];
    }
}
