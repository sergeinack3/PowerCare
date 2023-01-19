<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\Physician;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class PrescribingPhysicianMapperTest extends UnitTestJfse
{

    public function testGetPhysiciansFromArray(): void
    {
        $data = [
            "lstMedecins" => [
                [
                    "id"            => 1603448711301941782,
                    "prenom"        => "JOSEPH",
                    "nom"           => "MARTELL",
                    "noFacturation" => "123456789",
                    "specialite"    => 0,
                    "type"          => 0,
                    "noRPPS"        => "123456789",
                    "noStructure"   => "987654",
                ],
                [
                    "id"            => 9876543456789876,
                    "prenom"        => "Laurent",
                    "nom"           => "LLM",
                    "noFacturation" => "987654321",
                    "specialite"    => 0,
                    "type"          => 0,
                    "noRPPS"        => "987654321",
                    "noStructure"   => "12345",
                ],
                [
                    "id"            => 9876543456789876543,
                    "prenom"        => "Johne",
                    "nom"           => "Doe",
                    "noFacturation" => "09876",
                    "specialite"    => 0,
                    "type"          => 0,
                    "noRPPS"        => "09876",
                    "noStructure"   => "123456",
                ],
            ],
        ];

        $this->assertEquals(
            $this->expectedPhysiciansArray(),
            (new PrescribingPhysicianMapper())->getPhysiciansFromArray($data)
        );
    }

    /**
     * @dataProvider storeDataProvider
     */
    public function testMakeStoreArrayFromPhysician(Physician $physician, array $expected): void
    {
        $this->assertEquals($expected, (new PrescribingPhysicianMapper())->makeStoreArrayFromPhysician($physician));
    }

    public function testMakeDeleteArray(): void
    {
        $expected = ["deleteMedecinPrescripteur" => ["id" => 111]];

        $this->assertEquals($expected, (new PrescribingPhysicianMapper())->makeDeleteArray(111));
    }

    public function testGetPrescribingPhysiciansFromArray(): void
    {
        $data = [
            "lstMedecinPrescripteur" => [
                [
                    "id"            => 1603448711301941782,
                    "prenom"        => "JOSEPH",
                    "nom"           => "MARTELL",
                    "noFacturation" => "123456789",
                    "specialite"    => 0,
                    "type"          => 0,
                    "noRPPS"        => "123456789",
                    "noStructure"   => "987654",
                ],
                [
                    "id"            => 9876543456789876,
                    "prenom"        => "Laurent",
                    "nom"           => "LLM",
                    "noFacturation" => "987654321",
                    "specialite"    => 0,
                    "type"          => 0,
                    "noRPPS"        => "987654321",
                    "noStructure"   => "12345",
                ],
                [
                    "id"            => 9876543456789876543,
                    "prenom"        => "Johne",
                    "nom"           => "Doe",
                    "noFacturation" => "09876",
                    "specialite"    => 0,
                    "type"          => 0,
                    "noRPPS"        => "09876",
                    "noStructure"   => "123456",
                ],
            ],
        ];

        $this->assertEquals(
            $this->expectedPhysiciansArray(),
            (new PrescribingPhysicianMapper())->getPrescribingPhysiciansFromArray($data)
        );
    }

    /**
     * @dataProvider setPrescribingPhysicianDataProvider
     */
    public function testMakeSetPrescribingPhysicianArray(Physician $physician, array $expected): void
    {
        $date = new DateTimeImmutable("2020-11-04");

        $mapper = new PrescribingPhysicianMapper();
        $mapped = $mapper->makeSetPrescribingPhysicianArray("1111", $date, "0", $physician);

        $this->assertEquals($expected, $mapped);
    }

    public function storeDataProvider(): array
    {
        return [
            [
                Physician::hydrate(
                    [
                        "id"               => 1603448711301941782,
                        "first_name"       => "JOSEPH",
                        "last_name"        => "MARTELL",
                        "invoicing_number" => "123456789",
                        "speciality"       => 0,
                        "type"             => 0,
                    ]
                ),
                [
                    "updateMedecinPrescripteur" => [
                        "id"            => 1603448711301941782,
                        "nom"           => "MARTELL",
                        "prenom"        => "JOSEPH",
                        "noFacturation" => "123456789",
                        "specialite"    => 0,
                        "type"          => 0,
                    ],
                ],
            ],
            [
                Physician::hydrate(
                    [
                        "id"               => 1603448711301941782,
                        "first_name"       => "JOSEPH",
                        "last_name"        => "MARTELL",
                        "invoicing_number" => "123456789",
                        "speciality"       => 0,
                        "type"             => 0,
                        "national_id"      => "123456789",
                    ]
                ),
                [
                    "updateMedecinPrescripteur" => [
                        "id"            => 1603448711301941782,
                        "nom"           => "MARTELL",
                        "prenom"        => "JOSEPH",
                        "noFacturation" => "123456789",
                        "specialite"    => 0,
                        "type"          => 0,
                        "noRPPS"        => "123456789",
                    ],
                ],
            ],
            [
                Physician::hydrate(
                    [
                        "id"               => 1603448711301941782,
                        "first_name"       => "JOSEPH",
                        "last_name"        => "MARTELL",
                        "invoicing_number" => "123456789",
                        "speciality"       => 0,
                        "type"             => 0,
                        "national_id"      => "123456789",
                        "structure_id"     => "987654",
                    ]
                ),
                [
                    "updateMedecinPrescripteur" => [
                        "id"            => 1603448711301941782,
                        "nom"           => "MARTELL",
                        "prenom"        => "JOSEPH",
                        "noFacturation" => "123456789",
                        "specialite"    => 0,
                        "type"          => 0,
                        "noRPPS"        => "123456789",
                        "noStructure"   => "987654",
                    ],
                ],
            ],
        ];
    }

    public function setPrescribingPhysicianDataProvider(): array
    {
        return [
            [
                Physician::hydrate(
                    [
                        "id"               => 1603448711301941782,
                        "first_name"       => "JOSEPH",
                        "last_name"        => "MARTELL",
                        "invoicing_number" => "123456789",
                        "speciality"       => 0,
                        "type"             => 0,
                    ]
                ),
                [
                    "idFacture"    => "1111",
                    "prescripteur" => [
                        "datePrescription"    => "20201104",
                        "originePrescription" => "0",
                        "medecin"             => [
                            "id"            => 1603448711301941782,
                            "nom"           => "MARTELL",
                            "prenom"        => "JOSEPH",
                            "noFacturation" => "123456789",
                            "specialite"    => 0,
                            "type"          => 0,
                        ],
                    ],
                ],
            ],
            [
                Physician::hydrate(
                    [
                        "id"               => 1603448711301941782,
                        "first_name"       => "JOSEPH",
                        "last_name"        => "MARTELL",
                        "invoicing_number" => "123456789",
                        "speciality"       => 0,
                        "type"             => 0,
                        "national_id"      => "123456789",
                    ]
                ),
                [
                    "idFacture"    => "1111",
                    "prescripteur" => [
                        "datePrescription"    => "20201104",
                        "originePrescription" => "0",
                        "medecin"             => [
                            "id"            => 1603448711301941782,
                            "nom"           => "MARTELL",
                            "prenom"        => "JOSEPH",
                            "noFacturation" => "123456789",
                            "specialite"    => 0,
                            "type"          => 0,
                            "rpps"          => "123456789",
                        ],
                    ],
                ],
            ],
            [
                Physician::hydrate(
                    [
                        "id"               => 1603448711301941782,
                        "first_name"       => "JOSEPH",
                        "last_name"        => "MARTELL",
                        "invoicing_number" => "123456789",
                        "speciality"       => 0,
                        "type"             => 0,
                        "national_id"      => "123456789",
                        "structure_id"     => "987654",
                    ]
                ),
                [
                    "idFacture"    => "1111",
                    "prescripteur" => [
                        "datePrescription"    => "20201104",
                        "originePrescription" => "0",
                        "medecin"             => [
                            "id"            => 1603448711301941782,
                            "nom"           => "MARTELL",
                            "prenom"        => "JOSEPH",
                            "noFacturation" => "123456789",
                            "specialite"    => 0,
                            "type"          => 0,
                            "rpps"          => "123456789",
                            "noStructure"   => "987654",
                        ],
                    ],
                ],
            ],
        ];
    }

    private function expectedPhysiciansArray(): array
    {
        return [
            Physician::hydrate(
                [
                    "id"               => 1603448711301941782,
                    "first_name"       => "JOSEPH",
                    "last_name"        => "MARTELL",
                    "invoicing_number" => "123456789",
                    "speciality"       => 0,
                    "type"             => 0,
                    "national_id"      => "123456789",
                    "structure_id"     => "987654",
                ]
            ),
            Physician::hydrate(
                [
                    "id"               => 9876543456789876,
                    "first_name"       => "Laurent",
                    "last_name"        => "LLM",
                    "invoicing_number" => "987654321",
                    "speciality"       => 0,
                    "type"             => 0,
                    "national_id"      => "987654321",
                    "structure_id"     => "12345",
                ]
            ),
            Physician::hydrate(
                [
                    "id"               => 9876543456789876543,
                    "first_name"       => "Johne",
                    "last_name"        => "Doe",
                    "invoicing_number" => "09876",
                    "speciality"       => 0,
                    "type"             => 0,
                    "national_id"      => "09876",
                    "structure_id"     => "123456",
                ]
            ),
        ];
    }
}
