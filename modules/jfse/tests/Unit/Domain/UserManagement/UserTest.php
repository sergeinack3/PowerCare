<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain\UserManagement;

use DateTime;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\Cps\Situation;
use Ox\Mediboard\Jfse\Domain\UserManagement\User;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserParameter;
use Ox\Mediboard\Jfse\Mappers\UserMapper;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use Ox\Mediboard\Mediusers\CMediusers;

class UserTest extends UnitTestJfse
{
    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::hydrate([
            'id'                                       => 1,
            'establishment_id'                         => 0,
            'login'                                    => '9911297681grisanne',
            'password'                                 => 'Zvhoz8Nerj4bv?',
            'national_identification_type_code'        => 8,
            'national_identification_number'           => '99900065279',
            'civility_code'                            => 22,
            'civility_label'                           => 'Docteur',
            'last_name'                                => 'GRIS',
            'first_name'                               => 'ANNE',
            'address'                                  => 'Zone des 4 Chevaliers, 17180 Périgny',
            'installation_date'                        => DateTime::createFromFormat('Y-m-d', '2007-05-24'),
            'installation_zone_under_medicalized_date' => DateTime::createFromFormat('Y-m-d', '2015-11-03'),
            'ccam_activation'                          => false,
            'health_insurance_agency'                  => '013499881',
            'health_center'                            => false,
            'cnda_mode'                                => false,
            'cardless_mode'                            => false,
            'care_path'                                => 0,
            'card_type'                                => 0,
            'last_fse_number'                          => 122,
            'formatting'                               => false,
            'substitute_number'                        => '99900030943',
            'substitute_last_name'                     => 'REMPLA',
            'substitute_first_name'                    => 'SANDRINE',
            'substitute_situation_number'              => 1,
            'substitute_rpps_number'                   => '99900030943',
            'substitution_session'                     => 1,
            'mediuser_id'                              => 1,
            'parameters'                               => [
                UserParameter::hydrate(['id' => 127, 'name' => 'Contrat tarifaire', 'value' => 1]),
                UserParameter::hydrate(['id' => 52,  'name' => 'TP AMO', 'value' => true])
            ],
            'situation'                                => Situation::hydrate([
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
                'activity_sector'           => 31
            ]),
            'data_model'                    => new CJfseUser()
        ]);
    }

    public function testGetId(): void
    {
        $this->assertEquals(1, $this->user->getId());
    }

    public function testGetEstablishmentId(): void
    {
        $this->assertEquals(0, $this->user->getEstablishmentId());
    }

    public function testGetLogin(): void
    {
        $this->assertEquals('9911297681grisanne', $this->user->getLogin());
    }

    public function testGetPassword(): void
    {
        $this->assertEquals('Zvhoz8Nerj4bv?', $this->user->getPassword());
    }

    public function testGetNationalIdentificationTypeCode(): void
    {
        $this->assertEquals(8, $this->user->getNationalIdentificationTypeCode());
    }

    public function testGetNationalIdentificationNumber(): void
    {
        $this->assertEquals('99900065279', $this->user->getNationalIdentificationNumber());
    }

    public function testGetCivilityCode(): void
    {
        $this->assertEquals(22, $this->user->getCivilityCode());
    }

    public function testGetCivilityLabel(): void
    {
        $this->assertEquals('Docteur', $this->user->getCivilityLabel());
    }

    public function testGetLastName(): void
    {
        $this->assertEquals('GRIS', $this->user->getLastName());
    }

    public function testGetFirstName(): void
    {
        $this->assertEquals('ANNE', $this->user->getFirstName());
    }

    public function testGettAddress(): void
    {
        $this->assertEquals('Zone des 4 Chevaliers, 17180 Périgny', $this->user->getAddress());
    }

    public function testGetInstallationDate(): void
    {
        $this->assertEquals(
            DateTime::createFromFormat('Y-m-d', '2007-05-24')->format('Y-m-d'),
            $this->user->getInstallationDate()->format('Y-m-d')
        );
    }

    public function testGetInstallationZoneUnderMedicalizedDate(): void
    {
        $this->assertEquals(
            DateTime::createFromFormat('Y-m-d', '2015-11-03')->format('Y-m-d'),
            $this->user->getInstallationZoneUnderMedicalizedDate()->format('Y-m-d')
        );
    }

    public function testIsCcamActivation(): void
    {
        $this->assertEquals(false, $this->user->isCcamActivation());
    }

    public function testGetHealthInsuranceAgency(): void
    {
        $this->assertEquals('013499881', $this->user->getHealthInsuranceAgency());
    }

    public function testIsHealthCenter(): void
    {
        $this->assertEquals(false, $this->user->isHealthCenter());
    }

    public function testIsCndaMode(): void
    {
        $this->assertEquals(false, $this->user->isCndaMode());
    }

    public function testIsCardlessMode(): void
    {
        $this->assertEquals(false, $this->user->isCardlessMode());
    }

    public function testGetCarePath(): void
    {
        $this->assertEquals(0, $this->user->getCarePath());
    }

    public function testGetCardType(): void
    {
        $this->assertEquals(0, $this->user->getCardType());
    }

    public function testGetLastFseNumber(): void
    {
        $this->assertEquals(122, $this->user->getLastFseNumber());
    }

    public function testIsFormatting(): void
    {
        $this->assertEquals(false, $this->user->isFormatting());
    }

    public function testGetSubstituteNumber(): void
    {
        $this->assertEquals('99900030943', $this->user->getSubstituteNumber());
    }

    public function testGetSubstituteLastName(): void
    {
        $this->assertEquals('REMPLA', $this->user->getSubstituteLastName());
    }

    public function testGetSubstituteFirstName(): void
    {
        $this->assertEquals('SANDRINE', $this->user->getSubstituteFirstName());
    }

    public function testGetSubstituteSituationNumber(): void
    {
        $this->assertEquals(1, $this->user->getSubstituteSituationNumber());
    }

    public function testGetSubstituteRppsNumber(): void
    {
        $this->assertEquals('99900030943', $this->user->getSubstituteRppsNumber());
    }

    public function testGetSubstitutionSession(): void
    {
        $this->assertEquals(1, $this->user->getSubstitutionSession());
    }

    public function testGetMediuserId(): void
    {
        $this->assertEquals(1, $this->user->getMediuserId());
    }

    public function testGetParameters(): void
    {
        $this->assertEquals([
            UserParameter::hydrate(['id' => 127, 'name' => 'Contrat tarifaire', 'value' => 1]),
            UserParameter::hydrate(['id' => 52,  'name' => 'TP AMO', 'value' => true])
        ], $this->user->getParameters());
    }

    public function testGetSituation(): void
    {
        $this->assertEquals(Situation::hydrate([
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
            'activity_sector'           => 31
        ]), $this->user->getSituation());
    }

    public function testGetDataModel(): void
    {
        $this->assertEquals(new CJfseUser(), $this->user->getDataModel());
    }

    public function testCreateDataModelSuccess(): void
    {
        $this->assertTrue($this->user->createDataModel());
    }

    public function testCreateDataModelFailure(): void
    {
        $this->expectExceptionMessage('PersistenceError');
        (new User())->createDataModel();
    }
}
