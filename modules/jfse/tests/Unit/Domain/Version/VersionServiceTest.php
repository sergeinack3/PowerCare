<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Version;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\ApiClients\VersionClient;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class VersionServiceTest extends UnitTestJfse
{
    public function testGetApiVersion(): void
    {
        $json = <<<JSON
{
    "method": {
        "output": {
            "ssv": {
                "posteTravail": {
                    "groupe": "60",
                    "noVersionSSV": "0713",
                    "noVersionGALSS": "0345",
                    "noVersionPSS": "0339"
                },
                "lstConfigurationLecteurCartes": [],
                "lstLecteurPCSC": [
                    {
                        "groupe": "67",
                        "nom": "Identive CLOUD 2700 R Smart Card Reader 0",
                        "typeCarte": "carte PS"
                    }
                ],
                "lstComposantSesamVitale": [
                    {
                        "groupe": "64",
                        "identifiant": "100",
                        "libelle": "VERSION DES PROTOCOLES",
                        "noVersion": "0339"
                    }
                ]
            },
            "srt": {
                "groupe": "3600",
                "versionReferentiel": "1099",
                "versionReferentielServeur": "1099",
                "versionBaseCCAM": "06300",
                "versionBaseCCAMServeur": "06300",
                "dateModification": "",
                "varianteReferentiel": "REEL",
                "commentaire": "Base reelle (base nationale v6300 nouveau format)",
                "revisionReferentiel": "0",
                "versionPartieLogicielle": "0212"
            },
            "sts": {
                "lstDetails": [
                    {
                        "groupe": "3780",
                        "identificationModule": "I",
                        "identificationModuleLibelle": "STS Interface",
                        "versionModule": "0115",
                        "versionTablesExternes": "0110",
                        "variante": "REEL",
                        "commentaire": "Tables STS-Interface"
                    }
                ]
            }
        }
    }
}
JSON;

        $client = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $json)]);

        $service = new VersionService(new VersionClient($client));

        $expected = ApiVersion::hydrate(
            [
                "ssv" => SSVVersion::hydrate(
                    [
                        "computer"                => Computer::hydrate(
                            [
                                "group"         => "60",
                                "ssv_version"   => "0713",
                                "galss_version" => "0345",
                                "pss_version"   => "0339",
                            ]
                        ),
                        "card_reader_configs"     => [],
                        "pcsc_readers"            => [
                            PCSCReader::hydrate(
                                [
                                    "group"     => "67",
                                    "name"      => "Identive CLOUD 2700 R Smart Card Reader 0",
                                    "card_type" => "carte PS",
                                ]
                            ),
                        ],
                        "sesam_vitale_components" => [
                            SesamVitaleComponent::hydrate(
                                [
                                    "group"          => "64",
                                    "id"             => "100",
                                    "label"          => "VERSION DES PROTOCOLES",
                                    "version_number" => "0339",
                                ]
                            ),
                        ],
                    ]
                ),
                "srt" => SRTVersion::hydrate(
                    [
                        "group"                => "3600",
                        "referential"          => "1099",
                        "referential_server"   => "1099",
                        "ccam_db"              => "06300",
                        "ccm_db_server"        => "06300",
                        "modification_date"    => null,
                        "referential_variant"  => "REEL",
                        "comment"              => "Base reelle (base nationale v6300 nouveau format)",
                        "referential_revision" => "0",
                        "software_version"     => "0212",
                    ]
                ),
                "sts" => STSVersion::hydrate(
                    [
                        "details" => [
                            Detail::hydrate(
                                [
                                    "group"                       => "3780",
                                    "module_identification"       => "I",
                                    "module_identification_label" => "STS Interface",
                                    "module_version"              => "0115",
                                    "external_tables_version"     => "0110",
                                    "variant"                     => "REEL",
                                    "comment"                     => "Tables STS-Interface",
                                ]
                            ),
                        ],
                    ]
                ),
            ]
        );

        $this->assertEquals($expected, $service->getApiVersion(1234));
    }

    public function testGetVersion(): void
    {
        $json = <<<JSON
{
    "method": {
        "output": {
            "version": {
                "organismes": "3.64",
                "cdc": "1.40 Addendum 7",
                "dateCdc": "20200511",
                "dateTarifs": "20200305",
                "mail": "",
                "serveurVersion": "2.0 rev 00",
                "daemonVersion": "3.0011",
                "ccamVersion": "06000",
                "socleApiVersion": "1.40.13"
            }
        }
    }
}
JSON;

        $expected = Version::hydrate(
            [
                "organisations"    => "3.64",
                "cdc"              => "1.40 Addendum 7",
                "cdc_date"         => new DateTimeImmutable("2020-05-11"),
                "prices_date"      => new DateTimeImmutable("2020-03-05"),
                "mail"             => null,
                "server_version"   => "2.0 rev 00",
                "daemon_version"   => "3.0011",
                "ccam_version"     => "06000",
                "base_api_version" => "1.40.13",
            ]
        );

        $client  = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $json)]);
        $service = new VersionService(new VersionClient($client));

        $this->assertEquals($expected, $service->getVersion(1111));
    }
}
