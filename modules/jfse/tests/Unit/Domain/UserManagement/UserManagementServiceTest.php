<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain;

use Exception;
use Ox\Core\Cache;
use Ox\Mediboard\Jfse\ApiClients\UserManagementClient;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\Cps\Card;
use Ox\Mediboard\Jfse\Domain\Cps\Situation;
use Ox\Mediboard\Jfse\Domain\UserManagement\TariffContract;
use Ox\Mediboard\Jfse\Domain\UserManagement\User;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserConfiguration;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserManagementService;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserParameter;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Populate\Generators\CMediusersGenerator;

class UserManagementServiceTest extends UnitTestJfse
{
    /**
     * @param string $response The JSON response
     * @param User[] $expected The expected users
     *
     * @dataProvider listUsersProvider
     */
    public function testListUsers(string $response, array $expected): void
    {
        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $response)
        ]));

        $service = new UserManagementService($client);

        $actual = $service->listUsers('gris', 'anne', '991129768', '99900065279');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param string $response
     * @param User   $expected
     *
     * @dataProvider getUserProvider
     */
    public function testGetUser(string $response, User $expected): void
    {

        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $response)
        ]));

        $service = new UserManagementService($client);

        $actual = $service->getUser(1, '9911297681grisanne', '1234');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param Card $cps
     * @param string $user_response
     *
     * @dataProvider createUserFromCpsWithOneSituationProvider
     */
    public function testCreateUserFromCpsWithOneSituation(
        Card $cps,
        string $user_response
    ): void {
        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $user_response)
        ]));
        $service = new UserManagementService($client);

        $actual = $service->createUserFromCps($cps);

        $this->assertNotEmpty($actual->getDataModel()->_id);
    }

    /**
     * @param Card   $cps
     * @param string $user_response
     *
     * @dataProvider createUserFromCpsWithMultipleSituationProvider
     */
    public function testCreateUserFromCpsWithMultipleSituationsFailure(
        Card $cps,
        string $user_response
    ): void {
        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $user_response)
        ]));
        $service = new UserManagementService($client);

        $this->expectExceptionMessage('CpsNoSituationSelected');
        $service->createUserFromCps($cps);
    }

    /**
     * @param Card   $cps
     * @param string $user_response
     * @param User   $expected
     *
     * @dataProvider getUpdateUserFromCpsProvider
     */
    public function testUpdateUserFromCps(Card $cps, string $user_response, User $expected): void
    {
        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $user_response)
        ]));
        $service = new UserManagementService($client);

        $actual = $service->updateUserFromCps($cps);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param Card   $cps
     * @param string $user_response
     *
     * @dataProvider createUserFromCpsWithMultipleSituationProvider
     */
    public function testUpdateUserFromCpsWithMultipleSituationsFailure(Card $cps, string $user_response): void
    {
        $this->expectExceptionMessage('CpsNoSituationSelected');

        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $user_response)
        ]));
        $service = new UserManagementService($client);

        $service->updateUserFromCps($cps);
    }

    /**
     * @throws Exception
     */
    public function testLinkUserToMediuserSuccess(): void
    {
        $service = new UserManagementService();
        $this->assertTrue($service->linkUserToMediuser(1, (new CMediusersGenerator())->generate()->_id));
    }

    /**
     * @throws Exception
     */
    public function testLinkUserToMediuserFailure(): void
    {
        $this->expectExceptionMessage('UserAlreadyLinked');
        $service = new UserManagementService();
        $service->unlinkUserToMediuser(1);

        $mediusers = [
            (new CMediusersGenerator())->generate('Médecin'),
            (new CMediusersGenerator())->generate('Anesthésiste')
        ];

        foreach ($mediusers as $mediuser) {
            $service->linkUserToMediuser(1, $mediuser->_id);
        }
    }

    public function testUnlinkUserToMediuser(): void
    {
        $service = new UserManagementService();
        $this->assertTrue($service->unlinkUserToMediuser(1));
    }

    /**
     * @param string $response
     *
     * @dataProvider deleteUserProvider
     */
    public function testDeleteUserSuccess(string $response): void
    {
        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $response)
        ]));

        $service = new UserManagementService($client);

        /* Creates a user with the id 1 in case it doesn't exists */
        $user = new CJfseUser();
        $user->jfse_id = 1;
        $user->loadMatchingObject();
        $user->store();

        $this->assertTrue($service->deleteUser(1));
    }

    /**
     * @param string $response
     *
     * @dataProvider deleteUserProvider
     */
    public function testDeleteUserFailure(string $response): void
    {
        $this->expectExceptionMessage('PersistenceError');
        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $response)
        ]));

        $service = new UserManagementService($client);

        /* Creates a user with the id 1 in case it doesn't exists */
        $user = new CJfseUser();
        $user->jfse_id = 1;
        $user->loadMatchingObject();
        if ($user->_id) {
            $user->delete();
        }

        $service->deleteUser(1);
    }

    /**
     * @param string            $response
     * @param UserConfiguration $expected
     *
     * @dataProvider getUserParametersProvider
     */
    public function testGetUserParameters(string $response, UserConfiguration $expected): void
    {
        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, utf8_encode($response))
        ]));
        $service = new UserManagementService($client);

        $actual = $service->getUserParameters();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param string $response
     *
     * @dataProvider updateUserParameterProvider
     */
    public function testUpdateUserParameter(string $response): void
    {
        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $response)
        ]));
        $service = new UserManagementService($client);
        $this->assertTrue($service->updateUserParameter(1, 'test'));
    }

    /**
     * @param string $response
     *
     * @dataProvider deleteUserParameterProvider
     */
    public function testDeleteUserParameter(string $response): void
    {
        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $response)
        ]));
        $service = new UserManagementService($client);
        $this->assertTrue($service->deleteUserParameter(280));
    }

    /**
     * @param string           $response
     * @param TariffContract[] $expected
     *
     * @dataProvider getListTariffContractsProvider
     */
    public function testGetListTariffContracts(string $response, array $expected): void
    {
        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $response)
        ]));
        $service = new UserManagementService($client, new Cache('', '', Cache::NONE));

        $actual = $service->getListTariffContracts();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param array            $tariffs
     * @param TariffContract[] $expected
     *
     * @dataProvider getListTariffContractsFromCacheProvider
     */
    public function testGetListTariffContractsFromCache(array $tariffs, array $expected): void
    {
        $cache = $this->getMockBuilder(Cache::class)->disableOriginalConstructor()->setMethods(['get'])->getMock();
        $cache->method('get')->willReturn($tariffs);
        $service = new UserManagementService(null, $cache);

        $actual = $service->getListTariffContracts();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param string $response
     * @param string $expected
     *
     * @dataProvider getUserSignatureProvider
     */
    public function testGetUserSignature(string $response, string $expected): void
    {
        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $response)
        ]));
        $service = new UserManagementService($client);

        $actual = $service->getUserSignature(1);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param string $response
     * @param string $expected
     *
     * @dataProvider getUserSignatureFailureProvider
     */
    public function testGetUserSignatureFailure(string $response): void
    {
        $this->expectExceptionMessage('UserSignatureNotFound');

        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $response)
        ]));
        $service = new UserManagementService($client);

        $service->getUserSignature(1);
    }

    /**
     * @param string $response
     *
     * @dataProvider updateUserSignatureProvider
     */
    public function testUpdateUserSignature(string $response): void
    {
        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $response)
        ]));
        $service = new UserManagementService($client);

        $this->assertTrue($service->updateUserSignature(1, 'dummy_signature_file_content_for_testing'));
    }

    /**
     * @param string $response
     *
     * @dataProvider deleteUserSignatureProvider
     */
    public function testDeleteUserSignature(string $response): void
    {
        $client = new UserManagementClient($this->makeClientFromGuzzleResponses([
            $this->makeJsonGuzzleResponse(200, $response)
        ]));
        $service = new UserManagementService($client);

        $this->assertTrue($service->deleteUserSignature(1));
    }

    public function listUsersProvider(): array
    {
        $response_json = <<<JSON
{
    "integrator": {
        "name": "RESIP",
        "key": "TEST"
    },
    "method": {
        "name": "IDE-getListePs",
        "service": true,
        "parameters": {
            "getListePs": {
                "filtre": ""
            }
        },
        "output": {
            "lstInfoPS": [
                {
                    "idJfse": 1,
                    "idEtablissement": 0,
                    "login": "9911297681grisanne",
                    "password": "",
                    "noNational": "99900065279",
                    "typeCartePS": "0",
                    "dernierNumeroFacture": "",
                    "formatageOK": 0,
                    "typeIdentification": "8",
                    "codeCivilite": "22",
                    "libelleCivilite": "",
                    "nomPS": "GRIS",
                    "prenomPS": "ANNE",
                    "adresse1": "",
                    "adresse2": "",
                    "adresse3": "",
                    "adresse4": "",
                    "noPSRemplacant": "",
                    "nomPSRemplacant": "",
                    "prenomPSRemplacant": "",
                    "noSituationPSRemplacant": 0,
                    "noRPPSPSRemplacant": "",
                    "sessionRemplacement": 0,
                    "activationCCAM": 0,
                    "caisseExecutant": "",
                    "modeCS": 0,
                    "modeCNDA": 0,
                    "modeSansCPS": 0,
                    "parcoursSoins": 0,
                    "lstParamPS": {},
                    "situation": {
                        "noLogiqueSituation": 1,
                        "typeIdentificationStructure": "4",
                        "noIdentificationStructure": "999000652790050",
                        "raisonSociale": "CABINET GRIS",
                        "noPSRemplacant": "",
                        "noPSFacturation": "991129768",
                        "codeConventionnel": "3",
                        "codeSpecialite": "07",
                        "libelleSpecialite": "",
                        "familleSpecialite": "",
                        "groupeSpecialite": "",
                        "groupeTarif": "",
                        "codeZoneTarifaire": "30",
                        "codeZoneIK": "01",
                        "codeAgrement1": "0",
                        "codeAgrement2": "0",
                        "codeAgrement3": "0",
                        "habilitationFSE": "1",
                        "habilitationLots": "1",
                        "modeExercice": "0",
                        "statutExercice": "1",
                        "secteurActivite": "31",
                        "CSCodeSpecialite": "",
                        "CSFamillePS": "",
                        "CSCodeConvention": "",
                        "CSCodeZoneTarifaire": "",
                        "CSCodeZoneIK": "",
                        "libelleCentre": "",
                        "modeCS": 0,
                        "contratTarifaire": 0,
                        "contexte": 0
                    }
                },
                {
                    "idJfse": 2,
                    "idEtablissement": 0,
                    "login": "9911306341bistourinana",
                    "password": "",
                    "noNational": "99900064140",
                    "typeCartePS": "0",
                    "dernierNumeroFacture": "",
                    "formatageOK": 0,
                    "typeIdentification": "8",
                    "codeCivilite": "22",
                    "libelleCivilite": "",
                    "nomPS": "BISTOURI",
                    "prenomPS": "NANA",
                    "adresse1": "",
                    "adresse2": "",
                    "adresse3": "",
                    "adresse4": "",
                    "noPSRemplacant": "",
                    "nomPSRemplacant": "",
                    "prenomPSRemplacant": "",
                    "noSituationPSRemplacant": 0,
                    "noRPPSPSRemplacant": "",
                    "sessionRemplacement": 0,
                    "activationCCAM": 0,
                    "caisseExecutant": "",
                    "modeCS": 0,
                    "modeCNDA": 0,
                    "modeSansCPS": 0,
                    "parcoursSoins": 0,
                    "lstParamPS": {},
                    "situation": {
                        "noLogiqueSituation": 1,
                        "typeIdentificationStructure": "4",
                        "noIdentificationStructure": "999000641400000",
                        "raisonSociale": "CABINET BISTOURI",
                        "noPSRemplacant": "",
                        "noPSFacturation": "991130634",
                        "codeConventionnel": "3",
                        "codeSpecialite": "04",
                        "libelleSpecialite": "",
                        "familleSpecialite": "",
                        "groupeSpecialite": "",
                        "groupeTarif": "",
                        "codeZoneTarifaire": "30",
                        "codeZoneIK": "01",
                        "codeAgrement1": "1",
                        "codeAgrement2": "0",
                        "codeAgrement3": "0",
                        "habilitationFSE": "1",
                        "habilitationLots": "1",
                        "modeExercice": "0",
                        "statutExercice": "1",
                        "secteurActivite": "31",
                        "CSCodeSpecialite": "",
                        "CSFamillePS": "",
                        "CSCodeConvention": "",
                        "CSCodeZoneTarifaire": "",
                        "CSCodeZoneIK": "",
                        "libelleCentre": "",
                        "modeCS": 0,
                        "contratTarifaire": 0,
                        "contexte": 0
                    }
                }
            ]
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    },
    "cardReader": {
        "id": "08-00-27-a7-43-32",
        "protocol": "PCSC",
        "channel": "0",
        "reader": "0",
        "cps": "",
        "vitale": ""
    },
    "returnMode": {
        "mode": 1,
        "URL": ""
    },
    "idJfse": 1
}
JSON;

        $users = [
            User::hydrate([
                'id'                                       => 1,
                'establishment_id'                         => 0,
                'login'                                    => '9911297681grisanne',
                'password'                                 => '',
                'national_identification_type_code'        => 8,
                'national_identification_number'           => '99900065279',
                'civility_code'                            => 22,
                'last_name'                                => 'GRIS',
                'first_name'                               => 'ANNE',
                'address'                                  => '',
                'ccam_activation'                          => false,
                'health_insurance_agency'                  => '',
                'health_center'                            => false,
                'cnda_mode'                                => false,
                'cardless_mode'                            => false,
                'care_path'                                => 0,
                'card_type'                                => 0,
                'last_fse_number'                          => 0,
                'formatting'                               => false,
                'substitute_number'                        => '',
                'substitute_last_name'                     => '',
                'substitute_first_name'                    => '',
                'substitute_situation_number'              => 0,
                'substitute_rpps_number'                   => '',
                'substitution_session'                     => 0,
                'parameters'                               => [],
                'situation'                                => Situation::hydrate([
                    'practitioner_id'           => 0,
                    'situation_id'              => 0,
                    'structure_identifier_type' => 4,
                    'structure_identifier'      => '999000652790050',
                    'structure_name'            => 'CABINET GRIS',
                    'invoicing_number'          => '991129768',
                    'substitute_number'         => '',
                    'convention_code'           => 3,
                    'speciality_code'           => 7,
                    'speciality_label'          => '',
                    'speciality_group'          => '',
                    'price_zone_code'           => 30,
                    'distance_allowance_code'   => 1,
                    'approval_codes'            => [],
                    'approval_labels'            => [],
                    'fse_signing_authorisation' => true,
                    'lot_signing_authorisation' => true,
                    'practice_mode'             => 0,
                    'practice_status'           => 1,
                    'activity_sector'           => 31
                ]),
            ]),
            User::hydrate([
                'id'                                       => 2,
                'establishment_id'                         => 0,
                'login'                                    => '9911306341bistourinana',
                'password'                                 => '',
                'national_identification_type_code'        => 8,
                'national_identification_number'           => '99900064140',
                'civility_code'                            => 22,
                'last_name'                                => 'BISTOURI',
                'first_name'                               => 'NANA',
                'address'                                  => '',
                'ccam_activation'                          => false,
                'health_insurance_agency'                  => '',
                'health_center'                            => false,
                'cnda_mode'                                => false,
                'cardless_mode'                            => false,
                'care_path'                                => 0,
                'card_type'                                => 0,
                'last_fse_number'                          => 0,
                'formatting'                               => false,
                'substitute_number'                        => '',
                'substitute_last_name'                     => '',
                'substitute_first_name'                    => '',
                'substitute_situation_number'              => 0,
                'substitute_rpps_number'                   => '',
                'substitution_session'                     => 0,
                'parameters'                               => [],
                'situation'                                => Situation::hydrate([
                    'practitioner_id'           => 0,
                    'situation_id'              => 0,
                    'structure_identifier_type' => 4,
                    'structure_identifier'      => '999000641400000',
                    'structure_name'            => 'CABINET BISTOURI',
                    'invoicing_number'          => '991130634',
                    'substitute_number'         => '',
                    'convention_code'           => 3,
                    'speciality_code'           => 4,
                    'speciality_label'          => '',
                    'speciality_group'          => '',
                    'price_zone_code'           => 30,
                    'distance_allowance_code'   => 1,
                    'approval_codes'            => [],
                    'approval_labels'           => [],
                    'fse_signing_authorisation' => true,
                    'lot_signing_authorisation' => true,
                    'practice_mode'             => 0,
                    'practice_status'           => 1,
                    'activity_sector'           => 31
                ])
            ])
        ];

        return [['response' => utf8_encode($response_json), 'users' => $users]];
    }

    public function getUserProvider(): array
    {
        $response = <<<JSON
{
    "method": {
        "name": "IDE-getInfoPS",
        "service": true,
        "parameters": {
            "idJfse": 1
        },
        "output": {
            "idJfse": 156148484515484515,
            "idEtablissement": 0,
            "login": "9911297681grisanne",
            "password": "",
            "noNational": "99900065279",
            "typeCartePS": "0",
            "dernierNumeroFacture": "000000000",
            "formatageOK": 1,
            "typeIdentification": "8",
            "codeCivilite": "22",
            "libelleCivilite": "Madame",
            "nomPS": "GRIS",
            "prenomPS": "ANNE",
            "adresse1": "",
            "adresse2": "",
            "adresse3": "",
            "adresse4": "",
            "noPSRemplacant": "",
            "nomPSRemplacant": "",
            "prenomPSRemplacant": "",
            "noSituationPSRemplacant": 0,
            "noRPPSPSRemplacant": "",
            "sessionRemplacement": 0,
            "activationCCAM": 0,
            "caisseExecutant": "",
            "modeCS": 0,
            "modeCNDA": 0,
            "modeSansCPS": 0,
            "parcoursSoins": 1,
            "lstParamPS": {
                "66": {
                    "id": 66,
                    "name": "PS Partenaire de regime de sante des mines",
                    "value": "0"
                },
                "204": {
                    "id": 204,
                    "name": "ADRI",
                    "value": "2"
                },
                "14": {
                    "id": 14,
                    "name": "Dernier num. fichier",
                    "value": "000"
                },
                "206": {
                    "id": 206,
                    "name": "IMTI",
                    "value": "1"
                },
                "15": {
                    "id": 15,
                    "name": "Dernier num. lot FSE",
                    "value": "000"
                },
                "85": {
                    "id": 85,
                    "name": "Delai avant chainage",
                    "value": "0"
                },
                "153": {
                    "id": 153,
                    "name": "Date installation generaliste",
                    "value": "21/10/2020"
                },
                "154": {
                    "id": 154,
                    "name": "Date installation zone sous medicalisee",
                    "value": "23/10/2020"
                },
                "155": {
                    "id": 155,
                    "name": "Contrat tarifaire",
                    "value": "1"
                },
                "157": {
                    "id": 157,
                    "name": "Login POP3",
                    "value": ""
                },
                "30": {
                    "id": 30,
                    "name": "Type emetteur fichier",
                    "value": "TP"
                },
                "158": {
                    "id": 158,
                    "name": "Password POP3",
                    "value": ""
                },
                "31": {
                    "id": 31,
                    "name": "Num. emetteur fichier",
                    "value": "00000991129768"
                },
                "159": {
                    "id": 159,
                    "name": "Adresse serveur SMTP",
                    "value": ""
                },
                "32": {
                    "id": 32,
                    "name": "Délai acquittement",
                    "value": "2"
                },
                "160": {
                    "id": 160,
                    "name": "Adresse serveur POP3",
                    "value": ""
                },
                "161": {
                    "id": 161,
                    "name": "Adresse électronique",
                    "value": ""
                },
                "101": {
                    "id": 101,
                    "name": "Demande de non utilisation TLA",
                    "value": "0"
                },
                "104": {
                    "id": 104,
                    "name": "Activation CCAM",
                    "value": "0"
                },
                "107": {
                    "id": 107,
                    "name": "Caisse de l'executant",
                    "value": ""
                },
                "110": {
                    "id": 110,
                    "name": "Restriction STS CMU",
                    "value": "0"
                },
                "115": {
                    "id": 115,
                    "name": "Dernier num lot DRE",
                    "value": "ZZZ"
                },
                "116": {
                    "id": 116,
                    "name": "Niveau de diagnostic",
                    "value": "1"
                },
                "57": {
                    "id": 57,
                    "name": "PS SNCF",
                    "value": "0"
                },
                "58": {
                    "id": 58,
                    "name": "PS RATP",
                    "value": "0"
                },
                "126": {
                    "id": 126,
                    "name": "Type application",
                    "value": ""
                },
                "127": {
                    "id": 127,
                    "name": "Type destinataire",
                    "value": ""
                }
            },
            "situation": {
                "noLogiqueSituation": 1,
                "typeIdentificationStructure": "4",
                "noIdentificationStructure": "999000652790050",
                "raisonSociale": "CABINET GRIS",
                "noPSRemplacant": "",
                "noPSFacturation": "991129768",
                "codeConventionnel": "3",
                "codeSpecialite": "07",
                "libelleSpecialite": "Gynécologie obstétrique",
                "familleSpecialite": "PR",
                "groupeSpecialite": "MD",
                "groupeTarif": "CO",
                "codeZoneTarifaire": "30",
                "codeZoneIK": "01",
                "codeAgrement1": "0",
                "codeAgrement2": "0",
                "codeAgrement3": "0",
                "habilitationFSE": "1",
                "habilitationLots": "1",
                "modeExercice": "0",
                "statutExercice": "1",
                "secteurActivite": "31",
                "CSCodeSpecialite": "",
                "CSFamillePS": "PR1",
                "CSCodeConvention": "",
                "CSCodeZoneTarifaire": "",
                "CSCodeZoneIK": "",
                "libelleCentre": "Aucun",
                "modeCS": 0,
                "contratTarifaire": 0,
                "contexte": 42
            }
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    }
}
JSON;

        $jfse_user = new CJfseUser();
        $jfse_user->jfse_id = '156148484515484515';

        $user = User::hydrate([
            'id'                                       => '156148484515484515',
            'establishment_id'                         => 0,
            'login'                                    => '9911297681grisanne',
            'password'                                 => '',
            'national_identification_type_code'        => 8,
            'national_identification_number'           => '99900065279',
            'civility_code'                            => 22,
            'last_name'                                => 'GRIS',
            'first_name'                               => 'ANNE',
            'address'                                  => '',
            'ccam_activation'                          => false,
            'health_insurance_agency'                  => '',
            'health_center'                            => false,
            'cnda_mode'                                => false,
            'cardless_mode'                            => false,
            'care_path'                                => 1,
            'card_type'                                => 0,
            'last_fse_number'                          => 0,
            'formatting'                               => true,
            'substitute_number'                        => '',
            'substitute_last_name'                     => '',
            'substitute_first_name'                    => '',
            'substitute_situation_number'              => 0,
            'substitute_rpps_number'                   => '',
            'substitution_session'                     => 0,
            'data_model'                               => $jfse_user,
            'parameters'                               => [
                66 => UserParameter::hydrate([
                    'id' => 66, 'name' => "PS Partenaire de regime de sante des mines", 'value' => "0"
                ]),
                204 => UserParameter::hydrate([
                    'id' => 204, 'name' => "ADRI", 'value' => "2"
                ]),
                14 => UserParameter::hydrate([
                    'id' => 14, 'name' => "Dernier num. fichier", 'value' => "000"
                ]),
                206 => UserParameter::hydrate([
                    'id' => 206, 'name' => "IMTI", 'value' => "1"
                ]),
                15 => UserParameter::hydrate([
                    'id' => 15, 'name' => "Dernier num. lot FSE", 'value' => "000"
                ]),
                85 => UserParameter::hydrate([
                    'id' => 85, 'name' => "Delai avant chainage", 'value' => "0"
                ]),
                153 => UserParameter::hydrate([
                    'id' => 153, 'name' => "Date installation generaliste", 'value' => "21/10/2020"
                ]),
                154 => UserParameter::hydrate([
                    'id' => 154, 'name' => "Date installation zone sous medicalisee", 'value' => "23/10/2020"
                ]),
                155 => UserParameter::hydrate([
                    'id' => 155, 'name' => "Contrat tarifaire", 'value' => "1"
                ]),
                157 => UserParameter::hydrate([
                    'id' => 157, 'name' => "Login POP3", 'value' => ""
                ]),
                30 => UserParameter::hydrate([
                    'id' => 30, 'name' => "Type emetteur fichier", 'value' => "TP"
                ]),
                158 => UserParameter::hydrate([
                    'id' => 158, 'name' => "Password POP3", 'value' => ""
                ]),
                31 => UserParameter::hydrate([
                    'id' => 31, 'name' => "Num. emetteur fichier", 'value' => "00000991129768"
                ]),
                159 => UserParameter::hydrate([
                    'id' => 159, 'name' => "Adresse serveur SMTP", 'value' => ""
                ]),
                32 => UserParameter::hydrate([
                    'id' => 32, 'name' => "Délai acquittement", 'value' => "2"
                ]),
                160 => UserParameter::hydrate([
                    'id' => 160, 'name' => "Adresse serveur POP3", 'value' => ""
                ]),
                161 => UserParameter::hydrate([
                    'id' => 161, 'name' => "Adresse électronique", 'value' => ""
                ]),
                101 => UserParameter::hydrate([
                    'id' => 101, 'name' => "Demande de non utilisation TLA", 'value' => "0"
                ]),
                104 => UserParameter::hydrate([
                    'id' => 104, 'name' => "Activation CCAM", 'value' => "0"
                ]),
                107 => UserParameter::hydrate([
                    'id' => 107, 'name' => "Caisse de l'executant", 'value' => ""
                ]),
                110 => UserParameter::hydrate([
                    'id' => 110, 'name' => "Restriction STS CMU", 'value' => "0"
                ]),
                115 => UserParameter::hydrate([
                    'id' => 115, 'name' => "Dernier num lot DRE", 'value' => "ZZZ"
                ]),
                116 => UserParameter::hydrate([
                    'id' => 116, 'name' => "Niveau de diagnostic", 'value' => "1"
                ]),
                57 => UserParameter::hydrate([
                    'id' => 57, 'name' => "PS SNCF", 'value' => "0"
                ]),
                58 => UserParameter::hydrate([
                    'id' => 58, 'name' => "PS RATP", 'value' => "0"
                ]),
                126 => UserParameter::hydrate([
                    'id' => 126, 'name' => "Type application", 'value' => ""
                ]),
                127 => UserParameter::hydrate([
                    'id' => 127, 'name' => "Type destinataire", 'value' => ""
                ])
            ],
            'situation'                                => Situation::hydrate([
                'practitioner_id'           => 0,
                'situation_id'              => 0,
                'structure_identifier_type' => 4,
                'structure_identifier'      => '999000652790050',
                'structure_name'            => 'CABINET GRIS',
                'invoicing_number'          => '991129768',
                'substitute_number'         => '',
                'convention_code'           => 3,
                'speciality_code'           => 7,
                'speciality_label'          => 'Gynécologie obstétrique',
                'speciality_group'          => 'PR',
                'price_zone_code'           => 30,
                'distance_allowance_code'   => 1,
                'approval_codes'            => [],
                'approval_labels'            => [],
                'fse_signing_authorisation' => true,
                'lot_signing_authorisation' => true,
                'practice_mode'             => 0,
                'practice_status'           => 1,
                'activity_sector'           => 31
            ]),
        ]);

        return [['response' => utf8_encode($response), 'user' => $user]];
    }

    public function createUserFromCpsWithOneSituationProvider(): array
    {
        $cps = Card::hydrate([
            'type_code'                          => 0,
            'type_label'                         => 'Carte de Professionnel de Santé',
            'national_identification_type_code'  => 8,
            'national_identification_type_label' => 'No RPPS',
            'national_identification_number'     => '9990006527',
            'national_identification_key'        => '9',
            'civility_code'                      => 22,
            'civility_label'                     => 'Madame',
            'last_name'                          => 'GRIS',
            'first_name'                         => 'ANNE',
            'situations'                         => [
                Situation::hydrate([
                    'practitioner_id'           => 1,
                    'situation_id'              => 1,
                    'structure_identifier_type' => 4,
                    'structure_identifier'      => '999000652790050',
                    'structure_name'            => 'CABINET GRIS',
                    'invoicing_number'          => '99112976',
                    'invoicing_number_key'      => '8',
                    'substitute_number'         => '',
                    'convention_code'           => 3,
                    'convention_label'          => 'PS conventionné avec honoraires libres',
                    'speciality_code'           => 7,
                    'speciality_label'          => 'Gynécologie obstétrique',
                    'speciality_group'          => 'PR',
                    'price_zone_code'           => 30,
                    'price_zone_label'          => 'Autres dép. et localités avec IFA et IK',
                    'distance_allowance_code'   => 1,
                    'distance_allowance_label'  => 'Indemnités kilométriques plaine',
                    'approval_codes'            => [1 => 0, 2 => 0, 3 => 0],
                    'approval_labels'           => [
                        1 => 'Pas d\'agrément radio',
                        2 => 'Pas d\'agrément radio',
                        3 => 'Pas d\'agrément radio',
                    ],
                    'fse_signing_authorisation' => true,
                    'lot_signing_authorisation' => true,
                    'practice_mode'             => 0,
                    'practice_status'           => 1,
                    'activity_sector'           => 31,
                ]),
            ],
        ]);

        $user_response = <<<JSON
{
    "integrator": {
        "name": "RESIP",
        "key": "TEST"
    },
    "method": {
        "name": "IDE-updateUtilisateurViaCPS",
        "service": true,
        "parameters": {
            "updateUtilisateurViaCPS": {}
        },
        "output": {
            "typeCarte": 0,
            "libelleTypeCarte": "Carte de Professionnel de Santé",
            "typeIdentificationNational": 8,
            "libelleTypeIdentificationNational": "No RPPS",
            "numIdentificationNational": "9990006414",
            "cleIdentificationNational": "0",
            "codeCivilite": "22",
            "libelleCodeCivilite": "Madame",
            "nom": "BISTOURI",
            "prenom": "NANA",
            "lstCpsSituation": [
                {
                    "identifiantLogique": 1,
                    "typeIdentificationStructure": 4,
                    "numIdentificationStructure": "999000641400000",
                    "raisonSociale": "CABINET BISTOURI",
                    "numIdentificationPSFacturation": "99113063",
                    "cleIdentificationPSFacturation": "4",
                    "numIdentificationPSRemplacant": "",
                    "codeConventionnel": "3",
                    "libelleCodeConventionnel": "PS conventionné avec honoraires libres",
                    "codeSpecialite": "04",
                    "libelleCodeSpecialite": "Chirurgie générale",
                    "familleSpecialite": "PR",
                    "codeZoneTarif": "30",
                    "libelleCodeZoneTarif": "Autres dép. et localités avec IFA et IK",
                    "codeZoneIK": "01",
                    "libelleCodeZoneIK": "Indemnités kilométriques plaine",
                    "codeAgrement1": "1",
                    "libelleCodeAgrement1": "Agrément D ou agrément DDASS",
                    "codeAgrement2": "0",
                    "libelleCodeAgrement2": "Pas d'agrément radio",
                    "codeAgrement3": "0",
                    "libelleCodeAgrement3": "Pas d'agrément radio",
                    "habilitationSignerFSE": 1,
                    "habilitationSignerLOT": 1,
                    "modeExercice": 0,
                    "statusExercice": 1,
                    "secteurActivite": 31,
                    "lstTitulaires": [],
                    "idJfse": 2,
                    "nouveauMedecin": true
                }
            ]
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    },
    "cardReader": {
        "id": "08-00-27-a7-43-32",
        "protocol": "PCSC",
        "channel": "0",
        "reader": "0",
        "cps": "",
        "vitale": ""
    },
    "returnMode": {
        "mode": 1,
        "URL": ""
    },
    "idJfse": 1
}
JSON;

        return [[
            'cps' => $cps,
            'user_response' => utf8_encode($user_response),
        ]];
    }

    public function createUserFromCpsWithMultipleSituationProvider(): array
    {
        $cps = Card::hydrate([
            'type_code'                          => 0,
            'type_label'                         => 'Carte de Professionnel de Santé',
            'national_identification_type_code'  => 8,
            'national_identification_type_label' => 'No RPPS',
            'national_identification_number'     => '9990006527',
            'national_identification_key'        => '9',
            'civility_code'                      => 22,
            'civility_label'                     => 'Madame',
            'last_name'                          => 'GRIS',
            'first_name'                         => 'ANNE',
            'situations'                         => [
                Situation::hydrate([
                    'practitioner_id'           => 1,
                    'situation_id'              => 1,
                    'structure_identifier_type' => 4,
                    'structure_identifier'      => '999000652790050',
                    'structure_name'            => 'CABINET GRIS',
                    'invoicing_number'          => '99112976',
                    'invoicing_number_key'      => '8',
                    'substitute_number'         => '',
                    'convention_code'           => 3,
                    'convention_label'          => 'PS conventionné avec honoraires libres',
                    'speciality_code'           => 7,
                    'speciality_label'          => 'Gynécologie obstétrique',
                    'speciality_group'          => 'PR',
                    'price_zone_code'           => 30,
                    'price_zone_label'          => 'Autres dép. et localités avec IFA et IK',
                    'distance_allowance_code'   => 1,
                    'distance_allowance_label'  => 'Indemnités kilométriques plaine',
                    'approval_codes'            => [1 => 0, 2 => 0, 3 => 0],
                    'approval_labels'           => [
                        1 => 'Pas d\'agrément radio',
                        2 => 'Pas d\'agrément radio',
                        3 => 'Pas d\'agrément radio',
                    ],
                    'fse_signing_authorisation' => true,
                    'lot_signing_authorisation' => true,
                    'practice_mode'             => 0,
                    'practice_status'           => 1,
                    'activity_sector'           => 31,
                ]),
                Situation::hydrate([
                    'practitioner_id'           => 1,
                    'situation_id'              => 1,
                    'structure_identifier_type' => 4,
                    'structure_identifier'      => '999000652790050',
                    'structure_name'            => 'CABINET GRIS',
                    'invoicing_number'          => '99112976',
                    'invoicing_number_key'      => '8',
                    'substitute_number'         => '',
                    'convention_code'           => 3,
                    'convention_label'          => 'PS conventionné avec honoraires libres',
                    'speciality_code'           => 7,
                    'speciality_label'          => 'Gynécologie obstétrique',
                    'speciality_group'          => 'PR',
                    'price_zone_code'           => 30,
                    'price_zone_label'          => 'Autres dép. et localités avec IFA et IK',
                    'distance_allowance_code'   => 1,
                    'distance_allowance_label'  => 'Indemnités kilométriques plaine',
                    'approval_codes'            => [1 => 0, 2 => 0, 3 => 0],
                    'approval_labels'           => [
                        1 => 'Pas d\'agrément radio',
                        2 => 'Pas d\'agrément radio',
                        3 => 'Pas d\'agrément radio',
                    ],
                    'fse_signing_authorisation' => true,
                    'lot_signing_authorisation' => true,
                    'practice_mode'             => 0,
                    'practice_status'           => 1,
                    'activity_sector'           => 31,
                ]),
            ],
        ]);

        $user_response = <<<JSON
{
    "integrator": {
        "name": "RESIP",
        "key": "TEST"
    },
    "method": {
        "name": "IDE-updateUtilisateurViaCPS",
        "service": true,
        "parameters": {
            "updateUtilisateurViaCPS": {}
        },
        "output": {
            "typeCarte": 0,
            "libelleTypeCarte": "Carte de Professionnel de Santé",
            "typeIdentificationNational": 8,
            "libelleTypeIdentificationNational": "No RPPS",
            "numIdentificationNational": "9990006414",
            "cleIdentificationNational": "0",
            "codeCivilite": "22",
            "libelleCodeCivilite": "Madame",
            "nom": "BISTOURI",
            "prenom": "NANA",
            "lstCpsSituation": [
                {
                    "identifiantLogique": 1,
                    "typeIdentificationStructure": 4,
                    "numIdentificationStructure": "999000641400000",
                    "raisonSociale": "CABINET BISTOURI",
                    "numIdentificationPSFacturation": "99113063",
                    "cleIdentificationPSFacturation": "4",
                    "numIdentificationPSRemplacant": "",
                    "codeConventionnel": "3",
                    "libelleCodeConventionnel": "PS conventionné avec honoraires libres",
                    "codeSpecialite": "04",
                    "libelleCodeSpecialite": "Chirurgie générale",
                    "familleSpecialite": "PR",
                    "codeZoneTarif": "30",
                    "libelleCodeZoneTarif": "Autres dép. et localités avec IFA et IK",
                    "codeZoneIK": "01",
                    "libelleCodeZoneIK": "Indemnités kilométriques plaine",
                    "codeAgrement1": "1",
                    "libelleCodeAgrement1": "Agrément D ou agrément DDASS",
                    "codeAgrement2": "0",
                    "libelleCodeAgrement2": "Pas d'agrément radio",
                    "codeAgrement3": "0",
                    "libelleCodeAgrement3": "Pas d'agrément radio",
                    "habilitationSignerFSE": 1,
                    "habilitationSignerLOT": 1,
                    "modeExercice": 0,
                    "statusExercice": 1,
                    "secteurActivite": 31,
                    "lstTitulaires": [],
                    "idJfse": 2,
                    "nouveauMedecin": true
                }
            ]
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    },
    "cardReader": {
        "id": "08-00-27-a7-43-32",
        "protocol": "PCSC",
        "channel": "0",
        "reader": "0",
        "cps": "",
        "vitale": ""
    },
    "returnMode": {
        "mode": 1,
        "URL": ""
    },
    "idJfse": 1
}
JSON;

        return [[
            'cps' => $cps,
            'user_response' => utf8_encode($user_response),
        ]];
    }

    public function getUpdateUserFromCpsProvider(): array
    {
        $cps = Card::hydrate([
            'type_code'                          => 0,
            'type_label'                         => 'Carte de Professionnel de Santé',
            'national_identification_type_code'  => 8,
            'national_identification_type_label' => 'No RPPS',
            'national_identification_number'     => '9990006527',
            'national_identification_key'        => '9',
            'civility_code'                      => 22,
            'civility_label'                     => 'Madame',
            'last_name'                          => 'GRIS',
            'first_name'                         => 'ANNE',
            'situations'                         => [
                Situation::hydrate([
                    'practitioner_id'           => 1,
                    'situation_id'              => 1,
                    'structure_identifier_type' => 4,
                    'structure_identifier'      => '999000652790050',
                    'structure_name'            => 'CABINET GRIS',
                    'invoicing_number'          => '99112976',
                    'invoicing_number_key'      => '8',
                    'substitute_number'         => '',
                    'convention_code'           => 3,
                    'convention_label'          => 'PS conventionné avec honoraires libres',
                    'speciality_code'           => 7,
                    'speciality_label'          => 'Gynécologie obstétrique',
                    'speciality_group'          => 'PR',
                    'price_zone_code'           => 30,
                    'price_zone_label'          => 'Autres dép. et localités avec IFA et IK',
                    'distance_allowance_code'   => 1,
                    'distance_allowance_label'  => 'Indemnités kilométriques plaine',
                    'approval_codes'            => [1 => 0, 2 => 0, 3 => 0],
                    'approval_labels'           => [
                        1 => 'Pas d\'agrément radio',
                        2 => 'Pas d\'agrément radio',
                        3 => 'Pas d\'agrément radio',
                    ],
                    'fse_signing_authorisation' => true,
                    'lot_signing_authorisation' => true,
                    'practice_mode'             => 0,
                    'practice_status'           => 1,
                    'activity_sector'           => 31,
                ]),
            ],
        ]);

        $user_response = <<<JSON
{
    "integrator": {
        "name": "RESIP",
        "key": "TEST"
    },
    "method": {
        "name": "IDE-updateUtilisateurViaCPS",
        "service": true,
        "parameters": {
            "updateUtilisateurViaCPS": {}
        },
        "output": {
            "typeCarte": 0,
            "libelleTypeCarte": "Carte de Professionnel de Santé",
            "typeIdentificationNational": 8,
            "libelleTypeIdentificationNational": "No RPPS",
            "numIdentificationNational": "9990006414",
            "cleIdentificationNational": "0",
            "codeCivilite": "22",
            "libelleCodeCivilite": "Madame",
            "nom": "BISTOURI",
            "prenom": "NANA",
            "lstCpsSituation": [
                {
                    "identifiantLogique": 1,
                    "typeIdentificationStructure": 4,
                    "numIdentificationStructure": "999000641400000",
                    "raisonSociale": "CABINET BISTOURI",
                    "numIdentificationPSFacturation": "99113063",
                    "cleIdentificationPSFacturation": "4",
                    "numIdentificationPSRemplacant": "",
                    "codeConventionnel": "3",
                    "libelleCodeConventionnel": "PS conventionné avec honoraires libres",
                    "codeSpecialite": "04",
                    "libelleCodeSpecialite": "Chirurgie générale",
                    "familleSpecialite": "PR",
                    "codeZoneTarif": "30",
                    "libelleCodeZoneTarif": "Autres dép. et localités avec IFA et IK",
                    "codeZoneIK": "01",
                    "libelleCodeZoneIK": "Indemnités kilométriques plaine",
                    "codeAgrement1": "1",
                    "libelleCodeAgrement1": "Agrément D ou agrément DDASS",
                    "codeAgrement2": "0",
                    "libelleCodeAgrement2": "Pas d'agrément radio",
                    "codeAgrement3": "0",
                    "libelleCodeAgrement3": "Pas d'agrément radio",
                    "habilitationSignerFSE": 1,
                    "habilitationSignerLOT": 1,
                    "modeExercice": 0,
                    "statusExercice": 1,
                    "secteurActivite": 31,
                    "lstTitulaires": [],
                    "idJfse": 2,
                    "nouveauMedecin": true
                }
            ]
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    },
    "cardReader": {
        "id": "08-00-27-a7-43-32",
        "protocol": "PCSC",
        "channel": "0",
        "reader": "0",
        "cps": "",
        "vitale": ""
    },
    "returnMode": {
        "mode": 1,
        "URL": ""
    },
    "idJfse": 1
}
JSON;

        $user = User::hydrate([
            'id'                                       => 0,
            'establishment_id'                         => 0,
            'national_identification_type_code'        => 0,
            'national_identification_number'           => '9990006414',
            'civility_code'                            => 22,
            'civility_label'                           => 'Madame',
            'last_name'                                => 'BISTOURI',
            'first_name'                               => 'NANA',
            'address'                                  => '',
            'ccam_activation'                          => false,
            'health_center'                            => false,
            'cnda_mode'                                => false,
            'cardless_mode'                            => false,
            'care_path'                                => 0,
            'card_type'                                => 0,
            'last_fse_number'                          => 0,
            'formatting'                               => false,
            'substitute_situation_number'              => 0,
            'substitution_session'                     => 0,
            'parameters'                               => [],
            'situation'                                => Situation::hydrate([
                'practitioner_id'           => 2,
                'situation_id'              => 1,
                'structure_identifier_type' => 4,
                'structure_identifier'      => '999000641400000',
                'structure_name'            => 'CABINET BISTOURI',
                'invoicing_number'          => '99113063',
                'invoicing_number_key'      => '4',
                'substitute_number'         => '',
                'convention_code'           => 3,
                'convention_label'          => 'PS conventionné avec honoraires libres',
                'speciality_code'           => 4,
                'speciality_label'          => 'Chirurgie générale',
                'speciality_group'          => 'PR',
                'price_zone_code'           => 30,
                'price_zone_label'          => 'Autres dép. et localités avec IFA et IK',
                'distance_allowance_code'   => 1,
                'distance_allowance_label'  => 'Indemnités kilométriques plaine',
                'approval_codes'            => [1 => 1, 2 => 0, 3 => 0],
                'approval_labels'           => [
                    1 => 'Agrément D ou agrément DDASS',
                    2 => 'Pas d\'agrément radio',
                    3 => 'Pas d\'agrément radio'
                ],
                'fse_signing_authorisation' => true,
                'lot_signing_authorisation' => true,
                'practice_mode'             => 0,
                'practice_status'           => 1,
                'activity_sector'           => 31
            ])
        ]);

        return [[
            'cps' => $cps,
            'user_response' => utf8_encode($user_response),
            'user' => $user
        ]];
    }

    public function deleteUserProvider(): array
    {
        $response = <<<JSON
{
    "method": {
        "name": "IDE-deleteUtilisateur",
        "service": true,
        "parameters": {
            "deleteUtilisateur": {
                "idUtilisateur": 2
            }
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    }
}
JSON;

        return [['response' => $response]];
    }

    public function getUserParametersProvider(): array
    {
        $response = <<<JSON
{
    "method": {
        "name": "IDE-getParamsPs",
        "service": true,
        "parameters": {
            "getParamsPs": {}
        },
        "output": {
            "infoPs": {
                "id": "1594907869353300014",
                "identifiant": 1,
                "idEtablissement": 0,
                "sansCps": 0,
                "codeCivilite": "22",
                "nomMedecin": "GRIS",
                "prenomMedecin": "ANNE",
                "adresse1": "",
                "adresse2": "",
                "adresse3": "",
                "adresse4": "",
                "typeCarte": "0",
                "typeImmatriculationNationale": "8",
                "noImmatriculationNir": "99900065279",
                "noLogiqueSituation": 1,
                "modeExercice": "0",
                "statutExercice": "1",
                "secteurActivite": "31",
                "typeIdentificationStructure": "4",
                "noIdentificationStructure": "999000652790050",
                "raisonSocialeStucture": "CABINET GRIS",
                "noIdentFacturationPs": "991129768",
                "noIdentRemplacant": "",
                "codeConvention": "3",
                "codeSpecialite": "07",
                "codeZoneTarifaire": "30",
                "codeZoneIk": "01",
                "codeAgrement1": "0",
                "codeAgrement2": "0",
                "codeAgrement3": "0",
                "habilitationFse": "1",
                "habilitationLot": "1",
                "infoConvCodeSpecialite": "",
                "infoConvCodeConvention": "",
                "infoConvCodeZoneTarifaire": "",
                "infoConvCodeZoneIk": "",
                "remplacantNomMedecin": "",
                "remplacantPrenomMedecin": "",
                "remplacantNoIdentPs": "",
                "remplacantNoSituation": 0,
                "remplacantNoRpps": "",
                "sessionremplacementactive": 0,
                "buffergroupeinfo": "",
                "login": "9911297681grisanne",
                "password": "",
                "favActeNgap": "",
                "favActeCcam": "",
                "favActeCcamCodereg": "",
                "favActeCcamLibcodereg": "",
                "favCotation": "",
                "favCotationLib": "",
                "csCodeSpecialite": "",
                "csCodeConvention": "",
                "csCodeZoneTarifaire": "",
                "csCodeZoneIk": "",
                "famille": "PR1",
                "famillePS": 0
            },
            "repertoireConvention": "C:\\\\JFSE\\\\serveur\\\\lib/datas/1",
            "lstCerfas": [
                "cerfaFSE-11104-3.jrxml",
                "cerfaFSE-11388-4.jrxml",
                "cerfaFSE-11389-5.jrxml",
                "cerfaFSE-11390-3.jrxml",
                "cerfaFSE-11390-4.jrxml",
                "cerfaFSE-12541-1.jrxml",
                "cerfaFSE-12541-2.jrxml"
            ],
            "lstParam": [
                {
                    "id": 14,
                    "name": "Dernier num. fichier",
                    "value": "000"
                },
                {
                    "id": 15,
                    "name": "Dernier num. lot FSE",
                    "value": "000"
                },
                {
                    "id": 30,
                    "name": "Type emetteur fichier",
                    "value": "TP"
                },
                {
                    "id": 31,
                    "name": "Num. emetteur fichier",
                    "value": "00000991129768"
                },
                {
                    "id": 32,
                    "name": "Délai acquittement",
                    "value": "2"
                },
                {
                    "id": 33,
                    "name": "Racine par défaut",
                    "value": ""
                },
                {
                    "id": 34,
                    "name": "Demande avis de délivrance",
                    "value": "0"
                },
                {
                    "id": 50,
                    "name": "Version format base",
                    "value": ""
                },
                {
                    "id": 57,
                    "name": "PS SNCF",
                    "value": "0"
                },
                {
                    "id": 58,
                    "name": "PS RATP",
                    "value": "0"
                },
                {
                    "id": 66,
                    "name": "PS Partenaire de regime de sante des mines",
                    "value": "0"
                },
                {
                    "id": 79,
                    "name": "Date pour le tableau de bord",
                    "value": "132014102920151029"
                },
                {
                    "id": 85,
                    "name": "Delai avant chainage",
                    "value": "0"
                },
                {
                    "id": 90,
                    "name": "Centre de santé",
                    "value": "0"
                },
                {
                    "id": 101,
                    "name": "Demande de non utilisation TLA",
                    "value": "0"
                },
                {
                    "id": 104,
                    "name": "Activation CCAM",
                    "value": "0"
                },
                {
                    "id": 107,
                    "name": "Caisse de l'executant",
                    "value": ""
                },
                {
                    "id": 109,
                    "name": "PMSS",
                    "value": "3377"
                },
                {
                    "id": 110,
                    "name": "Restriction STS CMU",
                    "value": "0"
                },
                {
                    "id": 115,
                    "name": "Dernier num lot DRE",
                    "value": "ZZZ"
                },
                {
                    "id": 116,
                    "name": "Niveau de diagnostic",
                    "value": "1"
                },
                {
                    "id": 126,
                    "name": "Type application",
                    "value": ""
                },
                {
                    "id": 127,
                    "name": "Type destinataire",
                    "value": ""
                },
                {
                    "id": 150,
                    "name": "Visualisation des groupes d'infos SSV-SRT-STS",
                    "value": "000"
                },
                {
                    "id": 151,
                    "name": "Activation des cartes virtuelles",
                    "value": "0"
                },
                {
                    "id": 153,
                    "name": "Date installation generaliste",
                    "value": "21/10/2020"
                },
                {
                    "id": 154,
                    "name": "Date installation zone sous medicalisee",
                    "value": "23/10/2020"
                },
                {
                    "id": 155,
                    "name": "Contrat tarifaire",
                    "value": "0"
                },
                {
                    "id": 157,
                    "name": "Login POP3",
                    "value": ""
                },
                {
                    "id": 158,
                    "name": "Password POP3",
                    "value": ""
                },
                {
                    "id": 159,
                    "name": "Adresse serveur SMTP",
                    "value": ""
                },
                {
                    "id": 160,
                    "name": "Adresse serveur POP3",
                    "value": ""
                },
                {
                    "id": 161,
                    "name": "Adresse électronique",
                    "value": ""
                },
                {
                    "id": 162,
                    "name": "Flux vers CNDA",
                    "value": "0"
                },
                {
                    "id": 163,
                    "name": "Paramétrage des contrats responsables",
                    "value": "17.5@15@23@2@3@3.27@2.00@2.00@3.27"
                },
                {
                    "id": 164,
                    "name": "Mode CNDA",
                    "value": "0"
                },
                {
                    "id": 165,
                    "name": "Activation log télétransmission",
                    "value": "1"
                },
                {
                    "id": 176,
                    "name": "Transmission OCT@OCT eclateur@NoSiret@FSE enrichie@Adresse dest%OCT ID",
                    "value": "1"
                },
                {
                    "id": 182,
                    "name": "Type recettes",
                    "value": "1"
                },
                {
                    "id": 190,
                    "name": "Activation globale SCOR",
                    "value": "0"
                },
                {
                    "id": 192,
                    "name": "Taille d'un lot SCOR",
                    "value": "1000"
                },
                {
                    "id": 204,
                    "name": "ADRI",
                    "value": "2"
                },
                {
                    "id": 206,
                    "name": "IMTI",
                    "value": "1"
                },
                {
                    "id": 213,
                    "name": "Taille Lot SCOR en Ko",
                    "value": "150"
                },
                {
                    "id": 241,
                    "name": "Nombre de demandes par jour (LOI)",
                    "value": "1"
                },
                {
                    "id": 242,
                    "name": "Nombre de jours max sans incrément avant blocage (LOI)",
                    "value": "7"
                },
                {
                    "id": 243,
                    "name": "Adresse du serveur d''opposition (LOI)",
                    "value": "oppv-loi@opposition.sesam-vitale.fr"
                },
                {
                    "id": 244,
                    "name": "Répertoire de stockage des fichiers LOI",
                    "value": "LOI"
                },
                {
                    "id": 245,
                    "name": "Désactiver contrôles entre dLoi reçu/envoyé",
                    "value": "0"
                },
                {
                    "id": 246,
                    "name": "LOI - Activer contrôle date mise à jour référence LOI",
                    "value": "1"
                },
                {
                    "id": 277,
                    "name": "N/R",
                    "value": "1"
                },
                {
                    "id": 278,
                    "name": "N/R",
                    "value": "0"
                },
                {
                    "id": 279,
                    "name": "N/R",
                    "value": "0"
                },
                {
                    "id": 280,
                    "name": "N/R",
                    "value": "0"
                }
            ]
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    }
}
JSON;

        $config = UserConfiguration::hydrate([
            'user' => User::hydrate([
                'id'         => 1,
                'last_name'  => 'GRIS',
                'first_name' => 'ANNE',
            ]),
            'conventions_folder_path' => "C:\\JFSE\\serveur\\lib/datas/1",
            'cerfas_list' => [
                "cerfaFSE-11104-3.jrxml",
                "cerfaFSE-11388-4.jrxml",
                "cerfaFSE-11389-5.jrxml",
                "cerfaFSE-11390-3.jrxml",
                "cerfaFSE-11390-4.jrxml",
                "cerfaFSE-12541-1.jrxml",
                "cerfaFSE-12541-2.jrxml"
            ],
            'parameters' => [
                14 => UserParameter::hydrate([
                    'id' => 14, 'name' => "Dernier num. fichier", 'value' => "000"
                ]),
                15 => UserParameter::hydrate([
                    'id' => 15, 'name' => "Dernier num. lot FSE", 'value' => "000"
                ]),
                30 => UserParameter::hydrate([
                    'id' => 30, 'name' => "Type emetteur fichier", 'value' => "TP"
                ]),
                31 => UserParameter::hydrate([
                    'id' => 31, 'name' => "Num. emetteur fichier", 'value' => "00000991129768"
                ]),
                32 => UserParameter::hydrate([
                    'id' => 32, 'name' => "Délai acquittement", 'value' => "2"
                ]),
                33 => UserParameter::hydrate([
                    'id' => 33, 'name' => "Racine par défaut", 'value' => ""
                ]),
                34 => UserParameter::hydrate([
                    'id' => 34, 'name' => "Demande avis de délivrance", 'value' => "0"
                ]),
                50 => UserParameter::hydrate([
                    'id' => 50, 'name' => "Version format base", 'value' => ""
                ]),
                57 => UserParameter::hydrate([
                    'id' => 57, 'name' => "PS SNCF", 'value' => "0"
                ]),
                58 => UserParameter::hydrate([
                    'id' => 58, 'name' => "PS RATP", 'value' => "0"
                ]),
                66 => UserParameter::hydrate([
                    'id' => 66, 'name' => "PS Partenaire de regime de sante des mines", 'value' => "0"
                ]),
                79 => UserParameter::hydrate([
                    'id' => 79, 'name' => "Date pour le tableau de bord", 'value' => "132014102920151029"
                ]),
                85 => UserParameter::hydrate([
                    'id' => 85, 'name' => "Delai avant chainage", 'value' => "0"
                ]),
                90 => UserParameter::hydrate([
                    'id' => 90, 'name' => "Centre de santé", 'value' => "0"
                ]),
                101 => UserParameter::hydrate([
                    'id' => 101, 'name' => "Demande de non utilisation TLA", 'value' => "0"
                ]),
                104 => UserParameter::hydrate([
                    'id' => 104, 'name' => "Activation CCAM", 'value' => "0"
                ]),
                107 => UserParameter::hydrate([
                    'id' => 107, 'name' => "Caisse de l'executant", 'value' => ""
                ]),
                109 => UserParameter::hydrate([
                    'id' => 109, 'name' => "PMSS", 'value' => "3377"
                ]),
                110 => UserParameter::hydrate([
                    'id' => 110, 'name' => "Restriction STS CMU", 'value' => "0"
                ]),
                115 => UserParameter::hydrate([
                    'id' => 115, 'name' => "Dernier num lot DRE", 'value' => "ZZZ"
                ]),
                116 => UserParameter::hydrate([
                    'id' => 116, 'name' => "Niveau de diagnostic", 'value' => "1"
                ]),
                126 => UserParameter::hydrate([
                    'id' => 126, 'name' => "Type application", 'value' => ""
                ]),
                127 => UserParameter::hydrate([
                    'id' => 127, 'name' => "Type destinataire", 'value' => ""
                ]),
                150 => UserParameter::hydrate([
                    'id' => 150, 'name' => "Visualisation des groupes d'infos SSV-SRT-STS", 'value' => "000"
                ]),
                151 => UserParameter::hydrate([
                    'id' => 151, 'name' => "Activation des cartes virtuelles", 'value' => "0"
                ]),
                153 => UserParameter::hydrate([
                    'id' => 153, 'name' => "Date installation generaliste", 'value' => "21/10/2020"
                ]),
                154 => UserParameter::hydrate([
                    'id' => 154, 'name' => "Date installation zone sous medicalisee", 'value' => "23/10/2020"
                ]),
                155 => UserParameter::hydrate([
                    'id' => 155, 'name' => "Contrat tarifaire", 'value' => "0"
                ]),
                157 => UserParameter::hydrate([
                    'id' => 157, 'name' => "Login POP3", 'value' => ""
                ]),
                158 => UserParameter::hydrate([
                    'id' => 158, 'name' => "Password POP3", 'value' => ""
                ]),
                159 => UserParameter::hydrate([
                    'id' => 159, 'name' => "Adresse serveur SMTP", 'value' => ""
                ]),
                160 => UserParameter::hydrate([
                    'id' => 160, 'name' => "Adresse serveur POP3", 'value' => ""
                ]),
                161 => UserParameter::hydrate([
                    'id' => 161, 'name' => "Adresse électronique", 'value' => ""
                ]),
                162 => UserParameter::hydrate([
                    'id' => 162, 'name' => "Flux vers CNDA", 'value' => "0"
                ]),
                163 => UserParameter::hydrate([
                    'id' => 163,
                    'name' => "Paramétrage des contrats responsables",
                    'value' => "17.5@15@23@2@3@3.27@2.00@2.00@3.27"
                ]),
                164 => UserParameter::hydrate([
                    'id' => 164, 'name' => "Mode CNDA", 'value' => "0"
                ]),
                165 => UserParameter::hydrate([
                    'id' => 165, 'name' => "Activation log télétransmission", 'value' => "1"
                ]),
                176 => UserParameter::hydrate([
                    'id' => 176,
                    'name' => "Transmission OCT@OCT eclateur@NoSiret@FSE enrichie@Adresse dest%OCT ID",
                    'value' => "1"
                ]),
                182 => UserParameter::hydrate([
                    'id' => 182, 'name' => "Type recettes", 'value' => "1"
                ]),
                190 => UserParameter::hydrate([
                    'id' => 190, 'name' => "Activation globale SCOR", 'value' => "0"
                ]),
                192 => UserParameter::hydrate([
                    'id' => 192, 'name' => "Taille d'un lot SCOR", 'value' => "1000"
                ]),
                204 => UserParameter::hydrate([
                    'id' => 204, 'name' => "ADRI", 'value' => "2"
                ]),
                206 => UserParameter::hydrate([
                    'id' => 206, 'name' => "IMTI", 'value' => "1"
                ]),
                213 => UserParameter::hydrate([
                    'id' => 213, 'name' => "Taille Lot SCOR en Ko", 'value' => "150"
                ]),
                241 => UserParameter::hydrate([
                    'id' => 241, 'name' => "Nombre de demandes par jour (LOI)", 'value' => "1"
                ]),
                242 => UserParameter::hydrate([
                    'id' => 242, 'name' => "Nombre de jours max sans incrément avant blocage (LOI)", 'value' => "7"
                ]),
                243 => UserParameter::hydrate([
                    'id' => 243,
                    'name' => "Adresse du serveur d''opposition (LOI)",
                    'value' => "oppv-loi@opposition.sesam-vitale.fr"
                ]),
                244 => UserParameter::hydrate([
                    'id' => 244, 'name' => "Répertoire de stockage des fichiers LOI", 'value' => "LOI"
                ]),
                245 => UserParameter::hydrate([
                    'id' => 245, 'name' => "Désactiver contrôles entre dLoi reçu/envoyé", 'value' => "0"
                ]),
                246 => UserParameter::hydrate([
                    'id' => 246, 'name' => "LOI - Activer contrôle date mise à jour référence LOI", 'value' => "1"
                ]),
                277 => UserParameter::hydrate([
                    'id' => 277, 'name' => "N/R", 'value' => "1"
                ]),
                278 => UserParameter::hydrate([
                    'id' => 278, 'name' => "N/R", 'value' => "0"
                ]),
                279 => UserParameter::hydrate([
                    'id' => 279, 'name' => "N/R", 'value' => "0"
                ]),
                280 => UserParameter::hydrate([
                    'id' => 280, 'name' => "N/R", 'value' => "0"
                ]),
            ]
        ]);

        return [['response' => $response, 'config' => $config]];
    }

    public function updateUserParameterProvider(): array
    {
        $response = <<<JSON
{
    "method": {
        "name": "IDE-updateParamsPs",
        "service": true,
        "parameters": {
            "updateParamsPs": {
                "code": 155,
                "valeur": "1"
            }
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    }
}
JSON;

        return [['response' => $response]];
    }

    public function deleteUserParameterProvider(): array
    {
        $response = <<<JSON
{
    "method": {
        "name": "IDE-deleteParamsPs",
        "service": true,
        "parameters": {
            "deleteParamsPs": {
                "code": 280
            }
        },
        "output": {}
    }
}
JSON;

        return [['response' => $response]];
    }

    public function getListTariffContractsProvider(): array
    {
        $response = <<<JSON
{
    "method": {
        "name": "IDE-getListeContratsTarifaires",
        "service": true,
        "parameters": {},
        "output": {
            "lst": [
                {
                    "code": "0",
                    "libelle": "Pas de contrat"
                },
                {
                    "code": "1",
                    "libelle": "OPTAM"
                },
                {
                    "code": "2",
                    "libelle": "OPTAM-CO"
                }
            ]
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    }
}
JSON;
        $expected = [
            TariffContract::hydrate(['code' => 0, 'label' => 'Pas de contrat']),
            TariffContract::hydrate(['code' => 1, 'label' => 'OPTAM']),
            TariffContract::hydrate(['code' => 2, 'label' => 'OPTAM-CO'])
        ];

        return [['response' => $response, 'expected' => $expected]];
    }

    public function getListTariffContractsFromCacheProvider(): array
    {
        $tariffs = [
            ['code' => 0, 'label' => 'Pas de contrat'],
            ['code' => 1, 'label' => 'OPTAM'],
            ['code' => 2, 'label' => 'OPTAM-CO']
        ];

        $expected = [
            TariffContract::hydrate(['code' => 0, 'label' => 'Pas de contrat']),
            TariffContract::hydrate(['code' => 1, 'label' => 'OPTAM']),
            TariffContract::hydrate(['code' => 2, 'label' => 'OPTAM-CO'])
        ];

        return [['tariffs' => $tariffs, 'expected' => $expected]];
    }

    public function getUserSignatureProvider(): array
    {
        $response = <<<JSON
{
    "method": {
        "name": "IDE-getSignature",
        "service": true,
        "parameters": {
            "getSignature": {
                "idJfse": 1
            }
        },
        "output": {
            "signature": "ZHVtbXlfc2lnbmF0dXJlX2ZpbGVfY29udGVudF9mb3JfdGVzdGluZw=="
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    }
}
JSON;

        return [['response' => $response, 'signature' => 'dummy_signature_file_content_for_testing']];
    }

    public function getUserSignatureFailureProvider(): array
    {
        $response = <<<JSON
{
    "method": {
        "name": "IDE-getSignature",
        "service": true,
        "parameters": {
            "getSignature": {
                "idJfse": 1
            }
        },
        "output": {
            "signature": ""
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    }
}
JSON;

        return [['response' => $response]];
    }

    public function updateUserSignatureProvider(): array
    {
        $response = <<<JSON
{
    "method": {
        "name": "IDE-updateSignature",
        "service": true,
        "parameters": {
            "updateSignature": {
                "idJfse": 1,
                "signature": "ZHVtbXlfc2lnbmF0dXJlX2ZpbGVfY29udGVudF9mb3JfdGVzdGluZw=="
            }
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    }
}
JSON;
        return [['response' => $response]];
    }

    public function deleteUserSignatureProvider(): array
    {
        $response = <<<JSON
{
    "method": {
        "name": "IDE-deleteSignature",
        "service": true,
        "parameters": {
            "deleteSignature": {
                "idJfse": 1
            }
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    }
}
JSON;

        return [['response' => $response]];
    }
}
