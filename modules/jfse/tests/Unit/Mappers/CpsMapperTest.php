<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Mappers;

use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Cps\Card;
use Ox\Mediboard\Jfse\Domain\Cps\Situation;
use Ox\Mediboard\Jfse\Mappers\CpsMapper;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CardMapperTest
 *
 * @package Ox\Mediboard\Jfse\Tests\Unit\Mappers
 */
class CpsMapperTest extends OxUnitTestCase
{
    /**
     * Test the Mapping of a Card object from a LPS-lire API method response
     */
    public function testGetFromReadRequest(): void
    {
        $expected = Card::hydrate(
            [
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
                    1 => Situation::hydrate([
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
            ]
        );

        $json_response = <<<JSON
{
    "integrator": {
        "name": "RESIP",
        "key": "TEST"
    },
    "method": {
        "name": "LPS-lire",
        "service": true,
        "parameters": {
            "codePorteur": "1234"
        },
        "output": {
            "typeCarte": 0,
            "libelleTypeCarte": "Carte de Professionnel de Santé",
            "typeIdentificationNational": 8,
            "libelleTypeIdentificationNational": "No RPPS",
            "numIdentificationNational": "9990006527",
            "cleIdentificationNational": "9",
            "codeCivilite": "22",
            "libelleCodeCivilite": "Madame",
            "nom": "GRIS",
            "prenom": "ANNE",
            "lstCpsSituation": [
                {
                    "idJfse": 1,
                    "identifiantLogique": 1,
                    "typeIdentificationStructure": 4,
                    "numIdentificationStructure": "999000652790050",
                    "raisonSociale": "CABINET GRIS",
                    "numIdentificationPSFacturation": "99112976",
                    "cleIdentificationPSFacturation": "8",
                    "numIdentificationPSRemplacant": "",
                    "codeConventionnel": "3",
                    "libelleCodeConventionnel": "PS conventionné avec honoraires libres",
                    "codeSpecialite": "07",
                    "libelleCodeSpecialite": "Gynécologie obstétrique",
                    "familleSpecialite": "PR",
                    "codeZoneTarif": "30",
                    "libelleCodeZoneTarif": "Autres dép. et localités avec IFA et IK",
                    "codeZoneIK": "01",
                    "libelleCodeZoneIK": "Indemnités kilométriques plaine",
                    "codeAgrement1": "0",
                    "libelleCodeAgrement1": "Pas d'agrément radio",
                    "codeAgrement2": "0",
                    "libelleCodeAgrement2": "Pas d'agrément radio",
                    "codeAgrement3": "0",
                    "libelleCodeAgrement3": "Pas d'agrément radio",
                    "habilitationSignerFSE": 1,
                    "habilitationSignerLOT": 1,
                    "modeExercice": 0,
                    "statusExercice": 1,
                    "secteurActivite": 31,
                    "lstTitulaires": []
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

        $response = Response::forge('LPS-lire', json_decode(utf8_encode($json_response), true));
        $this->assertEquals($expected, CpsMapper::getCardFromReadResponse($response));
    }
}
