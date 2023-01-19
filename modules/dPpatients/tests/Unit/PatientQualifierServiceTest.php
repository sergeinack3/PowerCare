<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests;

use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientState;
use Ox\Mediboard\Patients\Services\PatientQualifierService;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class PatientQualifierServiceTest extends OxUnitTestCase
{
    /**
     * @param CPatient $patient
     * @param array    $traits_insi
     * @param bool     $qualify_status
     *
     * @return void
     * @dataProvider qualifyIdentiteProvider
     *
     */
    public function testQualifyIdentite(CPatient $patient, array $traits_insi, bool $qualify_status): void
    {
        $this->assertEquals($qualify_status, (new PatientQualifierService($patient, $traits_insi))->canQualify());
    }

    /**
     * @return array
     * @throws TestsException
     */
    public function qualifyIdentiteProvider(): array
    {
        $patient                          = new CPatient();
        $patient->nom_jeune_fille         = 'INS-FAMILLE-UN';
        $patient->prenom                  = 'JEAN-MICHEL';
        $patient->sexe                    = 'm';
        $patient->naissance               = '1973-08-07';
        $patient->lieu_naissance          = 'Omblèze';
        $patient->cp_naissance            = 26400;
        $patient->commune_naissance_insee = 26221;
        $patient->status                  = CPatientState::STATE_VALI;

        $traits_insi_identiques = [
            PatientQualifierService::TRAIT_STRICT_NOM_SOURCE            => $patient->nom_jeune_fille,
            PatientQualifierService::TRAIT_STRICT_PRENOM_SOURCE         => $patient->prenom,
            PatientQualifierService::TRAIT_STRICT_SEXE_SOURCE           => $patient->sexe,
            PatientQualifierService::TRAIT_STRICT_NAISSANCE_SOURCE      => $patient->naissance,
            PatientQualifierService::TRAIT_STRICT_LIEU_NAISSANCE_SOURCE => $patient->commune_naissance_insee,
            PatientQualifierService::TRAIT_STRICT_CP_NAISSANCE_SOURCE   => $patient->cp_naissance,
        ];

        $traits_insi_lieu_naissance_inconnu = $traits_insi_identiques;

        $traits_insi_lieu_naissance_inconnu[PatientQualifierService::TRAIT_STRICT_LIEU_NAISSANCE_SOURCE] = null;

        $traits_insi_premier_prenom_different = $traits_insi_identiques;

        $traits_insi_premier_prenom_different[PatientQualifierService::TRAIT_STRICT_PRENOM_SOURCE] = 'foo';

        return [
            'qualification_ok_traits_identiques'        => [
                $patient,
                $traits_insi_identiques,
                true,
            ],
            'qualification_ok_lieu_naissance_inconnu'   => [
                $patient,
                $traits_insi_lieu_naissance_inconnu,
                true,
            ],
            'qualification_ko_premier_prenom_different' => [
                $patient,
                $traits_insi_premier_prenom_different,
                false,
            ],
        ];
    }
}
