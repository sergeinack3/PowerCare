<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\ViewModels;

use Ox\Mediboard\Jfse\Domain\Cps\Card;
use Ox\Mediboard\Jfse\Domain\Cps\Situation;
use Ox\Mediboard\Jfse\ViewModels\CCpsCard;
use Ox\Mediboard\Jfse\ViewModels\CCpsSituation;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CpsCardTest
 *
 * @package Ox\Mediboard\Jfse\Tests\Unit\ViewModels
 */
class CpsCardTest extends OxUnitTestCase
{
    /**
     * Test the creation of a CCpsCard view model from a domain entity
     */
    public function testGetFromEntity(): void
    {
        $situation                            = new CCpsSituation();
        $situation->practitioner_id           = 1;
        $situation->situation_id              = 1;
        $situation->structure_identifier_type = 4;
        $situation->structure_identifier      = '999000652790050';
        $situation->structure_name            = 'CABINET GRIS';
        $situation->invoicing_number          = '99112976';
        $situation->invoicing_number_key      = '8';
        $situation->substitute_number         = '';
        $situation->convention_code           = 3;
        $situation->convention_label          = 'PS conventionné avec honoraires libres';
        $situation->speciality_code           = 7;
        $situation->speciality_label          = 'Gynécologie obstétrique';
        $situation->speciality_group          = 'PR';
        $situation->price_zone_code           = 30;
        $situation->price_zone_label          = 'Autres dép. et localités avec IFA et IK';
        $situation->distance_allowance_code   = 1;
        $situation->distance_allowance_label  = 'Indemnités kilométriques plaine';
        $situation->approval_codes            = [1 => 0, 2 => 0, 3 => 0];
        $situation->approval_labels           = [
            1 => 'Pas d\'agrément radio',
            2 => 'Pas d\'agrément radio',
            3 => 'Pas d\'agrément radio',
        ];
        $situation->fse_signing_authorisation = '1';
        $situation->lot_signing_authorisation = '1';
        $situation->practice_mode             = 0;
        $situation->practice_status           = 1;
        $situation->activity_sector           = 31;

        $expected                                     = new CCpsCard();
        $expected->type_code                          = 0;
        $expected->type_label                         = 'Carte de Professionnel de Santé';
        $expected->national_identification_type_code  = 8;
        $expected->national_identification_type_label = 'No RPPS';
        $expected->national_identification_number     = '9990006527';
        $expected->national_identification_key        = '9';
        $expected->civility_code                      = 22;
        $expected->civility_label                     = 'Madame';
        $expected->last_name                          = 'GRIS';
        $expected->first_name                         = 'ANNE';
        $expected->situations                         = [$situation];

        $actual = CCpsCard::getFromEntity(
            Card::hydrate(
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
                ]
            )
        );

        $this->assertEquals($expected, $actual);
    }
}
