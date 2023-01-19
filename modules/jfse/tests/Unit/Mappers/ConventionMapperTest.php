<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Mappers;

use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Convention\Convention;
use Ox\Mediboard\Jfse\Mappers\ConventionMapper;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class ConventionMapperTest extends UnitTestJfse
{
    /** @var ConventionMapper */
    private $mapper;

    public function setUp(): void
    {
        parent::setup();
        $this->mapper = new ConventionMapper();
    }

    /**
     * @dataProvider makeStoreArrayFromConventionProvider
     *
     * @param Convention $convention
     * @param array      $expected
     */
    public function testMakeStoreArrayFromConvention(Convention $convention, array $expected): void
    {
        $this->assertEquals($expected, $this->mapper->makeStoreArrayFromConvention($convention));
    }

    public function makeStoreArrayFromConventionProvider(): array
    {
        $convention_a = Convention::hydrate(
            [
                "jfse_id" => 1,
            ]
        );
        $expected_a   = [
            "updateConvention" => [
                "idJfse" => 1,
            ],
        ];
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
        $expected_b   = [
            "updateConvention" => [
                "numOrganismeSignataire"     => "775671993",
                "typeConvention"             => "RO",
                "critereSecondaire"          => "030255400",
                "typeAccord"                 => "T",
                "libOrganismeSignataire"     => "MUTUELLE BLEUE",
                "numAMC"                     => "775671993",
                "libelleAMC"                 => "MUTUELLE BLEUE",
                "codeRoutage"                => "RO",
                "nomDomaine"                 => "TEST DOMAINE",
                "indicateurConventionGroupe" => "0",
                "indicateurUsageAttestation" => "0",
                "indicateurDesactivationSTS" => "1",
                "gestionDAnnulation"         => "0",
                "gestiondReRectification"    => "0",
                "applicationConvention"      => "1",
                "applicationSystematique"    => "1",
                "idEtablissement"            => "0",
                "idJfse"                     => "1",
            ],
        ];

        return [
            [$convention_a, $expected_a],
            [$convention_b, $expected_b],
        ];
    }

    /**
     * @dataProvider getGroupingsToInstallFromResponseProvider
     *
     * @param Response $response
     * @param array    $expected
     */
    public function testGetGroupingsToInstallFromResponse(Response $response, array $expected): void
    {
        $this->assertEquals($expected, $this->mapper::getGroupingsToInstallFromResponse($response));
    }

    public function getGroupingsToInstallFromResponseProvider(): array
    {
        $json_response = <<< JSON
{
    "method":{
        "output":{
            "lstDetails":[
                {
                    "lstReferentielRegroupements":[
                        {
                            "monRegroupement":{
                                "numeroOrganismeComplementaire":"302976568",
                                "libelleOrganismeComplementaire":"Mutuelle Centrale des Finances",
                                "typeConvention":"RO",
                                "libelleTypeConvention":"Gestion unique",
                                "critereSecondaire":"94???????",
                                "identifiantOrganismeSignataire":"94"
                            }
                        },
                        {
                            "monRegroupement":{
                                "numeroOrganismeComplementaire":"341230380",
                                "libelleOrganismeComplementaire":"Mutuelle des Agents des Impots",
                                "typeConvention":"RO",
                                "libelleTypeConvention":"Gestion unique",
                                "critereSecondaire":"94???????",
                                "identifiantOrganismeSignataire":"94"
                            }
                        }
                    ]
                }
            ]
        }
    }
}
JSON;
        $a = json_decode(utf8_encode($json_response), true);
        $e = json_last_error_msg();
        $response      = Response::forge(
            'CNV-getListeConventionsAInstaller',
            json_decode(utf8_encode($json_response), true)
        );
        $expected      = [
            "0" => [
                "amc_number"                 => "302976568",
                "amc_label"                  => "Mutuelle Centrale des Finances",
                "convention_type"            => "RO",
                "convention_type_label"      => "Gestion unique",
                "secondary_criteria"         => "94???????",
                "signer_organization_number" => "94",
            ],
            "1" => [
                "amc_number"                 => "341230380",
                "amc_label"                  => "Mutuelle des Agents des Impots",
                "convention_type"            => "RO",
                "convention_type_label"      => "Gestion unique",
                "secondary_criteria"         => "94???????",
                "signer_organization_number" => "94",
            ],
        ];

        return [[$response, $expected]];
    }
}
