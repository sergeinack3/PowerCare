<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests;

use Exception;
use Ox\Mediboard\Patients\CIdentityProofType;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CSourceIdentite;
use Ox\Mediboard\Patients\PatientStatus;
use Ox\Mediboard\Patients\Tests\Fixtures\SimplePatientFixtures;
use Ox\Tests\OxUnitTestCase;

class PatientStatusTest extends OxUnitTestCase
{
    /**
     * @dataProvider updateStatusProvider
     */
    public function testGetStatus(array $sources_identite, CSourceIdentite $source_identite, ?string $expected): void
    {
        $patient_status = new PatientStatus(new CPatient());
        $patient_status->setSourceIdentite($source_identite);
        $patient_status->setSourcesIdentite($sources_identite);

        $this->assertEquals($expected, $this->invokePrivateMethod($patient_status, 'getStatus'));
    }

    public function updateStatusProvider(): array
    {
        $source_identite_manuelle                 = new CSourceIdentite();
        $source_identite_manuelle->_id            = 1;
        $source_identite_manuelle->active         = 1;
        $source_identite_manuelle->mode_obtention = CSourceIdentite::MODE_OBTENTION_MANUEL;

        $identity_proof_type                            = new CIdentityProofType();
        $identity_proof_type->_id                       = 1;
        $identity_proof_type->trust_level               = CIdentityProofType::TRUST_LEVEL_HIGH;

        $source_identite_justif                         = new CSourceIdentite();
        $source_identite_justif->_id                    = 2;
        $source_identite_justif->active                 = 1;
        $source_identite_justif->mode_obtention         = CSourceIdentite::MODE_OBTENTION_MANUEL;
        $source_identite_justif->identity_proof_type_id = 1;
        $source_identite_justif->_fwd['identity_proof_type_id'] = $identity_proof_type;

        $source_identite_insi                 = new CSourceIdentite();
        $source_identite_insi->_id            = 3;
        $source_identite_insi->active         = 1;
        $source_identite_insi->mode_obtention = CSourceIdentite::MODE_OBTENTION_INSI;

        return [
            'prov_only' => [
                [
                    $source_identite_manuelle,
                ],
                $source_identite_manuelle,
                'PROV',
            ],

            'prov_with_justif' => [
                [
                    $source_identite_manuelle,
                    $source_identite_justif,
                ],
                $source_identite_manuelle,
                'PROV',
            ],

            'prov_with_insi' => [
                [
                    $source_identite_manuelle,
                    $source_identite_insi,
                ],
                $source_identite_manuelle,
                'PROV',
            ],

            'prov_with_justif_and_insi' => [
                [
                    $source_identite_manuelle,
                    $source_identite_justif,
                    $source_identite_insi,
                ],
                $source_identite_manuelle,
                'PROV',
            ],

            'vali_alone' => [
                [
                    $source_identite_manuelle,
                    $source_identite_justif,
                ],
                $source_identite_justif,
                'VALI',
            ],

            'vali_with_insi' => [
                [
                    $source_identite_manuelle,
                    $source_identite_justif,
                    $source_identite_insi,
                ],
                $source_identite_justif,
                'VALI',
            ],

            'recup' => [
                [
                    $source_identite_manuelle,
                    $source_identite_insi,
                ],
                $source_identite_insi,
                'RECUP',
            ],

            'qual' => [
                [
                    $source_identite_manuelle,
                    $source_identite_insi,
                    $source_identite_justif,
                ],
                $source_identite_insi,
                'QUAL',
            ],
        ];
    }

    /**
     * @throws Exception
     * @dataProvider demotePatientStatusProvider
     */
    public function testDemotePatientStatus(array $sources, string $expected): void
    {
        /** @var CPatient $patient */
        $patient = $this->getObjectFromFixturesReference(CPatient::class, SimplePatientFixtures::SAMPLE_PATIENT);
        $patient_status = new PatientStatus($patient);
        $patient_status->setSourceIdentite($sources[0]);
        $patient_status->setSourcesIdentite($sources);

        $actual = $patient_status->demoteIdentitySource();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array[]
     */
    public function demotePatientStatusProvider(): array
    {
        $source_identite_manuelle                 = new CSourceIdentite();
        $source_identite_manuelle->_id            = 1;
        $source_identite_manuelle->active         = 1;
        $source_identite_manuelle->mode_obtention = CSourceIdentite::MODE_OBTENTION_MANUEL;

        $identity_proof_type              = new CIdentityProofType();
        $identity_proof_type->_id         = 1;
        $identity_proof_type->trust_level = CIdentityProofType::TRUST_LEVEL_HIGH;


        $source_identite_justif                                 = new CSourceIdentite();
        $source_identite_justif->_id                            = 2;
        $source_identite_justif->active                         = 1;
        $source_identite_justif->mode_obtention                 = CSourceIdentite::MODE_OBTENTION_MANUEL;
        $source_identite_justif->identity_proof_type_id         = 1;
        $source_identite_justif->_fwd['identity_proof_type_id'] = $identity_proof_type;

        $source_identite_insi                 = new CSourceIdentite();
        $source_identite_insi->_id            = 3;
        $source_identite_insi->active         = 1;
        $source_identite_insi->mode_obtention = CSourceIdentite::MODE_OBTENTION_INSI;

        $qual = [
            $source_identite_insi,
            $source_identite_justif,
            $source_identite_manuelle,
        ];

        $recup = [
            $source_identite_insi,
            $source_identite_manuelle,
        ];

        return [
            "QUAL TO VALI"  => [$qual, "VALI"],
            "RECUP TO PROV" => [$recup, "PROV"],
        ];
    }
}
