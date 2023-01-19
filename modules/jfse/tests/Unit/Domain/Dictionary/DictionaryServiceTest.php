<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain\Dictionary;

use Ox\Components\Cache\LayeredCache;
use Ox\Mediboard\Jfse\ApiClients\DictionaryClient;
use Ox\Mediboard\Jfse\Domain\Dictionary\DictionaryService;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use Ox\Mediboard\Patients\CPatient;

class DictionaryServiceTest extends UnitTestJfse
{
    public function testListRegimes(): void
    {
        $response = <<<JSON
{
    "integrator":{
        "name":"OPENXTREM",
        "key":"test",
        "os":"WINDOWS",
        "etablissement":"TEST"
    },
    "method":{
        "name":"DIC-getListeRegimes",
        "service":true,
        "parameters":[
            
        ],
        "output":{
            "lst":[
                {
                    "code":"01",
                    "libelle":"Regime general",
                    "typeDest":"CT"
                },
                {
                    "code":"02",
                    "libelle":"Regime agricole",
                    "typeDest":"MA"
                },
                {
                    "code":"03",
                    "libelle":"Regime Social des Independants - RSI",
                    "typeDest":"SR"
                },
                {
                    "code":"04",
                    "libelle":"Caisse de Prevoyance et de Retraite du personnel de la SNCF - CPRPSNCF",
                    "typeDest":"CF"
                },
                {
                    "code":"05",
                    "libelle":"Regie Autonome des Transports Parisiens - RATP",
                    "typeDest":"RP"
                }
            ]
        },
        "lstException":[
            
        ],
        "cancel":false,
        "asynchronous":false
    },
    "cardReader":{
        "id":"test",
        "protocol":"PCSC",
        "channel":"0",
        "reader":"0",
        "cps":"",
        "vitale":""
    },
    "returnMode":{
        "mode":1,
        "URL":""
    },
    "idJfse":0
}
JSON;
        $expected = [
            ['code' => '01', 'label' => 'Regime general'],
            ['code' => '02', 'label' => 'Regime agricole'],
            ['code' => '03', 'label' => 'Regime Social des Independants - RSI'],
            ['code' => '04', 'label' => 'Caisse de Prevoyance et de Retraite du personnel de la SNCF - CPRPSNCF'],
            ['code' => '05', 'label' => 'Regie Autonome des Transports Parisiens - RATP'],
        ];

        $this->assertEquals($expected, $this->getService($response)->listRegimes());
    }

    /**
     * @dataProvider listOrganismsProvider
     */
    public function testFilterOrganisms(string $response): void
    {
        $expected = [
            [
                'regime_code' => '01',
                'fund_code'   => '851',
                'center_code' => '0000',
                'label'       => 'LA ROCHE SUR YON',
                'regime_label' => 'Regime general',
            ],
            [
                'regime_code' => '01',
                'fund_code'   => '861',
                'center_code' => '0000',
                'label'       => 'POITIERS',
                'regime_label' => 'Regime general',
            ],
            [
                'regime_code' => '01',
                'fund_code'   => '161',
                'center_code' => '0000',
                'label'       => 'ANGOULEME',
                'regime_label' => 'Regime general',
            ],
            [
                'regime_code' => '01',
                'fund_code'   => '171',
                'center_code' => '0000',
                'label'       => 'LA ROCHELLE',
                'regime_label' => 'Regime general',
            ],
            [
                'regime_code' => '01',
                'fund_code'   => '181',
                'center_code' => '0000',
                'label'       => 'BOURGES',
                'regime_label' => 'Regime general',
            ],
            [
                'regime_code' => '01',
                'fund_code'   => '191',
                'center_code' => '0000',
                'label'       => 'TULLE',
                'regime_label' => 'Regime general',
            ],
            [
                'regime_code' => '01',
                'fund_code'   => '349',
                'center_code' => '9881',
                'label'       => 'Caisse de test',
                'regime_label' => 'Regime general',
            ],
        ];

        $this->assertEquals($expected, $this->getService($response)->filterOrganisms('01'));
    }

    /**
     * @dataProvider listOrganismsProvider
     */
    public function testFilterOrganismsWithNeedle(string $response): void
    {
        $expected = [
            0 => [
                'regime_code' => '01',
                'fund_code'   => '851',
                'center_code' => '0000',
                'label'       => 'LA ROCHE SUR YON',
                'regime_label' => 'Regime general',
            ],
            3 => [
                'regime_code' => '01',
                'fund_code'   => '171',
                'center_code' => '0000',
                'label'       => 'LA ROCHELLE',
                'regime_label' => 'Regime general',
            ],
        ];

        $this->assertEquals($expected, $this->getService($response)->filterOrganisms('01', 'roc'));
    }

    /**
     * @dataProvider listOrganismsProvider
     */
    public function testGetOrganismForPatientWithoutFundCode(string $response): void
    {
        $patient = new CPatient();
        $patient->code_regime = '01';

        $this->assertNull($this->getService($response)->getOrganismForPatient($patient));
    }

    /**
     * @dataProvider listOrganismsProvider
     */
    public function testGetOrganismForPatientWithoutCenterCode(string $response): void
    {
        $expected = [
            'regime_code' => '01',
            'fund_code'   => '349',
            'center_code' => '9881',
            'label'       => 'Caisse de test',
            'regime_label' => 'Regime general',
        ];

        $patient = new CPatient();
        $patient->code_regime = '01';
        $patient->caisse_gest = '349';

        $this->assertEquals($expected, $this->getService($response)->getOrganismForPatient($patient));
    }

    /**
     * @dataProvider listOrganismsProvider
     */
    public function testGetOrganismForPatientWithoutCenterCodeKO(string $response): void
    {
        $patient = new CPatient();
        $patient->code_regime = '02';
        $patient->caisse_gest = '349';

        $this->assertNull($this->getService($response)->getOrganismForPatient($patient));
    }

    /**
     * @dataProvider listOrganismsProvider
     */
    public function testGetOrganismForPatientWithCenterCode(string $response): void
    {
        $expected = [
            'regime_code' => '01',
            'fund_code'   => '349',
            'center_code' => '9881',
            'label'       => 'Caisse de test',
            'regime_label' => 'Regime general',
        ];

        $patient = new CPatient();
        $patient->code_regime = '01';
        $patient->caisse_gest = '349';
        $patient->centre_gest = '9981';

        $this->assertEquals($expected, $this->getService([$response, $response])->getOrganismForPatient($patient));
    }

    public function testListManagingCodes(): void
    {
        $response = <<<JSON
{
    "integrator":{
        "name":"OPENXTREM",
        "key":"eb31574a39b1ad8d4aa3a8d38df0677c",
        "os":"WINDOWS",
        "etablissement":"TEST"
    },
    "method":{
        "name":"DIC-getListeCodeGestions",
        "service":true,
        "parameters":[
            
        ],
        "output":{
            "lst":[
                {
                    "id":74,
                    "code":"23",
                    "libelle":"Fonctionnaires ouvriers de l'Etat"
                },
                {
                    "id":84,
                    "code":"89",
                    "libelle":"Assures beneficiaires de la CMU"
                },
                {
                    "id":75,
                    "code":"24",
                    "libelle":"EDF GDF"
                },
                {
                    "id":76,
                    "code":"25",
                    "libelle":"Etudiants"
                },
                {
                    "id":67,
                    "code":"04",
                    "libelle":"CPR SNCF"
                }
            ]
        },
        "lstException":[],
        "cancel":false,
        "asynchronous":false
    },
    "cardReader":{
        "id":"test",
        "protocol":"PCSC",
        "channel":"0",
        "reader":"0",
        "cps":"",
        "vitale":""
    },
    "returnMode":{
        "mode":1,
        "URL":""
    },
    "idJfse":0
}
JSON;
        $expected = [
            ['code' => '23', 'label' => "Fonctionnaires ouvriers de l'Etat", 'id' => '74'],
            ['code' => '89', 'label' => 'Assures beneficiaires de la CMU', 'id' => '84'],
            ['code' => '24', 'label' => 'EDF GDF', 'id' => '75'],
            ['code' => '25', 'label' => 'Etudiants', 'id' => '76'],
            ['code' => '04', 'label' => 'CPR SNCF', 'id' => '67'],
        ];

        $this->assertEquals($expected, $this->getService($response)->listManagingCodes());
    }

    /**
     * @param string|array $response
     *
     * @return DictionaryService
     */
    private function getService($response): DictionaryService
    {
        $cache_mock = $this->createMock(LayeredCache::class);
        $cache_mock->method('get')->willReturn(null);

        if (is_string($response)) {
            $responses = [$response];
        } elseif (is_array($response)) {
            $responses = $response;
        }

        $guzzle_responses = [];
        foreach ($responses as $response) {
            $guzzle_responses[] = $this->makeJsonGuzzleResponse(200, $response);
        }

        $client = new DictionaryClient($this->makeClientFromGuzzleResponses($guzzle_responses));

        return new DictionaryService($client, $cache_mock);
    }

    public function listOrganismsProvider(): array
    {
        return [[<<<JSON
{
    "integrator":{
        "name":"OPENXTREM",
        "key":"eb31574a39b1ad8d4aa3a8d38df0677c",
        "os":"WINDOWS",
        "etablissement":"TEST"
    },
    "method":{
        "name":"DIC-getListeOrganismes",
        "service":true,
        "parameters":{
            "getListeOrganismes":[
                
            ]
        },
        "output":{
            "lstOrganismes":[
                {
                    "codeRegime":"01",
                    "codeCaisse":"851",
                    "codeCentre":"0000",
                    "nomCaisse":"LA ROCHE SUR YON",
                    "codeDestinataire":"851",
                    "codeCentreInfo":"023",
                    "nomRegime":"Regime general",
                    "adresse1":"",
                    "adresse2":"",
                    "codePostal":"",
                    "commune":"",
                    "cedex":"",
                    "telephone":"",
                    "fax":""
                },
                {
                    "codeRegime":"01",
                    "codeCaisse":"861",
                    "codeCentre":"0000",
                    "nomCaisse":"POITIERS",
                    "codeDestinataire":"861",
                    "codeCentreInfo":"008",
                    "nomRegime":"Regime general",
                    "adresse1":"",
                    "adresse2":"",
                    "codePostal":"",
                    "commune":"",
                    "cedex":"",
                    "telephone":"",
                    "fax":""
                },
                {
                    "codeRegime":"01",
                    "codeCaisse":"161",
                    "codeCentre":"0000",
                    "nomCaisse":"ANGOULEME",
                    "codeDestinataire":"161",
                    "codeCentreInfo":"008",
                    "nomRegime":"Regime general",
                    "adresse1":"",
                    "adresse2":"",
                    "codePostal":"",
                    "commune":"",
                    "cedex":"",
                    "telephone":"",
                    "fax":""
                },
                {
                    "codeRegime":"01",
                    "codeCaisse":"171",
                    "codeCentre":"0000",
                    "nomCaisse":"LA ROCHELLE",
                    "codeDestinataire":"171",
                    "codeCentreInfo":"008",
                    "nomRegime":"Regime general",
                    "adresse1":"",
                    "adresse2":"",
                    "codePostal":"",
                    "commune":"",
                    "cedex":"",
                    "telephone":"",
                    "fax":""
                },
                {
                    "codeRegime":"01",
                    "codeCaisse":"181",
                    "codeCentre":"0000",
                    "nomCaisse":"BOURGES",
                    "codeDestinataire":"181",
                    "codeCentreInfo":"008",
                    "nomRegime":"Regime general",
                    "adresse1":"",
                    "adresse2":"",
                    "codePostal":"",
                    "commune":"",
                    "cedex":"",
                    "telephone":"",
                    "fax":""
                },
                {
                    "codeRegime":"01",
                    "codeCaisse":"191",
                    "codeCentre":"0000",
                    "nomCaisse":"TULLE",
                    "codeDestinataire":"191",
                    "codeCentreInfo":"008",
                    "nomRegime":"Regime general",
                    "adresse1":"",
                    "adresse2":"",
                    "codePostal":"",
                    "commune":"",
                    "cedex":"",
                    "telephone":"",
                    "fax":""
                },
                {
                    "codeRegime":"01",
                    "codeCaisse":"349",
                    "codeCentre":"9881",
                    "nomCaisse":"Caisse de test",
                    "codeDestinataire":"349",
                    "codeCentreInfo":"000",
                    "nomRegime":"Regime general",
                    "adresse1":"",
                    "adresse2":"",
                    "codePostal":"",
                    "commune":"",
                    "cedex":"",
                    "telephone":"",
                    "fax":""
                },
                {
                    "codeRegime":"02",
                    "codeCaisse":"141",
                    "codeCentre":"0000",
                    "nomCaisse":"CAEN",
                    "codeDestinataire":"141",
                    "codeCentreInfo":"351",
                    "nomRegime":"Regime agricole",
                    "adresse1":"",
                    "adresse2":"",
                    "codePostal":"",
                    "commune":"",
                    "cedex":"",
                    "telephone":"",
                    "fax":""
                },
                {
                    "codeRegime":"02",
                    "codeCaisse":"349",
                    "codeCentre":"0000",
                    "nomCaisse":"Caisse de test",
                    "codeDestinataire":"349",
                    "codeCentreInfo":"000",
                    "nomRegime":"Regime agricole",
                    "adresse1":"",
                    "adresse2":"",
                    "codePostal":"",
                    "commune":"",
                    "cedex":"",
                    "telephone":"",
                    "fax":""
                },
                {
                    "codeRegime":"02",
                    "codeCaisse":"349",
                    "codeCentre":"0000",
                    "nomCaisse":"Caisse de test 2",
                    "codeDestinataire":"349",
                    "codeCentreInfo":"000",
                    "nomRegime":"Regime agricole",
                    "adresse1":"",
                    "adresse2":"",
                    "codePostal":"",
                    "commune":"",
                    "cedex":"",
                    "telephone":"",
                    "fax":""
                },
                {
                    "codeRegime":"02",
                    "codeCaisse":"775",
                    "codeCentre":"0000",
                    "nomCaisse":"CPS ST BARTH",
                    "codeDestinataire":"771",
                    "codeCentreInfo":"591",
                    "nomRegime":"Regime agricole",
                    "adresse1":"",
                    "adresse2":"",
                    "codePostal":"",
                    "commune":"",
                    "cedex":"",
                    "telephone":"",
                    "fax":""
                }
            ]
        },
        "lstException":[],
        "cancel":false,
        "asynchronous":false
    },
    "cardReader":{
        "id":"test",
        "protocol":"PCSC",
        "channel":"0",
        "reader":"0",
        "cps":"",
        "vitale":""
    },
    "returnMode":{
        "mode":1,
        "URL":""
    },
    "idJfse":10
}
JSON]];
    }
}
