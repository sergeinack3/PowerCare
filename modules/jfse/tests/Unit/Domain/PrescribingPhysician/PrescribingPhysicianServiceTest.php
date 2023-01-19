<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain\PrescribingPhysician;

use DateTimeImmutable;
use Ox\Core\Cache;
use Ox\Mediboard\Jfse\ApiClients\PrescribingPhysicianClient;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\Physician;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\PhysicianSpeciality;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\PhysicianType;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\PrescribingPhysicianService;
use Ox\Mediboard\Jfse\Mappers\PrescribingPhysicianMapper;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use phpDocumentor\Reflection\Types\Void_;
use PHPUnit\Framework\MockObject\MockObject;

class PrescribingPhysicianServiceTest extends UnitTestJfse
{
    /** @var MockObject (Cache::class) */
    private $types_cache;

    /** @var MockObject (Cache::class) */
    private $specialities_cache;

    /** @var MockObject (Cache::class) */
    private $origins_cache;

    /** @var MockObject (Cache::class) */
    private $prescribing_cache;

    /** @var MockObject (Cache::class) */
    private $physician_cache;

    /** @var MockObject (PrescribingPhysicianMapper::class) */
    private $mapper;

    public function setUp(): void
    {
        parent::setUp();

        $this->types_cache        = $this->newMockCache();
        $this->specialities_cache = $this->newMockCache();
        $this->origins_cache      = $this->newMockCache();
        $this->prescribing_cache  = $this->newMockCache();
        $this->physician_cache    = $this->newMockCache();

        $this->mapper = $this->getMockBuilder(PrescribingPhysicianMapper::class)
            ->setMethods(['makeStoreArrayFromPhysician', 'makeDeleteArray', 'makeSetPrescribingPhysicianArray'])
            ->getMock();
        $this->mapper->method('makeStoreArrayFromPhysician')->willReturn([]);
        $this->mapper->method('makeDeleteArray')->willReturn([]);
        $this->mapper->method('makeSetPrescribingPhysicianArray')->willReturn([]);
    }

    public function testGetPhysicianTypesListWithoutCache(): void
    {
        $this->types_cache->method('get')->willReturn(null);

        $json_response = <<<JSON
    {
        "method": {
            "output": {
                "lstType": [
                    {
                        "code": 0,
                        "libelle": "Libéral"
                    },
                    {
                        "code": 1,
                        "libelle": "Salarié"
                    },
                    {
                        "code": 2,
                        "libelle": "Bénévole"
                    }
                ]
            }
        }
    }
JSON;
        $client        = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, utf8_encode($json_response))]
        );
        $service       = new PrescribingPhysicianService(
            'CMediusers-111',
            new PrescribingPhysicianClient($client, $this->mapper)
        );

        $expected = [
            PhysicianType::hydrate(["code" => 0, "label" => "Libéral"]),
            PhysicianType::hydrate(["code" => 1, "label" => "Salarié"]),
            PhysicianType::hydrate(["code" => 2, "label" => "Bénévole"]),
        ];

        $this->assertEquals($expected, $service->getPhysicianTypesList($this->types_cache));
    }

    public function testGetPhysicianTypesListWithCache(): void
    {
        $json_response = <<<JSON
    {
        "lstType": [
            {
                "code": 0,
                "libelle": "Libéral"
            },
            {
                "code": 1,
                "libelle": "Salarié"
            },
            {
                "code": 2,
                "libelle": "Bénévole"
            }
        ]
    }
JSON;
        $this->types_cache->method('get')->willReturn(json_decode(utf8_encode($json_response), true));

        $client  = $this->makeClientFromGuzzleResponses([]);
        $service = new PrescribingPhysicianService(
            'CMediusers-111',
            new PrescribingPhysicianClient($client, $this->mapper)
        );

        $expected = [
            PhysicianType::hydrate(["code" => 0, "label" => utf8_encode("Libéral")]),
            PhysicianType::hydrate(["code" => 1, "label" => utf8_encode("Salarié")]),
            PhysicianType::hydrate(["code" => 2, "label" => utf8_encode("Bénévole")]),
        ];

        $this->assertEquals($expected, $service->getPhysicianTypesList($this->types_cache));
    }

    //    public function testGetPhysicianListWithoutCache(): void
    //    {
    //        $this->physician_cache->method('get')->willReturn(null);
    //
    //        $json_response = <<<JSON
    //JSON;
    //        $client        = $this->makeClientFromGuzzleResponses(
    //            [$this->makeJsonGuzzleResponse(200, utf8_encode($json_response))]
    //        );
    //        $service       = new PrescribingPhysicianService(
    //            'CMediusers-111',
    //            new PrescribingPhysicianClient($client, $this->mapper)
    //        );
    //
    //        $expected = [
    //            Physician::hydrate(
    //                [
    //                    "id"               => 1603448711301941782,
    //                    "first_name"       => "JOSEPH",
    //                    "last_name"        => "MARTELL",
    //                    "invoicing_number" => "123456789",
    //                    "speciality"       => 0,
    //                    "type"             => 0,
    //                    "national_id"      => "123456789",
    //                    "structure_id"     => "987654",
    //                ]
    //            ),
    //        ];
    //
    //        $this->assertEquals($expected, $service->getPhysicianList('CMediusers-111', $this->physician_cache));
    //    }
    //
    //    public function testGetPhysicianListWithCache(): void
    //    {
    //        $json_response = <<<JSON
    //JSON;
    //        $this->physician_cache->method('get')->willReturn(json_decode($json_response, true));
    //
    //        $client  = $this->makeClientFromGuzzleResponses([]);
    //        $service = new PrescribingPhysicianService(
    //            'CMediusers-111',
    //            new PrescribingPhysicianClient($client, $this->mapper)
    //        );
    //
    //        $expected = [
    //            Physician::hydrate(
    //                [
    //                    "id"               => 1603448711301941782,
    //                    "first_name"       => "JOSEPH",
    //                    "last_name"        => "MARTELL",
    //                    "invoicing_number" => "123456789",
    //                    "speciality"       => 0,
    //                    "type"             => 0,
    //                    "national_id"      => "123456789",
    //                    "structure_id"     => "987654",
    //                ]
    //            ),
    //        ];
    //
    //        $this->assertEquals($expected, $service->getPhysicianList('CMediusers-111', $this->physician_cache));
    //    }

    public function testGetPrescribingPhysicianListWithoutCache(): void
    {
        $json_response = <<<JSON
{
    "method": {
        "output": {
            "lstMedecinPrescripteur": [
                {
                    "id": "1603448711301941782",
                    "nom": "MARTELL",
                    "prenom": "JOSEPH",
                    "noFacturation": "123456789",
                    "noRPPS": "123456789",
                    "noStructure": "987654",
                    "specialite": "00",
                    "type": 0
                }
            ]
        }
    }
}
JSON;

        $client = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, $json_response)]
        );

        $service = new PrescribingPhysicianService(
            'CMediusers-111',
            new PrescribingPhysicianClient($client, $this->mapper)
        );

        $expected = [
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
        ];

        $this->assertEquals(
            $expected,
            $service->getPrescribingPhysicianList($this->prescribing_cache)
        );
    }

    public function testGetPrescribingPhysicianListWithCache(): void
    {
        $json_response = <<<JSON
{
    "lstMedecinPrescripteur": [
        {
            "id": "1603448711301941782",
            "nom": "MARTELL",
            "prenom": "JOSEPH",
            "noFacturation": "123456789",
            "noRPPS": "123456789",
            "noStructure": "987654",
            "specialite": "00",
            "type": 0
        }
    ]
}
JSON;
        $this->prescribing_cache->method('get')->willReturn(json_decode($json_response, true));

        $client = $this->makeClientFromGuzzleResponses([]);

        $service = new PrescribingPhysicianService(
            'CMediusers-111',
            new PrescribingPhysicianClient($client, $this->mapper)
        );

        $expected = [
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
        ];

        $this->assertEquals(
            $expected,
            $service->getPrescribingPhysicianList($this->prescribing_cache)
        );
    }

    public function testGetPhysicianSpecialitiesListWithoutCache(): void
    {
        $this->specialities_cache->method('get')->willReturn(null);

        $json_response = <<<JSON
{
    "method": {
        "output": {
            "lstSpecialite": [
                {
                    "code": "01",
                    "libelle": "Médecine générale",
                    "famille": "PR"
                },
                {
                    "code": "02",
                    "libelle": "Anesthésie-Réanimation",
                    "famille": "PR"
                },
                {
                    "code": "03",
                    "libelle": "Cardiologie",
                    "famille": "PR"
                }
            ]
        }
    }
}
JSON;
        $client        = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, utf8_encode($json_response))]
        );
        $service       = new PrescribingPhysicianService(
            'CMediusers-111',
            new PrescribingPhysicianClient($client, $this->mapper)
        );

        $expected = [
            PhysicianSpeciality::hydrate(["code" => "01", "label" => "Médecine générale", "family" => "PR"]),
            PhysicianSpeciality::hydrate(["code" => "02", "label" => "Anesthésie-Réanimation", "family" => "PR"]),
            PhysicianSpeciality::hydrate(["code" => "03", "label" => "Cardiologie", "family" => "PR"]),
        ];

        $this->assertEquals($expected, $service->getPhysicianSpecialitiesList($this->specialities_cache));
    }

    public function testGetPhysicianSpecialitiesListWithCache(): void
    {
        $json_response = <<<JSON
{
    "lstSpecialite": [
        {
            "code": "01",
            "libelle": "Médecine générale",
            "famille": "PR"
        },
        {
            "code": "02",
            "libelle": "Anesthésie-Réanimation",
            "famille": "PR"
        },
        {
            "code": "03",
            "libelle": "Cardiologie",
            "famille": "PR"
        }
    ]
}
JSON;
        $this->specialities_cache->method('get')->willReturn(json_decode(utf8_encode($json_response), true));

        $client  = $this->makeClientFromGuzzleResponses([]);
        $service = new PrescribingPhysicianService(
            'CMediusers-111',
            new PrescribingPhysicianClient($client, $this->mapper)
        );

        $expected = [
            PhysicianSpeciality::hydrate(
                ["code" => "01", "label" => utf8_encode("Médecine générale"), "family" => "PR"]
            ),
            PhysicianSpeciality::hydrate(
                ["code" => "02", "label" => utf8_encode("Anesthésie-Réanimation"), "family" => "PR"]
            ),
            PhysicianSpeciality::hydrate(
                ["code" => "03", "label" => utf8_encode("Cardiologie"), "family" => "PR"]
            ),
        ];

        $this->assertEquals($expected, $service->getPhysicianSpecialitiesList($this->specialities_cache));
    }

    public function testAddOrUpdateAPhysician(): void
    {
        $add_json_response             = '{"method": {"output": {}}}';

        $client  = $this->makeClientFromGuzzleResponses(
            [
                $this->makeJsonGuzzleResponse(200, $add_json_response),
            ]
        );
        $service = $this->getMockBuilder(PrescribingPhysicianService::class)
            ->setConstructorArgs(['CMediusers-111', new PrescribingPhysicianClient($client, $this->mapper)])
            ->setMethods(['setPrescribingPhysicianListIntoCache'])
            ->getMock();
//        $service->method('setPrescribingPhysicianListIntoCache')->willReturn(null);

        $this->assertTrue($service->storePhysician(Physician::hydrate([])));
    }

    public function testDeleteAPhysician(): void
    {
        $delete_json_response          = '{"method": {"output": {}}}';
        $list_physicians_json_response = <<<JSON
{
        "method": {
            "output": {
                "lstMedecinPrescripteur": [
                {
                    "id": "1603448711301941782",
                    "nom": "MARTELL",
                    "prenom": "JOSEPH",
                    "noFacturation": "123456789",
                    "noRPPS": "123456789",
                    "noStructure": "987654",
                    "specialite": "00",
                    "type": 0
                }
            ]
        }
    }
}
JSON;

        $client  = $this->makeClientFromGuzzleResponses(
            [
                $this->makeJsonGuzzleResponse(200, $delete_json_response),
                $this->makeJsonGuzzleResponse(200, $list_physicians_json_response),
            ]
        );
        $service = new PrescribingPhysicianService(
            'CMediusers-111',
            new PrescribingPhysicianClient($client, $this->mapper)
        );

        $this->assertTrue($service->deletePhysician(1));
    }

    public function testSetPrescribingPhysician(): void
    {
        $json_response = '{"method": {"output": {}}}';
        $client        = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $json_response)]);
        $service       = new PrescribingPhysicianService(
            'CMediusers-111',
            new PrescribingPhysicianClient($client, $this->mapper)
        );

        $physician = Physician::hydrate([]);

        $this->assertTrue($service->setPrescribingPhysician("11", new DateTimeImmutable(), "O", $physician));
    }

    /**
     * @dataProvider physicianListFilterProvider
     */
    public function testGetPrescribingPhysicianWithFilters(
        string $first_name,
        string $last_name,
        string $national_id,
        array $expected
    ): void {
        $physicians          = [
            Physician::hydrate(
                [
                    "id"               => 1603448711301941782,
                    "first_name"       => "Laurent",
                    "last_name"        => "LM",
                    "invoicing_number" => "123456789",
                    "speciality"       => 0,
                    "type"             => 0,
                    "national_id"      => "987654321",
                    "structure_id"     => "987654",
                ]
            ),
        ];
        $prescribing_service = $this->getMockBuilder(PrescribingPhysicianService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrescribingPhysicianList'])
            ->getMock();
        $prescribing_service->method('getPrescribingPhysicianList')->willReturn($physicians);

        $this->assertEquals(
            $expected,
            $prescribing_service->getPrescribingPhysiciansWithFilters(
                $first_name,
                $last_name,
                $national_id
            )
        );
    }

    public function physicianListFilterProvider(): array
    {
        return [
            [
                "",
                "",
                "",
                [],
            ],
            [
                "Laurent",
                "",
                "",
                [
                    Physician::hydrate(
                        [
                            "id"               => 1603448711301941782,
                            "first_name"       => "Laurent",
                            "last_name"        => "LM",
                            "invoicing_number" => "123456789",
                            "speciality"       => 0,
                            "type"             => 0,
                            "national_id"      => "987654321",
                            "structure_id"     => "987654",
                        ]
                    ),
                ],
            ],
            [
                "",
                "LM",
                "",
                [
                    Physician::hydrate(
                        [
                            "id"               => 1603448711301941782,
                            "first_name"       => "Laurent",
                            "last_name"        => "LM",
                            "invoicing_number" => "123456789",
                            "speciality"       => 0,
                            "type"             => 0,
                            "national_id"      => "987654321",
                            "structure_id"     => "987654",
                        ]
                    ),
                ],
            ],
            [
                "",
                "",
                "987654",
                [
                    Physician::hydrate(
                        [
                            "id"               => 1603448711301941782,
                            "first_name"       => "Laurent",
                            "last_name"        => "LM",
                            "invoicing_number" => "123456789",
                            "speciality"       => 0,
                            "type"             => 0,
                            "national_id"      => "987654321",
                            "structure_id"     => "987654",
                        ]
                    ),
                ],
            ],
        ];
    }

    public function testHasPrescribingPhysician(): void
    {
        $physicians          = [
            Physician::hydrate(
                [
                    "id"               => '1603448711301941782',
                    "first_name"       => "Laurent",
                    "last_name"        => "LM",
                    "invoicing_number" => "123456789",
                    "speciality"       => 0,
                    "type"             => 0,
                    "national_id"      => "987654321",
                    "structure_id"     => "987654",
                ]
            ),
        ];
        $prescribing_service = $this->getMockBuilder(PrescribingPhysicianService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrescribingPhysicianList'])
            ->getMock();
        $prescribing_service->method('getPrescribingPhysicianList')->willReturn($physicians);

        $this->assertTrue($prescribing_service->hasPrescribingPhysician('1603448711301941782'));
    }

    public function testGetPhysicianSpecialityByCode(): void
    {
        $list                = [
            PhysicianSpeciality::hydrate(["code" => "01", "label" => "Medecine generale", "family" => "MG"]),
            PhysicianSpeciality::hydrate(["code" => "02", "label" => "Urologie", "family" => "MG"]),
            PhysicianSpeciality::hydrate(["code" => "03", "label" => "Chirurgie", "family" => "C"]),
        ];
        $prescribing_service = $this->getMockBuilder(PrescribingPhysicianService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPhysicianSpecialitiesList'])
            ->getMock();
        $prescribing_service->method('getPhysicianSpecialitiesList')->willReturn($list);

        $expected = PhysicianSpeciality::hydrate(["code" => "01", "label" => "Medecine generale", "family" => "MG"]);

        $this->assertEquals($expected, $prescribing_service->getPhysicianSpecialityByCode("01"));
    }

    public function testGetPhysicianTypeByCode(): void
    {
        $list                = [
            PhysicianType::hydrate(["code" => 0, "label" => "Liberal"]),
            PhysicianType::hydrate(["code" => 2, "label" => "Employé"]),
            PhysicianType::hydrate(["code" => 3, "label" => "Bénévole"]),
        ];
        $prescribing_service = $this->getMockBuilder(PrescribingPhysicianService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPhysicianTypesList'])
            ->getMock();
        $prescribing_service->method('getPhysicianTypesList')->willReturn($list);

        $expected = PhysicianType::hydrate(["code" => 3, "label" => "Bénévole"]);

        $this->assertEquals($expected, $prescribing_service->getPhysicianTypeByCode(3));
    }

    private function newMockCache(): MockObject
    {
        return $this->getMockBuilder(Cache::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'put'])
            ->getMock();
    }
}
